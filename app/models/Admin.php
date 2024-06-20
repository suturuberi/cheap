<?php

class Admin extends Database {
    public $errors = [];

    public function getAllAdmins() {
        $conn = $this->openConnection();

        try {
            $query = "SELECT * FROM ADMIN";
            $stmt = $conn->query($query);
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $admins;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching item admins. " . $e->getMessage();
            return [];
        }
    }
}