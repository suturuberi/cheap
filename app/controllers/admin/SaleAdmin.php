<?php

class SaleAdmin extends Controller
{
    public function index()
    {   
        $user = new User();
        $sale = new Sale();
        $data = [];

        $data['user'] = $user->getUserDetails();
        $data['sale'] = $sale->getAllScheduledSales();

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
            $user->errors
        );

        $this->view('admin/sale-admin', $data);
    }
}
