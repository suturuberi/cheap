<?php

class Order extends Database {
    public $errors = [];    

    private function insert_order_validation($data)
    {
        if (empty($data['cus_id']) || empty($data['itemv_id']) || empty((int)$data['ord_qty']) || empty($data['ordp_id'])) {
            $this->errors['message'] = "All fields required.";
            return false;
        }

        $ord_qty = (int)$data['ord_qty'];

        if ($ord_qty <= 0) {
            $this->errors['message'] = "Order quantity must be greater than zero.";
            return false;
        }

        if (!$this->insert_stock_quantity_validation($data['itemv_id'], $ord_qty)) {
            return false;
        }

        return true;
    }

    private function insert_stock_quantity_validation($itemv_id, $ord_qty)
    {
        $conn = $this->openConnection();
        try {
            $query = "SELECT ITEMV_STOCKED_QTY FROM ITEM_VARIATION WHERE ITEMV_ID = :itemv_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':itemv_id', $itemv_id); 
            $stmt->execute();
            $item_qty = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($item_qty['itemv_stocked_qty'] < $ord_qty) {
                $this->errors['message'] = "Order quantity exceeds available stock.";
                return false;
            }

            return true;
        } catch (PDOException $e) {
            $this->errors['message'] = "Check Stock Quantity Error: " . $e->getMessage();
            return false;
        }
    }

    private function update_if_duplicate_order($cus_id, $itemv_id, $ordp_id, $ord_qty)
    {
        $conn = $this->openConnection();

        try {
            // Check if the order exists with status not "DELIVERED" or "CANCELLED"
            $query = "SELECT o.ORD_ID, oi.ORDI_QTY, os.ORDS_DESCRIPT
                    FROM ORDERS o
                    JOIN ORDER_ITEM oi ON o.ORD_ID = oi.ORD_ID
                    JOIN ORDER_STATUS os ON o.ORDS_ID = os.ORDS_ID
                    WHERE o.CUS_ID = :cus_id 
                    AND oi.ITEMV_ID = :itemv_id 
                    AND o.ORDP_ID = :ordp_id
                    AND os.ORDS_DESCRIPT NOT IN ('DELIVERED', 'CANCELLED')";
                    
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':cus_id', $cus_id);
            $stmt->bindParam(':itemv_id', $itemv_id);
            $stmt->bindParam(':ordp_id', $ordp_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $ord_id = $result['ord_id'];
                $prev_qty = $result['ordi_qty'];
                
                // Update the quantity
                $new_qty = $prev_qty + $ord_qty;
                $updateQuery = "UPDATE ORDER_ITEM SET ORDI_QTY = :new_qty WHERE ORD_ID = :ord_id AND ITEMV_ID = :itemv_id";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bindParam(':new_qty', $new_qty);
                $updateStmt->bindParam(':ord_id', $ord_id);
                $updateStmt->bindParam(':itemv_id', $itemv_id);
                
                if ($updateStmt->execute()) {
                    $this->errors['message'] = "Order is existing and has been updated successfully!";
                } else {
                    $this->errors['message'] = "Failed to update the order item.";
                }
                return true;
            }

            return false;
        } catch (PDOException $e) {
            $this->errors['message'] = "Validation error: " . $e->getMessage();
            return false;
        }
    }

    private function update_stock_quantity_validation($ord_id, $itemv_id, $new_ord_qty) {
        $conn = $this->openConnection();
        try {
            // Get the current quantity of the order
            $orderQtyQuery = "SELECT ORDI_QTY FROM ORDER_ITEM WHERE ORD_ID = :ord_id AND ITEMV_ID = :itemv_id";
            $orderQtyStmt = $conn->prepare($orderQtyQuery);
            $orderQtyStmt->bindParam(':ord_id', $ord_id);
            $orderQtyStmt->bindParam(':itemv_id', $itemv_id);
            $orderQtyStmt->execute();
            $current_ord_qty = $orderQtyStmt->fetchColumn();
    
            // Check if the new order quantity is greater than the available stock
            $itemQtyQuery = "SELECT ITEMV_STOCKED_QTY FROM ITEM_VARIATION WHERE ITEMV_ID = :itemv_id";
            $itemQtyStmt = $conn->prepare($itemQtyQuery);
            $itemQtyStmt->bindParam(':itemv_id', $itemv_id);
            $itemQtyStmt->execute();
            $item_qty = $itemQtyStmt->fetchColumn();
    
            if ($new_ord_qty > $item_qty) {
                $this->errors['message'] = "Order quantity exceeds available stock.";
                return false;
            }
    
            // Check if the new order quantity is lesser than the current order quantity
            // if ($new_ord_qty < $current_ord_qty) {
            //     $this->errors['message'] = "Order quantity cannot be lesser than the current order quantity.";
            //     return false;
            // }
    
            return true;
        } catch (PDOException $e) {
            $this->errors['message'] = "Check Stock Availability Error: " . $e->getMessage();
            return false;
        }
    }
     
    public function order_action_validation($data, $user_id) {
        $conn = $this->openConnection();
        try {
            // Retrieve the admin ID associated with the order
            $get_admin_query = "SELECT ADMIN_ID FROM ORDERS WHERE ORD_ID = :ord_id";
            $get_admin_stmt = $conn->prepare($get_admin_query);
            $get_admin_stmt->bindParam(':ord_id', $data['ord_id']);
            $get_admin_stmt->execute();
            $order_admin_id = $get_admin_stmt->fetchColumn();
    
            // Check if the user is authorized to delete the order (matches admin ID)
            if ($order_admin_id == $user_id) {
                return true; 
            } else {
                $this->errors['message'] = "Unauthorized to execute this action.";
                return false; 
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not validate order action. " . $e->getMessage();
            return false; 
        }
    }
    
    //MAIN FUNCTION
    public function insert_order($data, $user_id){
        $conn = $this->openConnection();
        if (isset($_POST['add_order'])) {
            if (!$this->insert_order_validation($data, $user_id)){
                return;
            }

            $ord_qty = (int)$data['ord_qty'];
            $ordp_id = $data['ordp_id'];
            $cus_id = $data['cus_id'];
            $itemv_id = $data['itemv_id'];

            if ($this->update_if_duplicate_order($cus_id, $itemv_id, $ordp_id, $ord_qty)) {
                return;
            }

            try {

                // Calculate the total order amount based on item price and quantity
                $itemPriceQuery = "SELECT ITEMV_PRICE FROM ITEM_VARIATION WHERE ITEMV_ID = :itemv_id";
                $priceStmt = $conn->prepare($itemPriceQuery);
                $priceStmt->bindParam(':itemv_id', $itemv_id);
                $priceStmt->execute();
                $itemPrice = $priceStmt->fetchColumn();

                $ord_total = $itemPrice * $ord_qty;

                // Insert into the ORDERS table
                $stmt = $conn->prepare("INSERT INTO ORDERS (ORD_TOTAL, ORDP_ID, CUS_ID, ADMIN_ID)
                                        VALUES (:ord_total, :ordp_id, :cus_id, :user_id)");
                $stmt->bindParam(':ord_total', $ord_total);
                $stmt->bindParam(':ordp_id', $ordp_id);
                $stmt->bindParam(':cus_id', $cus_id);
                $stmt->bindParam(':user_id', $user_id);

                if ($stmt->execute()) {
                    $ord_id = $conn->lastInsertId();

                    // Insert into ORDER_ITEM table
                    $itemStmt = $conn->prepare("INSERT INTO ORDER_ITEM (ORD_ID, ITEMV_ID, ORDI_QTY)
                                                VALUES (:ord_id, :itemv_id, :ord_qty)");
                    $itemStmt->bindParam(':ord_id', $ord_id);
                    $itemStmt->bindParam(':itemv_id', $itemv_id);
                    $itemStmt->bindParam(':ord_qty', $ord_qty);

                    if ($itemStmt->execute()) {
                        $this->errors['message'] = "Order created successfully!";
                    } else {
                        $this->errors['message'] = "Failed to create order item.";
                    }
                } else {
                    $this->errors['message'] = "Failed to create order.";
                }
            } catch (PDOException $e) {
                $this->errors['message'] = "Could not add order. " . $e->getMessage();
            }
        }
    }

    public function edit_order($data, $user_id) {
        $conn = $this->openConnection();
    
        if (!$this->order_action_validation($data, $user_id)) {
            return;
        }
    
        try {
            $ord_qty = (int)$data['ord_qty'];
            $ord_id = $data['ord_id']; 
            $itemv_id = $data['itemv_id'];
            $ords_id = $data['ords_id'];

            // var_dump($ords_id);
            // var_dump($ord_id);
            // Check if the order quantity exceeds the available stock
            if (!$this->update_stock_quantity_validation($ord_id, $itemv_id, $ord_qty)) {
                return;
            }

            // Update order item quantity
            $this->updateOrderItemQty($ord_qty, $ord_id);
            
            // Update order status
            $this->updateOrderStatus($ords_id, $ord_id);
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not update order quantity. " . $e->getMessage();
        }
    }
    
    private function updateOrderItemQty($ord_qty, $ord_id) {
        $con = $this->openConnection();
        try {            
            $updateOrderItemQuery = "UPDATE ORDER_ITEM SET ORDI_QTY = :ord_qty WHERE ORD_ID = :ord_id";
            $updateOrderItemStmt = $con->prepare($updateOrderItemQuery);
            $updateOrderItemStmt->bindParam(':ord_qty', $ord_qty);
            $updateOrderItemStmt->bindParam(':ord_id', $ord_id);
    
            if ($updateOrderItemStmt->execute()) {
                $this->errors['message'] = "Order quantity updated successfully!";
            } else {
                $this->errors['message'] = "Failed to update order quantity.";
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Error updating order quantity: " . $e->getMessage();
        }
    }
    
    private function updateOrderStatus($ords_id, $ord_id) {
        $conn = $this->openConnection();
        try {
            
            $updateOrderStatusQuery = "UPDATE ORDERS SET ORDS_ID = :ords_id WHERE ORD_ID = :ord_id";
            $updateOrderStatusStmt = $conn->prepare($updateOrderStatusQuery);
            $updateOrderStatusStmt->bindParam(':ords_id', $ords_id);
            $updateOrderStatusStmt->bindParam(':ord_id', $ord_id);
    
            if ($updateOrderStatusStmt->execute()) {
                $this->errors['message'] = "Order status updated successfully!";
            } else {
                $this->errors['message'] = "Failed to update order status.";
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Error updating order status: " . $e->getMessage();
        }
    }
    
    public function delete_order($data, $user_id) {
        $conn = $this->openConnection();

        if (!$this->order_action_validation($data, $user_id)) {
            return;
        }

        try {
            $delete_query = "DELETE FROM ORDERS WHERE ORD_ID = :ord_id";
            $stmt = $conn->prepare($delete_query);
            $stmt->bindParam(':ord_id', $data['ord_id']);

            if ($stmt->execute()) {
                $this->errors['message'] = "Order deleted successfully!";
            } else {
                $this->errors['message'] = "Failed to delete order.";
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not delete order." . $e->getMessage();
        }
    }

    // public function getAllStatus() {
    //     $conn = $this->openConnection();

    //     try {
    //         $query = "SELECT * FROM ORDER_STATUS";
    //         $stmt = $conn->query($query);
    //         $status = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //         return $status;

    //     } catch (PDOException $e) {
    //         $this->errors['message'] = "Error fetching status. " . $e->getMessage();
    //         return [];
    //     }
    // }

    public function getAllStatusExceptToBeDelivered() {
        $conn = $this->openConnection();
    
        try {
            $query = "SELECT * FROM ORDER_STATUS WHERE ORDS_DESCRIPT != 'TO BE DELIVERED'";
            $stmt = $conn->query($query);
            $status = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $status;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching status. " . $e->getMessage();
            return [];
        }
    }
    
    public function getAllPaymentMethod() {
        $conn = $this->openConnection();
        try {
            $query = "SELECT * FROM ORD_PAY_METHOD";
            $stmt = $conn->query($query);
            $pay_method = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $pay_method;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching payment method. " . $e->getMessage();
            return [];
        }
    }

    public function getAllOrders()
    {
        $conn = $this->openConnection();
        try {
            $query = "SELECT
                        o.*, os.*, op.*, c.*,
                        -- c.cus_fname,
                        -- c.cus_mname,
                        -- c.cus_lname,
                        a.admin_fname,
                        a.admin_mname,
                        a.admin_lname,
                        oi.*, iv.*, i.*, ic.*, isz.*
                    FROM
                        orders o
                        JOIN order_status os ON o.ords_id = os.ords_id
                        JOIN ord_pay_method op ON o.ordp_id = op.ordp_id
                        JOIN customer c ON o.cus_id = c.cus_id
                        JOIN admin a ON o.admin_id = a.admin_id
                        JOIN order_item oi ON o.ord_id = oi.ord_id
                        JOIN item_variation iv ON oi.itemv_id = iv.itemv_id
                        JOIN item i ON iv.item_id = i.item_id
                        JOIN item_color ic ON iv.item_col_id = ic.item_col_id
                        JOIN item_size isz ON iv.item_size_id = isz.item_size_id
                    WHERE
                        os.ords_descript = 'TO BE DELIVERED'
                    ORDER BY ORD_ADDED DESC";
                      
            $stmt = $conn->query($query);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $orders;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching orders. " . $e->getMessage();
            return [];
        }
    }
    
}

