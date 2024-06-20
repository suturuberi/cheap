<?php

class CustomersAdmin extends Controller
{
    public function index()
    {
        $cus = new Customer;
        $user = new User();
        $data = [];

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

            if(isset($_POST['add_cus'])){            
                $admin_id = $_SESSION['user_id'];
                $cus->insert_customer($data, $admin_id); 
            }

            if(isset($_POST['update_cus'])){
                $cus->update_customer($data);
            }

            if(isset($_POST['delete_cus'])){
                $cus->delete_customer($data);
            }
        }

        $data['customers'] = $cus->getAllCustomers();

        $data['errors'] = array_merge(
            $cus->errors,
            $user->errors
        );

        $this->view('admin/customers-admin', $data);
    }
}
