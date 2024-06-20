<?php

class Item extends Database {
    public $errors = [];

    public function insert_item_validation($data) {
        $conn = $this->openConnection();

        try {
            $item_description = strtoupper($data['item_d']);
            $item_category_id = $data['item_category'];

            if (empty($item_category_id)) {
                $this->errors['message'] = "Item category cannot be empty.";
                return false;
            }

            $check_query = "SELECT COUNT(*) FROM ITEM WHERE ITEM_NAME = :item_name";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bindParam(':item_name', $item_description);
            $check_stmt->execute();
            $count = $check_stmt->fetchColumn();

            if ($count > 0) {
                $this->errors['message'] = "Item already exists.";
                return false;
            }

            return true;

        } catch (PDOException $e) {
            $this->errors['message'] = "Error validating item." . $e->getMessage();
            return false;
        }
    }

    public function update_item_validation($data) {
        $conn = $this->openConnection();
    
        try {
            $item_id = $data['item_id'];
            $item_description = strtoupper($data['item_d']);
            $item_category_id = $data['item_category']; // Get item category from the data
    
            $check_query = "SELECT COUNT(*) FROM ITEM WHERE ITEM_NAME = :item_name AND ITEM_CAT_ID = :item_category_id AND ITEM_ID != :item_id";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bindParam(':item_name', $item_description);
            $check_stmt->bindParam(':item_id', $item_id);
            $check_stmt->bindParam(':item_category_id', $item_category_id);
            $check_stmt->execute();
            $count = $check_stmt->fetchColumn();
    
            if ($count > 0) {
                $this->errors['message'] = "Item already exists in the same category.";
                return false;
            }
    
            return true;
    
        } catch (PDOException $e) {
            $this->errors['message'] = "Error validating item." . $e->getMessage();
            return false;
        }
    }
    

    public function delete_item_validation($data) {
        $conn = $this->openConnection();
    
        try {
            $item_id = $data['item_id'];
    
            // Check if there are associated sizes
            $check_size_query = "SELECT COUNT(*) FROM ITEM_SIZE WHERE ITEM_ID = :item_id";
            $check_size_stmt = $conn->prepare($check_size_query);
            $check_size_stmt->bindParam(':item_id', $item_id);
            $check_size_stmt->execute();
            $size_count = $check_size_stmt->fetchColumn();
    
            // Check if there are associated colors
            $check_color_query = "SELECT COUNT(*) FROM ITEM_COLOR WHERE ITEM_ID = :item_id";
            $check_color_stmt = $conn->prepare($check_color_query);
            $check_color_stmt->bindParam(':item_id', $item_id);
            $check_color_stmt->execute();
            $color_count = $check_color_stmt->fetchColumn();
    
            // Check if there are associated variations
            $check_variation_query = "SELECT COUNT(*) FROM ITEM_VARIATION WHERE ITEM_ID = :item_id";
            $check_variation_stmt = $conn->prepare($check_variation_query);
            $check_variation_stmt->bindParam(':item_id', $item_id);
            $check_variation_stmt->execute();
            $variation_count = $check_variation_stmt->fetchColumn();
    
            // If any of the counts are greater than 0, prevent deletion
            if ($size_count > 0 || $color_count > 0 || $variation_count > 0) {
                $this->errors['message'] = "Cannot delete item because it has associated size, color, or item variation.";
                return false;
            }
    
            return true;
    
        } catch (PDOException $e) {
            $this->errors['message'] = "Error validating item deletion." . $e->getMessage();
            return false;
        }
    }
    
    

    public function insert_item($data) {
        $conn = $this->openConnection();

        if (!$this->insert_item_validation($data)) {
            return false;
        }

        try {
            $item_description = strtoupper($data['item_d']);
            $item_category_id = $data['item_category'];

            $insert_query = "INSERT INTO ITEM (ITEM_NAME, ITEM_CAT_ID) 
                             VALUES (:item_d, :item_category)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bindParam(':item_d', $item_description);
            $stmt->bindParam(':item_category', $item_category_id);

            if ($stmt->execute()) {
                $this->errors['message'] = "Item added successfully!";
            } else {
                $this->errors['message'] = "Failed to add color.";
            }

        } catch (PDOException $e) {
            $this->errors['message'] = "Could not add item." . $e->getMessage();
        }
    }

    public function update_item($data) {
        $conn = $this->openConnection();

        if (!$this->update_item_validation($data)) {
            return false;
        }
    
        try {
            $item_id = $data['item_id'];
            $item_description = strtoupper($data['item_d']);
    
            $update_query = "UPDATE ITEM 
                             SET ITEM_NAME = :item_d
                             WHERE ITEM_ID = :item_id";
            $stmt = $conn->prepare($update_query);
            $stmt->bindParam(':item_d', $item_description);
            $stmt->bindParam(':item_id', $item_id);
    
            if ($stmt->execute()) {
                $this->errors['message'] = "Item updated successfully!";
            } else {
                $this->errors['message'] = "Failed to update item.";
            }
    
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not update item." . $e->getMessage();
        }
    }
    

    public function delete_item($data) {
        $conn = $this->openConnection();

        if (!$this->delete_item_validation($data)) {
            return false;
        }
    
        try {
            $delete_query = "DELETE FROM ITEM WHERE ITEM_ID = :item_id";
            $stmt = $conn->prepare($delete_query);
            $stmt->bindParam(':item_id', $data['item_id']);
    
            if ($stmt->execute()) {
                $this->errors['message'] = "Item deleted successfully!";
            } else {
                $this->errors['message'] = "Failed to delete item.";
            }
    
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not delete item." . $e->getMessage();
        }
    }
    

    public function getAllItems() {
        $conn = $this->openConnection();

        try {
            $query = "SELECT i.ITEM_ID, i.ITEM_NAME, i.ITEM_ADDED, ic.ITEM_CAT_DESCRIPT
                  FROM ITEM i
                  NATURAL JOIN ITEM_CATEGORY ic
                  ORDER BY i.ITEM_ADDED DESC";
            $stmt = $conn->query($query);
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $categories;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching items. " . $e->getMessage();
            return [];
        }
    }
}