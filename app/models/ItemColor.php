<?php

class ItemColor extends Database {
    public $errors = [];

    public function insert_color_validation($data) {
        $conn = $this->openConnection();

        try {
            $color_d = strtoupper($data['color_d']);
            $item_id = $data['item_id'];

            $check_query = "SELECT COUNT(*) FROM ITEM_COLOR WHERE ITEM_COL_DESCRIPT = :color_d AND ITEM_ID = :item_id";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bindParam(':color_d', $color_d);
            $check_stmt->bindParam(':item_id', $item_id);
            $check_stmt->execute();
            $count = $check_stmt->fetchColumn();

            if ($count > 0) {
                $this->errors['message'] = "Color already exist.";
                return false;
            } else {
                return true;
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Error validating color. " . $e->getMessage();
            return false;
        }
    }

    public function delete_color_validation($data) {
        $conn = $this->openConnection();
    
        try {
            $color_id = $data['col_id'];
    
            $check_variation_query = "SELECT COUNT(*) FROM ITEM_VARIATION WHERE ITEM_COL_ID = :col_id";
            $check_variation_stmt = $conn->prepare($check_variation_query);
            $check_variation_stmt->bindParam(':col_id', $color_id);
            $check_variation_stmt->execute();
            $variation_count = $check_variation_stmt->fetchColumn();
    
            if ($variation_count > 0) {
                $this->errors['message'] = "Cannot delete color because it has associated item variations.";
                return false;
            }
    
            return true;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error validating color deletion." . $e->getMessage();
            return false;
        }
    }
    

    public function insert_color ($data) {
        $conn = $this->openConnection();

        if (!$this->insert_color_validation($data)) {
            return;
        }

        try {
            $color_d = strtoupper($data['color_d']);
            $item_id = $data['item_id'];

            $insert = "INSERT INTO ITEM_COLOR (ITEM_COL_DESCRIPT, ITEM_ID) 
                        VALUES (:color_d, :item_id)"; 
            $stmt = $conn->prepare($insert);
            $stmt->bindParam(':color_d', $color_d);
            $stmt->bindParam(':item_id', $item_id);

            if ($stmt->execute()) {
                $this->errors['message'] = "Color created successfully!";
            } else {
                $this->errors['message'] = "Failed to create color.";
            }

        } catch (PDOException $e){
            $this->errors['message'] = "Could not add color." . $e->getMessage();
        }
    }

    public function update_color($data) {
        $conn = $this->openConnection();

        if (!$this->insert_color_validation($data)) {
            return;
        }

        try {
            $color_id = $data['col_id'];
            $color_d = strtoupper($data['col_d']);

            $update_query = "UPDATE ITEM_COLOR SET ITEM_COL_DESCRIPT = :col_d WHERE ITEM_COL_ID = :col_id";
            $stmt = $conn->prepare($update_query);
            $stmt->bindParam(':col_id', $color_id);
            $stmt->bindParam(':col_d', $color_d);

            if ($stmt->execute()) {
                $this->errors['message'] = "Color variation updated successfully!";
            } else {
                $this->errors['message'] = "Failed to update color variation.";
            }

        } catch (PDOException $e) {
            $this->errors['message'] = "Could not update color variation." . $e->getMessage();
        }        
    }

    public function delete_color($data) {
        $conn = $this->openConnection();

        if (!$this->delete_color_validation($data)) {
            return;
        }

        try {
            $delete_query = "DELETE FROM ITEM_COLOR WHERE ITEM_COL_ID = :col_id";
            $stmt = $conn->prepare($delete_query);
            $stmt->bindParam(':col_id', $data['col_id']);

            if ($stmt->execute()) {
                $this->errors['message'] = "Color variation deleted successfully!";
            } else {
                $this->errors['message'] = "Failed to delete color variation.";
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not delete color variation." . $e->getMessage();
        }
    }

    public function getAllColors() {
        $conn = $this->openConnection();
    
        try {
            $query = "SELECT ic.*, i.item_name 
                      FROM ITEM_COLOR ic
                      JOIN ITEM i ON ic.ITEM_ID = i.ITEM_ID
                      ORDER BY i.ITEM_NAME, ITEM_COL_ADDED DESC";
            $stmt = $conn->query($query);
            $colors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $colors;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching colors. " . $e->getMessage();
            return [];
        }
    }
}