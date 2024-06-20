<?php

class SaleFreebie extends Database {
    public $errors = [];  

    public function insert_sale_freebie_validation($data) {
        $conn = $this->openConnection();
        try {
            if (empty($data['salef_item']) || empty($data['salef_descript'])) {
                $this->errors['message'] = "All fields are required.";
                return false;
            }

            $data['salef_item'] = strtoupper($data['salef_item']);
    
            // Check for duplicate item name
            $query = "SELECT COUNT(*) FROM SALE_FREEBIE WHERE UPPER(SALEF_ITEM) = :salef_item";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':salef_item', $data['salef_item']);
            $stmt->execute();
            $count = $stmt->fetchColumn();
    
            if ($count > 0) {
                $this->errors['message'] = "Item name already exists.";
                return false;
            }
    
            return true; 
        } catch (PDOException $e) {
            $this->errors['message'] = "Error validating sale. " . $e->getMessage();
            return false;
        }
    }
    
    public function insert_sale_freebie($data) {
        if (!$this->insert_sale_freebie_validation($data)) {
            return;
        }
    
        try {
            $conn = $this->openConnection();
            
            // Ensure data is in uppercase
            $sale_id = $data['sale_id'];
            $salef_item = strtoupper($data['salef_item']);
            $salef_descript = strtoupper($data['salef_descript']);
    
            $insert_query = "INSERT INTO SALE_FREEBIE (SALEF_ITEM, SALEF_DESCRIPT, SALE_ID) 
                             VALUES (:salef_item, :salef_descript, :sale_id)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bindParam(':salef_item', $salef_item);
            $stmt->bindParam(':salef_descript', $salef_descript);
            $stmt->bindParam(':sale_id', $sale_id);
    
            if ($stmt->execute()) {
                $this->errors['message'] = "Sale freebie added successfully!";
            } else {
                $this->errors['message'] = "Failed to add sale freebie.";
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not add freebie." . $e->getMessage();
        }
    }
    
    public function delete_sale_freebie_validation($data) {
        $conn = $this->openConnection();
        
        try {
            // Check if the sale is scheduled
            $query = "SELECT COUNT(*) FROM SCHEDULE WHERE SALE_ID = (SELECT SALE_ID FROM SALE_FREEBIE WHERE SALEF_ID = :salef_id)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':salef_id', $data['salef_id']);
            $stmt->execute();
            $count = $stmt->fetchColumn();
    
            if ($count > 0) {
                $this->errors['message'] = "Cannot delete this sale freebie because it is part of a scheduled sale.";
                return false;
            }
    
            return true;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error validating sale freebie deletion. " . $e->getMessage();
            return false;
        }
    }

    public function delete_sale_freebie($data) {

        if (!$this->delete_sale_freebie_validation($data)) {
            return;
        }

        $conn = $this->openConnection();
        try {
            $delete_query = "DELETE FROM SALE_FREEBIE WHERE SALEF_ID = :salef_id";
            $stmt = $conn->prepare($delete_query);
            $stmt->bindParam(':salef_id', $data['salef_id']);
    
            if ($stmt->execute()) {
                $this->errors['message'] = "Sale freebie deleted successfully!";
            } else {
                $this->errors['message'] = "Failed to delete sale freebie.";
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not delete sale freebie." . $e->getMessage();
        }
    }

    public function getAllSaleFreebie() {
        $conn = $this->openConnection();
        try {
            $query = "SELECT SF.*, S.SALE_DESCRIPT 
                      FROM SALE_FREEBIE SF
                      JOIN SALE S ON SF.SALE_ID = S.SALE_ID
                      ORDER BY SALEF_ADDED, SALE_DESCRIPT";
            $stmt = $conn->query($query);
            $sale_freebie = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $sale_freebie;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching sale freebie. " . $e->getMessage();
            return [];
        }
    }

    public function getAllScheduledSaleFreebie() {
        $conn = $this->openConnection();
        try {
            $query = "SELECT SF.*, S.SALE_DESCRIPT 
                      FROM SALE_FREEBIE SF
                      JOIN SALE S ON SF.SALE_ID = S.SALE_ID
                      JOIN SALE_STATUS SS ON S.SALES_ID = SS.SALES_ID
                      WHERE SS.SALES_DESCRIPT = 'SCHEDULED'
                      ORDER BY SALE_DESCRIPT";
            $stmt = $conn->query($query);
            $scheduled_sale_freebies = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $scheduled_sale_freebies;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching scheduled sale freebies. " . $e->getMessage();
            return [];
        }
    }
    
}