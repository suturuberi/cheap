<?php

class ScheduleType extends Database {
    public $errors = [];

    public function getAllScheduleType() {
        $conn = $this->openConnection();

        try {
            $query = "SELECT * FROM SCHEDULE_TYPE";
            $stmt = $conn->query($query);
            $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $statuses;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching schedule type. " . $e->getMessage();
            return [];
        }
    }
}