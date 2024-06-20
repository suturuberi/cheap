<?php

class SaleCategory extends Database {
    public $errors = [];
    public function getAllSalesCategory()
    {
        $conn = $this->openConnection();

        try {
            $query = "SELECT * FROM SALE_CATEGORY";
            $stmt = $conn->query($query);
            $sale_category = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $sale_category;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching sale categories. " . $e->getMessage();
            return [];
        }
    }
}