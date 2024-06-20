<?php

class OrdersAdmin extends Controller
{
    public function index() {
        $user = new User();
        $order = new Order();
        $customer = new Customer();
        $item_variations = new ItemVariation();
        $schedule = new Schedule();
        $data = [];
    
        $data['user'] = $user->getUserDetails();
    
        $orders = $order->getAllOrders();
        $data['orders'] = $orders;
    
        $status = $order->getAllStatusExceptToBeDelivered();
        $data['status'] = $status;
    
        $pay_method = $order->getAllPaymentMethod();
        $data['pay_method'] = $pay_method;
    
        $cus = $customer->getAllCustomersOrderByName();
        $data['customer'] = $cus;
    
        $item_var = $item_variations->getAllReleasedItemVariations();
        $data['item_var'] = $item_var;
    
        // Get the current admin's schedule
        $user_id = $_SESSION['user_id'];
        $admin_schedule = $schedule->getAdminSchedule($user_id); // Implement this method in Schedule model
    
        // Check if the current time is within the admin's schedule
        $is_admin_scheduled = $this->isAdminScheduled($admin_schedule);
    
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $data = array_merge($data, $_POST);
    
            if (!$is_admin_scheduled) {
                $data['errors'][] = 'You are not authorized to perform this action at this time.';
            } else {
                if (isset($_POST['changepass'])) {
                    $changePassData = [
                        'c_pass' => $_POST['c_pass'],
                        'n_pass' => $_POST['n_pass'],
                        'cpass' => $_POST['cpass']
                    ];
                    $user_id = $_SESSION['user_id'];
                    $user->change_pass($changePassData, $user_id);
                }
    
                if (isset($_POST['add_order'])) {
                    if ($this->isAdminAuthorizedForItemOrSale($user_id, $_POST)) {
                        $order->insert_order($data, $user_id);
                        $data['orders'] = $order->getAllOrders();
                    } else {
                        $data['errors'][] = 'You are not authorized to add an order with this item variation or sale.';
                    }
                }
    
                if (isset($_POST['edit_order'])) {
                    if ($this->isAdminAuthorizedForItemOrSale($user_id, $_POST)) {
                        $order->edit_order($data, $user_id);
                        $data['orders'] = $order->getAllOrders();
                    } else {
                        $data['errors'][] = 'You are not authorized to edit an order with this item variation or sale.';
                    }
                }
    
                if (isset($_POST['delete_order'])) {
                    $order->delete_order($data, $user_id);
                    $data['orders'] = $order->getAllOrders();
                }
            }
        }
    
        $data['errors'] = array_merge(
            $order->errors,
            $customer->errors,
            $user->errors,
            isset($data['errors']) ? $data['errors'] : []
        );
    
        $this->view('admin/orders-admin', $data);
    }

    private function isAdminScheduled($admin_schedule) {
        if (empty($admin_schedule)) {
            return false;
        }
    
        $current_time = new DateTime();
    
        foreach ($admin_schedule as $schedule) {
            $start_time = new DateTime($schedule['start_time']);
            $end_time = new DateTime($schedule['end_time']);
    
            if ($current_time >= $start_time && $current_time <= $end_time) {
                return true;
            }
        }
    
        return false;
    }

    private function isAdminAuthorizedForItemOrSale($admin_id, $data) {
        $schedule = new Schedule();

        if (isset($data['itemv_id'])) {
            $itemv_id = $data['itemv_id'];
            $schedules = $schedule->getAllRegularScheduleByAdmin($admin_id);
            foreach ($schedules as $sched) {
                if ($sched['itemv_id'] == $itemv_id) {
                    return true;
                }
            }
        }

        if (isset($data['sale_id'])) {
            $sale_id = $data['sale_id'];
            $schedules = $schedule->getAllSaleScheduleByAdmin($admin_id);
            foreach ($schedules as $sched) {
                if ($sched['sale_id'] == $sale_id) {
                    return true;
                }
            }
        }

        return false;
    }
}
