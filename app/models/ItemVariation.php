<?php

class ItemVariation extends Database {
    public $errors = [];

    private function insert_item_variation_validation($data) {
        if (empty($data['item']) || empty($data['size']) || empty($data['color']) || empty($data['stocks']) || empty($data['price'])) {
            $this->errors['validation'] = "All fields are required.";
            return false;
        }

        if (!is_numeric($data['stocks']) || $data['stocks'] <= 0) {
            $this->errors['validation'] = "Stocks must be a positive number.";
            return false;
        }

        if (!is_numeric($data['price']) || $data['price'] <= 0) {
            $this->errors['validation'] = "Price must be a positive number.";
            return false;
        }
        
        if (!$this->check_duplicate_item($data)) {
            return false;
        }

        return true;
    }

    private function check_duplicate_item($data){
        $conn = $this->openConnection();
        try {
            $query = "SELECT COUNT(*) AS count FROM ITEM_VARIATION 
                        WHERE ITEM_ID = :item_id AND ITEM_SIZE_ID = :size_id AND ITEM_COL_ID = :color_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':item_id', $data['item']);
            $stmt->bindParam(':size_id', $data['size']);
            $stmt->bindParam(':color_id', $data['color']);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && $result['count'] > 0) {
                $this->errors['validation'] = "An item variation with the same item, size, and color already exists.";
                return false;
            }

            return true;
        } catch (PDOException $e) {
            $this->errors['message'] = "Validation error: " . $e->getMessage();
            return false;
        }
    }

    public function delete_var_validation($data) {
        $conn = $this->openConnection();

        try {
            $query = "SELECT ITEMV_QOH FROM ITEM_VARIATION WHERE ITEMV_ID = :itemv_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':itemv_id', $data['itemv_id']);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_COLUMN);

            if ($result > 0) {
                $this->errors['validation'] = "Cannot delete item variation with quantity on hand (QOH) greater than zero.";
                return false;
            }

            return true;
        } catch (PDOException $e) {
            $this->errors['message'] = "Validation error: " . $e->getMessage();
            return false;
        }
    }

    public function update_item_var_validation($data) {
        $conn = $this->openConnection();
    
        // Validate add_qoh
        if (!is_numeric($data['add_qoh']) || $data['add_qoh'] < 0) {
            $this->errors['validation'] = "Quantity on hand must be a positive number.";
            return false;
        }
    
        // Validate price
        if (!is_numeric($data['price']) || $data['price'] <= 0) {
            $this->errors['validation'] = "Price must be a positive number.";
            return false;
        }
    
        try {
            // Fetch current ITEMS_ID description
            $itemv_id = $data['itemv_id'];
            $query = "SELECT ist.ITEMS_DESCRIPT
                      FROM ITEM_VARIATION iv
                      JOIN ITEM_STATUS ist ON iv.ITEMS_ID = ist.ITEMS_ID
                      WHERE iv.ITEMV_ID = :itemv_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':itemv_id', $itemv_id);
            $stmt->execute();
            $current_status_description = $stmt->fetch(PDO::FETCH_COLUMN);
    
            // Fetch target ITEMS_ID description
            $target_items_id = $data['status'];
            $query = "SELECT ITEMS_DESCRIPT FROM ITEM_STATUS WHERE ITEMS_ID = :items_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':items_id', $target_items_id);
            $stmt->execute();
            $target_status_description = $stmt->fetch(PDO::FETCH_COLUMN);
    
            // Validation check
            if ($current_status_description === "TO BE RELEASED" && $target_status_description === "HANDED BACK") {
                $this->errors['validation'] = "Cannot update status from 'TO BE RELEASED' to 'HANDED BACK'.";
                return false;
            }

            if ($target_status_description === "OUT OF STOCK") {
                $this->errors['validation'] = "Cannot update item status to 'Out of Stock'.";
                return false;
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Validation error: " . $e->getMessage();
            return false;
        }
    
        return true;
    }
    

    private function get_item_status_id($description) {
        $conn = $this->openConnection();
        
        try {
            $query = "SELECT ITEMS_ID FROM ITEM_STATUS WHERE ITEMS_DESCRIPT = :description";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':description', $description);
            $stmt->execute();
    
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                return $result['items_id'];
            } else {
                return false;
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching status ID: " . $e->getMessage();
            return false;
        }
    }
    
    
    public function insert_item_variation($data) {
        $conn = $this->openConnection();

        if (!$this->insert_item_variation_validation($data)) {
            return false;
        }

        try {
            $item_size_id = $data['size'];
            $item_col_id = $data['color'];
            $itemv_stocked_qty = $data['stocks'];
            $itemv_price = $data['price'];
            $item_id = $data['item'];

            $insert_query = "INSERT INTO ITEM_VARIATION (ITEMV_PRICE, ITEMV_STOCKED_QTY, ITEM_SIZE_ID, ITEM_COL_ID, ITEM_ID) 
                             VALUES (:itemv_price, :itemv_stocked_qty, :item_size_id, :item_col_id, :item_id)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bindParam(':item_size_id', $item_size_id);
            $stmt->bindParam(':item_col_id', $item_col_id);
            $stmt->bindParam(':itemv_stocked_qty', $itemv_stocked_qty);
            $stmt->bindParam(':itemv_price', $itemv_price);
            $stmt->bindParam(':item_id', $item_id);

            if ($stmt->execute()) {
                $this->errors['message'] = "Item variation added successfully!";
            } else {
                $this->errors['message'] = "Failed to add item variation.";
            }

        } catch (PDOException $e) {
            $this->errors['message'] = "Could not add item variation. " . $e->getMessage();
        }
    }

    public function update_item_variation($data)
    {
        $conn = $this->openConnection();

        if (!$this->update_item_var_validation($data)) {
            return false;
        }

        try {
            $itemv_id = $data['itemv_id'];
            $itemv_qoh = $data['qoh'];
            $itemv_add_qoh = $data['add_qoh'];
            $itemv_price = $data['price'];
            $items_id = $data['status'];

            $new_itemv_qoh = $itemv_qoh + $itemv_add_qoh;

            // Start transaction
            $conn->beginTransaction();

            // Set the session variable to mark the update source
            $conn->exec("SET LOCAL myapp.update_stocked_qty = 'on'");

            $update_query = "UPDATE ITEM_VARIATION 
                            SET ITEMV_QOH = :itemv_qoh, ITEMV_PRICE = :itemv_price, ITEMS_ID = :items_id 
                            WHERE ITEMV_ID = :itemv_id";
            $stmt = $conn->prepare($update_query);
            $stmt->bindParam(':itemv_qoh', $new_itemv_qoh);
            $stmt->bindParam(':itemv_price', $itemv_price);
            $stmt->bindParam(':items_id', $items_id);
            $stmt->bindParam(':itemv_id', $itemv_id);

            if ($stmt->execute()) {
                $this->errors['message'] = "Item variation updated successfully!";
                $conn->commit(); // Commit the transaction
            } else {
                $this->errors['message'] = "Failed to update item variation.";
                $conn->rollBack(); // Rollback the transaction
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not update item variation. " . $e->getMessage();
            $conn->rollBack(); // Rollback the transaction
        }
    }


    public function delete_item_var($data) {
        $conn = $this->openConnection();

        if (!$this->delete_var_validation($data)) {
            return;
        }

        try {
            $delete_query = "DELETE FROM ITEM_VARIATION WHERE ITEMV_ID = :itemv_id";
            $stmt = $conn->prepare($delete_query);
            $stmt->bindParam(':itemv_id', $data['itemv_id']);

            if ($stmt->execute()) {
                $this->errors['message'] = "Item variation deleted successfully!";
            } else {
                $this->errors['message'] = "Failed to delete item variation.";
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not delete item variation." . $e->getMessage();
        }
    }

    public function getAllItemVariations() {
        $conn = $this->openConnection();
    
        try {
            $query = "SELECT iv.ITEMV_ID, i.ITEM_ID, i.ITEM_NAME, icol.ITEM_COL_ID, icol.ITEM_COL_DESCRIPT, isz.ITEM_SIZE_ID, isz.ITEM_SIZE_DESCRIPT, 
                iv.ITEMV_PRICE, iv.ITEMV_STOCKED_QTY, iv.ITEMV_QOH, iv.ITEMV_ADDED, iv.ITEMS_ID, ist.ITEMS_DESCRIPT
                FROM ITEM_VARIATION iv
                JOIN ITEM_SIZE isz ON iv.ITEM_SIZE_ID = isz.ITEM_SIZE_ID
                JOIN ITEM_COLOR icol ON iv.ITEM_COL_ID = icol.ITEM_COL_ID
                JOIN ITEM i ON iv.ITEM_ID = i.ITEM_ID
                JOIN ITEM_STATUS ist ON iv.ITEMS_ID = ist.ITEMS_ID
                ORDER BY i.ITEM_NAME, iv.ITEMV_ADDED DESC";
            $stmt = $conn->query($query);
            $variations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $variations;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching item variations. " . $e->getMessage();
            return [];
        }
    }

    public function getAllToBeRHandedItemVariations() {
        $conn = $this->openConnection();
    
        try {
            $query = "SELECT iv.ITEMV_ID, i.ITEM_ID, i.ITEM_NAME, icol.ITEM_COL_ID, icol.ITEM_COL_DESCRIPT, isz.ITEM_SIZE_ID, isz.ITEM_SIZE_DESCRIPT, 
                iv.ITEMV_PRICE, iv.ITEMV_STOCKED_QTY, iv.ITEMV_QOH, iv.ITEMV_ADDED, iv.ITEMS_ID, ist.ITEMS_DESCRIPT
                FROM ITEM_VARIATION iv
                JOIN ITEM_SIZE isz ON iv.ITEM_SIZE_ID = isz.ITEM_SIZE_ID
                JOIN ITEM_COLOR icol ON iv.ITEM_COL_ID = icol.ITEM_COL_ID
                JOIN ITEM i ON iv.ITEM_ID = i.ITEM_ID
                JOIN ITEM_STATUS ist ON iv.ITEMS_ID = ist.ITEMS_ID
                WHERE iv.ITEMS_ID = (SELECT ITEMS_ID FROM ITEM_STATUS WHERE ITEMS_DESCRIPT = 'TO BE RELEASED') OR
                    iv.ITEMS_ID = (SELECT ITEMS_ID FROM ITEM_STATUS WHERE ITEMS_DESCRIPT = 'HANDED BACK');";
            $stmt = $conn->query($query);
            $variations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $variations;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching item variations. " . $e->getMessage();
            return [];
        }
    } 
    
    public function getAllReleasedItemVariations() {
        $conn = $this->openConnection();
    
        try {
            $query = "SELECT iv.ITEMV_ID, i.ITEM_ID, i.ITEM_NAME, icol.ITEM_COL_ID, icol.ITEM_COL_DESCRIPT, isz.ITEM_SIZE_ID, isz.ITEM_SIZE_DESCRIPT, 
                iv.ITEMV_PRICE, iv.ITEMV_STOCKED_QTY, iv.ITEMV_QOH, iv.ITEMV_ADDED, iv.ITEMS_ID, ist.ITEMS_DESCRIPT
                FROM ITEM_VARIATION iv
                JOIN ITEM_SIZE isz ON iv.ITEM_SIZE_ID = isz.ITEM_SIZE_ID
                JOIN ITEM_COLOR icol ON iv.ITEM_COL_ID = icol.ITEM_COL_ID
                JOIN ITEM i ON iv.ITEM_ID = i.ITEM_ID
                JOIN ITEM_STATUS ist ON iv.ITEMS_ID = ist.ITEMS_ID
                WHERE iv.ITEMS_ID = (SELECT ITEMS_ID FROM ITEM_STATUS WHERE ITEMS_DESCRIPT = 'RELEASED')
                ORDER BY i.ITEM_NAME;";
            $stmt = $conn->query($query);
            $variations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $variations;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching item variations. " . $e->getMessage();
            return [];
        }
    }
    
    public function getAllReleasedandOoSItemVariations() {
        $conn = $this->openConnection();
    
        try {
            $query = "SELECT iv.ITEMV_ID, i.ITEM_ID, i.ITEM_NAME, icol.ITEM_COL_ID, icol.ITEM_COL_DESCRIPT, isz.ITEM_SIZE_ID, isz.ITEM_SIZE_DESCRIPT, 
                iv.ITEMV_PRICE, iv.ITEMV_STOCKED_QTY, iv.ITEMV_QOH, iv.ITEMV_ADDED, iv.ITEMS_ID, ist.ITEMS_DESCRIPT
                FROM ITEM_VARIATION iv
                JOIN ITEM_SIZE isz ON iv.ITEM_SIZE_ID = isz.ITEM_SIZE_ID
                JOIN ITEM_COLOR icol ON iv.ITEM_COL_ID = icol.ITEM_COL_ID
                JOIN ITEM i ON iv.ITEM_ID = i.ITEM_ID
                JOIN ITEM_STATUS ist ON iv.ITEMS_ID = ist.ITEMS_ID
                WHERE iv.ITEMS_ID IN (SELECT ITEMS_ID FROM ITEM_STATUS WHERE ITEMS_DESCRIPT IN ('RELEASED', 'OUT OF STOCK'))
                ORDER BY i.ITEM_NAME;";
            $stmt = $conn->query($query);
            $variations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $variations;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching item variations. " . $e->getMessage();
            return [];
        }
    } 
    
}