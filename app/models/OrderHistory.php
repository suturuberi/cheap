<?php

class OrderHistory extends Database {
    public $errors = [];    

    public function getOrderHistory()
    {
        $conn = $this->openConnection();
        try {
            $query = "SELECT
                        o.*, os.*, op.*, c.*,
                        a.admin_fname,
                        a.admin_mname,
                        a.admin_lname,
                        oi.*, iv.*, i.*, ic.*, isz.*, oh.*
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
                        JOIN order_history oh ON o.ord_id = oh.ord_id
                    ORDER BY ORDH_DATE DESC";
    
            $stmt = $conn->query($query);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $orders;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching orders. " . $e->getMessage();
            return [];
        }
    }
    
}