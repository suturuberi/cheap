<?php

class ItemSize extends Database {
    public $errors = [];

    public function insert_size_validation($data) {
        $conn = $this->openConnection();

        try {
            $size_d = strtoupper($data['size_d']);
            $item_id = $data['item_id'];

            $check_query = "SELECT COUNT(*) FROM ITEM_SIZE WHERE ITEM_SIZE_DESCRIPT = :size_d AND ITEM_ID = :item_id";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bindParam(':size_d', $size_d);
            $check_stmt->bindParam(':item_id', $item_id);
            $check_stmt->execute();
            $count = $check_stmt->fetchColumn();

            if ($count > 0) {
                $this->errors['message'] = "Size already exist.";
                return false;
            } else {
                return true;
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Error validating size. " . $e->getMessage();
            return false;
        }
    }

    public function delete_size_validation($data) {
        $conn = $this->openConnection();
    
        try {
            $size_id = $data['size_id'];
    
            // Check if there are associated item variations for the size being deleted
            $check_variation_query = "SELECT COUNT(*) FROM ITEM_VARIATION WHERE ITEM_SIZE_ID = :size_id";
            $check_variation_stmt = $conn->prepare($check_variation_query);
            $check_variation_stmt->bindParam(':size_id', $size_id);
            $check_variation_stmt->execute();
            $variation_count = $check_variation_stmt->fetchColumn();
    
            if ($variation_count > 0) {
                $this->errors['message'] = "Cannot delete size because it has associated item variations.";
                return false;
            }
    
            return true;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error validating size deletion." . $e->getMessage();
            return false;
        }
    }
    

    public function insert_size ($data) {
        $conn = $this->openConnection();


        if (!$this->insert_size_validation($data)) {
            return;
        }

        try {
            $size_d = strtoupper($data['size_d']);
            $item_id = $data['item_id']; 

            $insert = "INSERT INTO ITEM_SIZE (ITEM_SIZE_DESCRIPT, ITEM_ID) 
                        VALUES (:size_d, :item_id)"; 
            $stmt = $conn->prepare($insert);
            $stmt->bindParam(':size_d', $size_d);
            $stmt->bindParam(':item_id', $item_id);

            if ($stmt->execute()) {
                $this->errors['message'] = "Size created successfully!";
            } else {
                $this->errors['message'] = "Failed to create size.";
            }

        } catch (PDOException $e){
            $this->errors['message'] = "Could not add size." . $e->getMessage();
        }
    }

    public function update_size($data) {
        $conn = $this->openConnection();

        if (!$this->insert_size_validation($data)) {
            return;
        }

        try {
            $size_id = $data['size_id'];
            $size_d = strtoupper($data['size_d']);

            $update_query = "UPDATE ITEM_SIZE SET ITEM_SIZE_DESCRIPT = :size_d WHERE ITEM_SIZE_ID = :size_id";
            $stmt = $conn->prepare($update_query);
            $stmt->bindParam(':size_id', $size_id);
            $stmt->bindParam(':size_d', $size_d);

            if ($stmt->execute()) {
                $this->errors['message'] = "Size variation updated successfully!";
            } else {
                $this->errors['message'] = "Failed to update size variation.";
            }

        } catch (PDOException $e) {
            $this->errors['message'] = "Could not update size variation." . $e->getMessage();
        }        
    }

    public function delete_size($data) {
        $conn = $this->openConnection();

        if (!$this->delete_size_validation($data)) {
            return;
        }
        
        try {
            $delete_query = "DELETE FROM ITEM_SIZE WHERE ITEM_SIZE_ID = :size_id";
            $stmt = $conn->prepare($delete_query);
            $stmt->bindParam(':size_id', $data['size_id']);

            if ($stmt->execute()) {
                $this->errors['message'] = "Size variation deleted successfully!";
            } else {
                $this->errors['message'] = "Failed to delete size variation.";
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not delete size variation." . $e->getMessage();
        }
    }

    public function getAllSizes() {
        $conn = $this->openConnection();
    
        try {
            $query = "SELECT isz.*, i.item_name 
                      FROM ITEM_SIZE isz
                      JOIN ITEM i ON isz.ITEM_ID = i.ITEM_ID
                      ORDER BY i.ITEM_NAME, ITEM_SIZE_ADDED DESC";
            $stmt = $conn->query($query);
            $sizes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $sizes;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching sizes. " . $e->getMessage();
            return [];
        }
    }
}