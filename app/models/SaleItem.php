<?php

class SaleItem extends Database {
    public $errors = [];  

    public function insert_sale_item_validation($data) {
        $conn = $this->openConnection();
        try {
            if (empty($data['salei_item_name']) || empty($data['salei_og_price']) || empty($data['salei_price']) || empty($data['salei_qty'])) {
                $this->errors['message'] = "All fields are required.";
                return false;
            }

            if (floatval($data['salei_og_price']) < floatval($data['salei_price'])) {
                $this->errors['message'] = "Sale Price must be less than the Original Price";
                return false;
            }

            //Check Duplicates
            $itemv_id = $data['salei_item_name'];      
            $duplicate_query = "SELECT COUNT(*) FROM SALE_ITEM WHERE ITEMV_ID = :itemv_id";
            $stmt = $conn->prepare($duplicate_query);
            $stmt->bindParam(':itemv_id', $itemv_id);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            if ($count > 0) {
                $this->errors['message'] = "Duplicate sale item found.";
                return false;
            }

            return true; 
        } catch (PDOException $e) {
            $this->errors['message'] = "Error validating sale. " . $e->getMessage();
            return false;
        }
        return true;
    }

    public function insert_sale_item($data) {
        $conn = $this->openConnection();

        if (!$this->insert_sale_item_validation($data)) {
            return;
        }

        try {
            
            $salei_price = floatval($data['salei_price']);
            $salei_itemv_id = $data['salei_item_name'];
            $sale_id = $data['sale_id'];

            $insert_query = "INSERT INTO SALE_ITEM (SALEI_PRICE, ITEMV_ID, SALE_ID) 
                             VALUES (:salei_price, :itemv_id, :sale_id)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bindParam(':salei_price', $salei_price);
            $stmt->bindParam(':itemv_id', $salei_itemv_id);
            $stmt->bindParam(':sale_id', $sale_id);

            if ($stmt->execute()) {
                $this->errors['message'] = "Sale item added successfully!";
            } else {
                $this->errors['message'] = "Failed to add sale item.";
            }

        } catch (PDOException $e) {
            $this->errors['message'] = "Could not add sale item." . $e->getMessage();
        }
    }

    public function delete_sale_item_validation($data) {
        $conn = $this->openConnection();

        try {
            // Check if the sale is scheduled
            $query = "SELECT COUNT(*) FROM SCHEDULE WHERE SALE_ID = (SELECT SALE_ID FROM SALE_ITEM WHERE SALEI_ID = :salei_id)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':salei_id', $data['salei_id']);
            $stmt->execute();
            $count = $stmt->fetchColumn();
    
            if ($count > 0) {
                $this->errors['message'] = "Cannot delete this sale item because it is part of a scheduled sale.";
                return false;
            }
    
            return true;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error validating sale item deletion. " . $e->getMessage();
            return false;
        }
    }

    public function delete_sale_item($data) {
        $conn = $this->openConnection();
        
        if (!$this->delete_sale_item_validation($data)) {
            return;
        }

        try {
            $delete_query = "DELETE FROM SALE_ITEM WHERE SALEI_ID = :salei_id";
            $stmt = $conn->prepare($delete_query);
            $stmt->bindParam(':salei_id', $data['salei_id']);
    
            if ($stmt->execute()) {
                $this->errors['message'] = "Sale item deleted successfully!";
            } else {
                $this->errors['message'] = "Failed to delete sale item.";
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not delete sale item." . $e->getMessage();
        }
    }

    public function getAllSaleItem() {
        $conn = $this->openConnection();
        try {
            $query = "SELECT SI.*, IV.*, I.ITEM_NAME, IC.ITEM_COL_DESCRIPT, ISZ.ITEM_SIZE_DESCRIPT, S.SALE_DESCRIPT
                      FROM SALE_ITEM SI
                      JOIN ITEM_VARIATION IV ON SI.ITEMV_ID = IV.ITEMV_ID
                      JOIN ITEM_SIZE ISZ ON IV.ITEM_SIZE_ID = ISZ.ITEM_SIZE_ID
                      JOIN ITEM_COLOR IC ON IV.ITEM_COL_ID = IC.ITEM_COL_ID
                      JOIN ITEM I ON IV.ITEM_ID = I.ITEM_ID
                      JOIN SALE S ON SI.SALE_ID = S.SALE_ID
                      ORDER BY SALEI_ADDED, SALE_DESCRIPT";
            $stmt = $conn->query($query);
            $sale_item = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $sale_item;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching sale item. " . $e->getMessage();
            return [];
        }
    }

    public function getAllScheduledSaleItem() {
        $conn = $this->openConnection();
        try {
            $query = "SELECT SI.*, IV.*, I.ITEM_NAME, IC.ITEM_COL_DESCRIPT, ISZ.ITEM_SIZE_DESCRIPT, S.SALE_DESCRIPT
                      FROM SALE_ITEM SI
                      JOIN ITEM_VARIATION IV ON SI.ITEMV_ID = IV.ITEMV_ID
                      JOIN ITEM_SIZE ISZ ON IV.ITEM_SIZE_ID = ISZ.ITEM_SIZE_ID
                      JOIN ITEM_COLOR IC ON IV.ITEM_COL_ID = IC.ITEM_COL_ID
                      JOIN ITEM I ON IV.ITEM_ID = I.ITEM_ID
                      JOIN SALE S ON SI.SALE_ID = S.SALE_ID
                      JOIN SALE_STATUS SS ON S.SALES_ID = SS.SALES_ID
                      WHERE SS.SALES_DESCRIPT = 'SCHEDULED'
                      ORDER BY SALE_DESCRIPT";
            $stmt = $conn->query($query);
            $scheduled_sale_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $scheduled_sale_items;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching scheduled sale items. " . $e->getMessage();
            return [];
        }
    }
    
}