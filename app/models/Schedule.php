<?php

class Schedule extends Database {
    public $errors = [];

    public function insert_validation_schedule($data) {
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        $admin_id = $data['admin_id'];
        $itemv_id = isset($data['itemv_id']) ? $data['itemv_id'] : null;
        $sale_id = isset($data['sale_id']) ? $data['sale_id'] : null;

        if (empty($start_date) || empty($end_date) || empty($admin_id)) {
            $this->errors['message'] = "All fields are required.";
            return false;
        }

        // if (empty($itemv_id) || empty($sale_id)) {
        //     $this->errors['message'] = "All fields are required.";
        //     return false;
        // }

        if ($this->checkDuplicateSchedule($admin_id, $itemv_id, $sale_id, $start_date, $end_date)) {
            $this->errors['message'] = "This schedule already exists.";
            return false;
        }

        if (!empty($sale_id) && !$this->checkSaleHasItems($sale_id)) {
            $this->errors['message'] = "The sale must have at least one associated sale item.";
            return false;
        }

        if ($start_date <= date("Y-m-d")) {
            $this->errors['message'] = "Starting date cannot be less than or equal the current date.";
            return false;
        }

        if ($end_date < $start_date) {
            $this->errors['message'] = "End date cannot be less than the starting date.";
            return false;
        }

        return true;
    }

    public function edit_validation_schedule($data) {
        $start_date = $data['edit-start-date'];
        $end_date = $data['edit-end-date'];
        $admin_id = $data['edit-admin-id'];
        $itemv_id = $data['edit-itemv-id'];
        $sale_id = $data['edit-sale-id'];

        if ($this->checkDuplicateSchedule($admin_id, $itemv_id, $sale_id, $start_date, $end_date)) {
            $this->errors['message'] = "This schedule already exists.";
            return false;
        }
        if ($start_date <= date("Y-m-d")) {
            $this->errors['message'] = "Starting date cannot be less than or equal the current date.";
            return false;
        }

        if ($end_date < $start_date) {
            $this->errors['message'] = "End date cannot be less than the starting date.";
            return false;
        }

        return true;
    }

    public function delete_validation_schedule($data) {
        $conn = $this->openConnection();
    
        try {
            $sched_id = $data['del-sched-id'];
    
            // Get the starting date of the schedule to be deleted
            $query = "SELECT SCHED_START_DATE FROM SCHEDULE WHERE SCHED_ID = :sched_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':sched_id', $sched_id);
            $stmt->execute();
            $start_date = $stmt->fetchColumn();
    
            // Calculate the current date and 3 days after the current date
            $current_date = date("Y-m-d");
            $three_days_later = date("Y-m-d", strtotime($current_date . "+3 days"));
    
            // Check if the starting date is within 3 days from the current date
            if ($start_date >= $current_date && $start_date <= $three_days_later) {
                $this->errors['message'] = "Cannot delete schedule if the starting date is within 3 days from the current date.";
                return false;
            }
    
            return true;
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not validate schedule deletion. " . $e->getMessage();
            return false;
        }
    }

    public function checkDuplicateSchedule($admin_id, $itemv_id, $sale_id, $start_date, $end_date) {
        $conn = $this->openConnection();

        try{
            $query = "SELECT COUNT(*) FROM SCHEDULE WHERE ADMIN_ID = :admin_id AND (ITEMV_ID IS NOT NULL AND ITEMV_ID = :itemv_id 
            OR SALE_ID IS NOT NULL AND SALE_ID = :sale_id) AND SCHED_START_DATE = :start_date AND SCHED_END_DATE = :end_date";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':admin_id', $admin_id);
            $stmt->bindParam(':itemv_id', $itemv_id);
            $stmt->bindParam(':sale_id', $sale_id);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->execute();
            $count = $stmt->fetchColumn();
        
            return $count > 0;
        }
        catch (PDOException $e) {
            $this->errors['message'] = "Could not check schedule. " . $e->getMessage();
            return false;
        }
    }

    private function checkSaleHasItems($sale_id) {
        $conn = $this->openConnection();

        try {
            $query = "SELECT COUNT(*) FROM SALE_ITEM WHERE SALE_ID = :sale_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':sale_id', $sale_id, PDO::PARAM_INT);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            return $count > 0;
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not check sale items. " . $e->getMessage();
            return false;
        }
    }

    public function insert_schedule($data) {
        $conn = $this->openConnection();
    
        if (!$this->insert_validation_schedule($data)) {
            return false;
        }
    
        try {
            $start_date = $data['start_date'];
            $end_date = $data['end_date'];
            $schedt_id = $data['schedule_type'];
            $admin_id = $data['admin_id'];
            $itemv_id = isset($data['itemv_id']) ? $data['itemv_id'] : null;
            $sale_id = isset($data['sale_id']) ? $data['sale_id'] : null;
    
            $insert_query = "INSERT INTO SCHEDULE (SCHED_START_DATE, SCHED_END_DATE, SCHEDT_ID, ADMIN_ID, ITEMV_ID, SALE_ID) 
                             VALUES (:start_date, :end_date, :schedt_id, :admin_id, :itemv_id, :sale_id)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->bindParam(':schedt_id', $schedt_id);
            $stmt->bindParam(':admin_id', $admin_id);
            $stmt->bindParam(':itemv_id', $itemv_id, PDO::PARAM_INT);
            $stmt->bindParam(':sale_id', $sale_id, PDO::PARAM_INT);
    
            if ($stmt->execute()) {
                $this->errors['message'] = "Schedule added successfully!";
                return true;
            } else {
                $this->errors['message'] = "Failed to add schedule.";
                return false;
            }
    
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not add schedule. " . $e->getMessage();
            return false;
        }
    }
    

    public function edit_schedule($data) {
        $conn = $this->openConnection();
        
        if (!$this->edit_validation_schedule($data)) {
            return false;
        }

        try {
            $sched_id = $data['edit-sched-id'];
            $start_date = $data['edit-start-date'];
            $end_date = $data['edit-end-date'];
    
            $update_query = "UPDATE SCHEDULE 
                             SET SCHED_START_DATE = :start_date, SCHED_END_DATE = :end_date
                             WHERE SCHED_ID = :sched_id";
            $stmt = $conn->prepare($update_query);
            $stmt->bindParam(':sched_id', $sched_id);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
    
            if ($stmt->execute()) {
                $this->errors['message'] = "Schedule updated successfully!";
                return true;
            } else {
                $this->errors['message'] = "Failed to update schedule.";
                return false;
            }
    
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not update schedule. " . $e->getMessage();
            return false;
        }
    }
    

    public function delete_schedule($data) {
        $conn = $this->openConnection();

        if (!$this->delete_validation_schedule($data)) {
            return false;
        }

        try {
            $delete_query = "DELETE FROM SCHEDULE WHERE SCHED_ID = :sched_id";
            $stmt = $conn->prepare($delete_query);
            $stmt->bindParam(':sched_id', $data['del-sched-id']);

            if ($stmt->execute()) {
                $this->errors['message'] = "Schedule deleted successfully!";
                return true;
            } else {
                $this->errors['message'] = "Failed to delete schedule.";
                return false;
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not delete schedule. " . $e->getMessage();
            return false;
        }
    }
    
    public function getSchedulesByType($scheduleTypeId)
    {
        $conn = $this->openConnection();

        try {
            $stmt = $conn->prepare("SELECT * FROM SCHEDULE WHERE SCHEDT_ID = :scheduleTypeId");
            $stmt->bindParam(':scheduleTypeId', $scheduleTypeId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching schedule by type. " . $e->getMessage();
            return [];
        }
        
    }

    public function getAllRegularSchedule()
    {
        $conn = $this->openConnection();

        try {
            $stmt = $conn->prepare("SELECT s.SCHED_ID, s.SCHED_START_DATE, s.SCHED_END_DATE, s.SCHED_ADDED, st.SCHEDT_ID, st.SCHEDT_DESCRIPT, a.ADMIN_ID, a.ADMIN_FNAME, a.ADMIN_LNAME, i.ITEM_ID, i.ITEM_NAME, iv.ITEMV_ID, ic.ITEM_COL_DESCRIPT, iss.ITEM_SIZE_DESCRIPT
                               FROM SCHEDULE s
                               JOIN SCHEDULE_TYPE st ON s.SCHEDT_ID = st.SCHEDT_ID
                               JOIN ADMIN a ON s.ADMIN_ID = a.ADMIN_ID
                               LEFT JOIN ITEM_VARIATION iv ON s.ITEMV_ID = iv.ITEMV_ID
                               LEFT JOIN ITEM i ON iv.ITEM_ID = i.ITEM_ID
                               LEFT JOIN ITEM_COLOR ic ON iv.ITEM_COL_ID = ic.ITEM_COL_ID
                               LEFT JOIN ITEM_SIZE iss ON iv.ITEM_SIZE_ID = iss.ITEM_SIZE_ID
                                WHERE st.SCHEDT_ID = :scheduleTypeId");
            $regularScheduleId = 1; // Replace with the actual ID for "Regular Schedule"
            $stmt->bindParam(':scheduleTypeId', $regularScheduleId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching regular schedules. " . $e->getMessage();
            return [];
        }
    }

    public function getAllSaleSchedule()
    {
        $conn = $this->openConnection();

        try {
            $stmt = $conn->prepare("SELECT s.SCHED_ID, s.SCHED_START_DATE, s.SCHED_END_DATE, s.SCHED_ADDED, st.SCHEDT_ID, st.SCHEDT_DESCRIPT, a.ADMIN_ID, a.ADMIN_FNAME, a.ADMIN_LNAME, i.ITEM_ID, i.ITEM_NAME, sa.SALE_ID, sa.SALE_DESCRIPT, iv.ITEMV_ID, ic.ITEM_COL_DESCRIPT, iss.ITEM_SIZE_DESCRIPT
                               FROM SCHEDULE s
                               JOIN SCHEDULE_TYPE st ON s.SCHEDT_ID = st.SCHEDT_ID
                               JOIN ADMIN a ON s.ADMIN_ID = a.ADMIN_ID
                               LEFT JOIN ITEM_VARIATION iv ON s.ITEMV_ID = iv.ITEMV_ID
                               LEFT JOIN ITEM i ON iv.ITEM_ID = i.ITEM_ID
                               LEFT JOIN SALE sa ON s.SALE_ID = sa.SALE_ID
                               LEFT JOIN ITEM_COLOR ic ON iv.ITEM_COL_ID = ic.ITEM_COL_ID
                               LEFT JOIN ITEM_SIZE iss ON iv.ITEM_SIZE_ID = iss.ITEM_SIZE_ID
                                WHERE st.SCHEDT_ID = :scheduleTypeId");
            $saleScheduleId = 2; // Replace with the actual ID for "Sale Schedule"
            $stmt->bindParam(':scheduleTypeId', $saleScheduleId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching sale schedules. " . $e->getMessage();
            return [];
        }
    }

    public function getAllSchedule() {
        $conn = $this->openConnection();
    
        try {
            $query = "SELECT s.SCHED_ID, s.SCHED_START_DATE, s.SCHED_END_DATE, s.SCHED_ADDED, st.SCHEDT_ID, st.SCHEDT_DESCRIPT, a.ADMIN_ID, a.ADMIN_FNAME, a.ADMIN_LNAME, i.ITEM_ID, i.ITEM_NAME, sa.SALE_ID, sa.SALE_DESCRIPT
                      FROM SCHEDULE s
                      JOIN SCHEDULE_TYPE st ON s.SCHEDT_ID = st.SCHEDT_ID
                      JOIN ADMIN a ON s.ADMIN_ID = a.ADMIN_ID
                      LEFT JOIN ITEM_VARIATION iv ON s.ITEMV_ID = iv.ITEMV_ID
                      LEFT JOIN ITEM i ON iv.ITEM_ID = i.ITEM_ID
                      LEFT JOIN SALE sa ON s.SALE_ID = sa.SALE_ID
                      ORDER BY s.SCHED_START_DATE";
            $stmt = $conn->query($query);
            $scheds = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $scheds;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching item schedules. " . $e->getMessage();
            return [];
        }
    }

    public function getSchedulesByAdmin($admin_id)
    {
        $conn = $this->openConnection();

        try {
            $stmt = $conn->prepare("SELECT s.SCHED_ID, s.SCHED_START_DATE, s.SCHED_END_DATE, s.SCHED_ADDED, st.SCHEDT_DESCRIPT, 
                                        a.ADMIN_ID, a.ADMIN_FNAME, a.ADMIN_LNAME, 
                                        i.ITEM_ID, i.ITEM_NAME, 
                                        sa.SALE_ID, sa.SALE_DESCRIPT, 
                                        iv.ITEMV_ID, ic.ITEM_COL_DESCRIPT, iss.ITEM_SIZE_DESCRIPT
                                FROM SCHEDULE s
                                JOIN SCHEDULE_TYPE st ON s.SCHEDT_ID = st.SCHEDT_ID
                                JOIN ADMIN a ON s.ADMIN_ID = a.ADMIN_ID
                                LEFT JOIN ITEM_VARIATION iv ON s.ITEMV_ID = iv.ITEMV_ID
                                LEFT JOIN ITEM i ON iv.ITEM_ID = i.ITEM_ID
                                LEFT JOIN SALE sa ON s.SALE_ID = sa.SALE_ID
                                LEFT JOIN ITEM_COLOR ic ON iv.ITEM_COL_ID = ic.ITEM_COL_ID
                                LEFT JOIN ITEM_SIZE iss ON iv.ITEM_SIZE_ID = iss.ITEM_SIZE_ID
                                WHERE a.ADMIN_ID = :admin_id
                                ORDER BY s.SCHED_START_DATE");
            $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching schedules for admin. " . $e->getMessage();
            return [];
        }
    }

    public function getAllRegularScheduleByAdmin($admin_id)
    {
        $conn = $this->openConnection();

        try {
            $stmt = $conn->prepare("SELECT s.SCHED_ID, s.SCHED_START_DATE, s.SCHED_END_DATE, s.SCHED_ADDED, st.SCHEDT_ID, st.SCHEDT_DESCRIPT, 
                                        a.ADMIN_ID, a.ADMIN_FNAME, a.ADMIN_LNAME, 
                                        i.ITEM_ID, i.ITEM_NAME, 
                                        iv.ITEMV_ID, ic.ITEM_COL_DESCRIPT, iss.ITEM_SIZE_DESCRIPT
                                    FROM SCHEDULE s
                                    JOIN SCHEDULE_TYPE st ON s.SCHEDT_ID = st.SCHEDT_ID
                                    JOIN ADMIN a ON s.ADMIN_ID = a.ADMIN_ID
                                    LEFT JOIN ITEM_VARIATION iv ON s.ITEMV_ID = iv.ITEMV_ID
                                    LEFT JOIN ITEM i ON iv.ITEM_ID = i.ITEM_ID
                                    LEFT JOIN ITEM_COLOR ic ON iv.ITEM_COL_ID = ic.ITEM_COL_ID
                                    LEFT JOIN ITEM_SIZE iss ON iv.ITEM_SIZE_ID = iss.ITEM_SIZE_ID
                                    WHERE a.ADMIN_ID = :admin_id AND st.SCHEDT_ID = :scheduleTypeId
                                    ORDER BY s.SCHED_START_DATE");
            $regularScheduleId = 1; // Replace with the actual ID for "Regular Schedule"
            $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
            $stmt->bindParam(':scheduleTypeId', $regularScheduleId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching regular schedules for admin. " . $e->getMessage();
            return [];
        }
    }

    public function getAllSaleScheduleByAdmin($admin_id)
    {
        $conn = $this->openConnection();

        try {
            $stmt = $conn->prepare("SELECT s.SCHED_ID, s.SCHED_START_DATE, s.SCHED_END_DATE, s.SCHED_ADDED, st.SCHEDT_ID, st.SCHEDT_DESCRIPT, 
                                        a.ADMIN_ID, a.ADMIN_FNAME, a.ADMIN_LNAME, 
                                        i.ITEM_ID, i.ITEM_NAME, 
                                        sa.SALE_ID, sa.SALE_DESCRIPT, 
                                        iv.ITEMV_ID, ic.ITEM_COL_DESCRIPT, iss.ITEM_SIZE_DESCRIPT
                                    FROM SCHEDULE s
                                    JOIN SCHEDULE_TYPE st ON s.SCHEDT_ID = st.SCHEDT_ID
                                    JOIN ADMIN a ON s.ADMIN_ID = a.ADMIN_ID
                                    LEFT JOIN ITEM_VARIATION iv ON s.ITEMV_ID = iv.ITEMV_ID
                                    LEFT JOIN ITEM i ON iv.ITEM_ID = i.ITEM_ID
                                    LEFT JOIN SALE sa ON s.SALE_ID = sa.SALE_ID
                                    LEFT JOIN ITEM_COLOR ic ON iv.ITEM_COL_ID = ic.ITEM_COL_ID
                                    LEFT JOIN ITEM_SIZE iss ON iv.ITEM_SIZE_ID = iss.ITEM_SIZE_ID
                                    WHERE a.ADMIN_ID = :admin_id AND st.SCHEDT_ID = :scheduleTypeId
                                    ORDER BY s.SCHED_START_DATE");
            $saleScheduleId = 2; // Replace with the actual ID for "Sale Schedule"
            $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
            $stmt->bindParam(':scheduleTypeId', $saleScheduleId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching sale schedules for admin. " . $e->getMessage();
            return [];
        }
    }

    public function getAdminSchedule($admin_id) {
        $conn = $this->openConnection();
    
        try {
            $stmt = $conn->prepare("SELECT SCHED_START_DATE as start_time, SCHED_END_DATE as end_time
                                    FROM SCHEDULE 
                                    WHERE ADMIN_ID = :admin_id 
                                    AND SCHED_START_DATE <= NOW() 
                                    AND SCHED_END_DATE >= NOW()");
            $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching admin schedule. " . $e->getMessage();
            return [];
        }
    } 
}

