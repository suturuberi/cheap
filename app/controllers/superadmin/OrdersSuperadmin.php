<?php

class OrdersSuperadmin extends Controller
{
    public function index()
    {
        $order = new Order();
        $user = new User();
        $data = [];

        $data['user'] = $user->getUserDetails();

        $orders = $order->getAllOrders();
        $data['orders'] = $orders;
    
        if ($_SERVER["REQUEST_METHOD"]=="POST")
        {
            $data = array_merge($data, $_POST);

            if (isset($_POST['changepass'])) {
                $changePassData = [
                    'c_pass' => $_POST['c_pass'],
                    'n_pass' => $_POST['n_pass'],
                    'cpass' => $_POST['cpass']
                ];
                $user_id = $_SESSION['user_id'];
                $user->change_pass($changePassData, $user_id);
            }
        }

        $data['errors'] = array_merge(
            $order->errors,
            $user->errors
        );

        $this->view('superadmin/orders-superadmin', $data);
    }
}
