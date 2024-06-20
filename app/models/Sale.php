<?php

class Sale extends Database {
    public $errors = [];  

    private function insert_sale_validation($data)
    {
        $conn = $this->openConnection();
        try {
            $sale_descript = strtoupper($data['sale_descript']);
            $sale_added = date('Y-m-d'); // assuming sale_added is set to the current date

            // Check for existing sale with the same description and date
            $check_query = "SELECT COUNT(*) FROM SALE WHERE SALE_DESCRIPT = :sale_descript AND SALE_ADDED = :sale_added";
            $stmt = $conn->prepare($check_query);
            $stmt->bindParam(':sale_descript', $sale_descript);
            $stmt->bindParam(':sale_added', $sale_added);
            $stmt->execute();
            $existing_count = $stmt->fetchColumn();

            if ($existing_count > 0) {
                $this->errors['message'] = "A sale with the same description already exists on this date.";
                return false;
            }
            return true;

        } catch (PDOException $e) {
            $this->errors['message'] = "Could not validate sale. " . $e->getMessage();
            return false;
        }
    }

    private function delete_sale_validation($data) {
        $sale_id = $data['sale_id'];

        if (!$this->canDelete($sale_id)) {
            return false;
        }

        if (!$this->isScheduledSale($sale_id)) {
            return false;
        }

        return true;
    }

    private function canDelete($sale_id) {
        $conn = $this->openConnection();
        try {
            // Query to check if the schedule has already started or ended
            $query = "SELECT SCHED_START_DATE, SCHED_END_DATE 
                      FROM SCHEDULE 
                      WHERE SALE_ID = :sale_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':sale_id', $sale_id);
            $stmt->execute();
            $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($schedule) {
                $current_date = date('Y-m-d');
                if ($schedule['sched_start_date'] <= $current_date || $schedule['sched_end_date'] < $current_date) {
                    $this->errors['message'] = "Cannot delete a schedule that has already started or ended.";
                    return false;
                }
            }
            return true;

        } catch (PDOException $e) {
            $this->errors['message'] = "Could not validate schedule. " . $e->getMessage();
            return false;
        }
    }

    private function isScheduledSale($sale_id) {
        $conn = $this->openConnection();

        try {
            $query = "SELECT COUNT(*) FROM SALE_STATUS WHERE SALES_ID = (SELECT SALES_ID FROM SALE WHERE SALE_ID = :sale_id) AND SALES_DESCRIPT = 'SCHEDULED'";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':sale_id', $sale_id);
            $stmt->execute();
            $result = $stmt->fetchColumn();
            if ($result > 0) {
                $this->errors['message'] = "Cannot delete a sale that is already scheduled.";
                return false;
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not determine if sale is scheduled. " . $e->getMessage();
            return false;
        }
        
    }

    public function insert_sale($data)
    {
        $conn = $this->openConnection();

        if (!$this->insert_sale_validation($data)) {
            return;
        }

        try {

            $sale_descript = strtoupper($data['sale_descript']);
            $sale_cat_id = $data['sale_cat_id'];
            $sale_added = date('Y-m-d'); // assuming sale_added is set to the current date

            // Insert new sale
            $insert = "INSERT INTO SALE (SALE_DESCRIPT, SALE_CAT_ID, SALE_ADDED)
                        VALUES (:sale_descript, :sale_cat_id, :sale_added)";
            $stmt = $conn->prepare($insert);
            $stmt->bindParam(':sale_descript', $sale_descript);
            $stmt->bindParam(':sale_cat_id', $sale_cat_id);
            $stmt->bindParam(':sale_added', $sale_added);

            if ($stmt->execute()) {
                $this->errors['message'] = "Sale created successfully!";
            } else {
                $this->errors['message'] = "Failed to create sale.";
            }

        } catch (PDOException $e) {
            $this->errors['message'] = "Could not add sale." . $e->getMessage();
        }
    }

    public function delete_sale($data) {
        $conn = $this->openConnection();

        if (!$this->delete_sale_validation($data)) {
            return;
        }

        try {
            $delete_query = "DELETE FROM SALE WHERE SALE_ID = :sale_id";
            $stmt = $conn->prepare($delete_query);
            $stmt->bindParam(':sale_id', $data['sale_id']);
    
            if ($stmt->execute()) {
                $this->errors['message'] = "Sale deleted successfully!";
            } else {
                $this->errors['message'] = "Failed to delete sale.";
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not delete sale." . $e->getMessage();
        }
    }
    
    public function getAllSales() {
        $conn = $this->openConnection();
        try {
            $query = "SELECT SALE.SALE_ID, SALE.SALE_DESCRIPT, SALE.SALE_ADDED, 
                         SALE_CATEGORY.SALE_CAT_DESCRIPT, SALE_STATUS.SALES_DESCRIPT 
                  FROM SALE
                  JOIN SALE_CATEGORY ON SALE.SALE_CAT_ID = SALE_CATEGORY.SALE_CAT_ID
                  JOIN SALE_STATUS ON SALE.SALES_ID = SALE_STATUS.SALES_ID
                  ORDER BY SALE_ADDED DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not retrieve sales." . $e->getMessage();
            return [];
        }
    }

    public function getAllAddedSales() {
        $conn = $this->openConnection();
        try {
            $query = "SELECT SALE.SALE_ID, SALE.SALE_DESCRIPT, SALE.SALE_ADDED, 
                         SALE_CATEGORY.SALE_CAT_DESCRIPT, SALE_STATUS.SALES_DESCRIPT 
                  FROM SALE
                  JOIN SALE_CATEGORY ON SALE.SALE_CAT_ID = SALE_CATEGORY.SALE_CAT_ID
                  JOIN SALE_STATUS ON SALE.SALES_ID = SALE_STATUS.SALES_ID
                  WHERE SALE_STATUS.SALES_DESCRIPT = 'ADDED'";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not retrieve added sales." . $e->getMessage();
            return [];
        }
    }

    public function getAllScheduledSales() {
        $conn = $this->openConnection();
        try {
            $query = "SELECT SALE.SALE_ID, SALE.SALE_DESCRIPT, SALE.SALE_ADDED, 
                         SALE_CATEGORY.SALE_CAT_DESCRIPT, SALE_STATUS.SALES_DESCRIPT 
                  FROM SALE
                  JOIN SALE_CATEGORY ON SALE.SALE_CAT_ID = SALE_CATEGORY.SALE_CAT_ID
                  JOIN SALE_STATUS ON SALE.SALES_ID = SALE_STATUS.SALES_ID
                  WHERE SALE_STATUS.SALES_DESCRIPT = 'SCHEDULED'
                  ORDER BY SALE_DESCRIPT";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not retrieve added sales." . $e->getMessage();
            return [];
        }
    }
    
} 