<?php

class OrdersHistoryAdmin extends Controller
{
    public function index()
    {
        $ord_history = new OrderHistory();
        $user = new User();
        $data = [];

        $data['history'] = $ord_history->getOrderHistory();
        $data['user'] = $user->getUserDetails();
        

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
            $ord_history->errors,
            $user->errors
        );

        $this->view('admin/orders-history-admin', $data);
    }
}
