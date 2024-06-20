<?php

class ExpensesSuperadmin extends Controller
{
    public function index()
    {
        $user = new User();
        $expenses = new Expenses();
        $item = new Item();
        $data = [];

        $data['user'] = $user->getUserDetails();

        $items = $item->getAllItems();
        $data['items'] = $items;

        $exp = $expenses->getAllExpenses();
        $data['exp'] = $exp;

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $data = array_merge($data, $_POST);

            if (isset($_POST['add_exp'])) {
                $expenses->insert_expenses($data);
                $data['exp']= $expenses->getAllExpenses();  
            }

            if (isset($_POST['del_exp'])) {
                $expenses->delete_expenses($data);
                $data['exp']= $expenses->getAllExpenses();  
            }

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
            $expenses->errors,
            $user->errors
        );

        $this->view('superadmin/expenses-superadmin', $data);
    }
}
