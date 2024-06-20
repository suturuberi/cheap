<?php

class SaleDeetAdmin extends Controller
{
    public function index()
    {
        
        $user = new User();
        $salei_item = new SaleItem();
        $salef_freebie = new SaleFreebie();
        $data = [];

        $data['user'] = $user->getUserDetails();
        $data['sale_item'] = $salei_item->getAllScheduledSaleItem();
        $data['sale_freebie'] = $salef_freebie->getAllScheduledSaleFreebie();

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

        $this->view('admin/sale-deet-admin', $data);
    }
}
