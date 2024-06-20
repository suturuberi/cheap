<?php

class ItemStatus extends Database {
    public $errors = [];

    public function getAllStatuses() {
        $conn = $this->openConnection();

        try {
            $query = "SELECT * FROM ITEM_STATUS";
            $stmt = $conn->query($query);
            $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $statuses;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching item statuses. " . $e->getMessage();
            return [];
        }
    }
}