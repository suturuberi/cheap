<?php

class CustomersSuperadmin extends Controller
{
    public function index()
    {
        $cus = new Customer();
        $user = new User();
        $data = [];

        $data['customers'] = $cus->getAllCustomersWithAddedAdmin();
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
            $cus->errors,
            $user->errors
        );
        
        $this->view('superadmin/customers-superadmin', $data);
    }
}
