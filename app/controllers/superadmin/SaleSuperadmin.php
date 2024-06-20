<?php

class SaleSuperadmin extends Controller
{
    public function index()
    {
        $user = new User();
        $sale_categ = new SaleCategory();
        $sale = new Sale();
        $data = [];

        $data['user'] = $user->getUserDetails();
        $data['sale_categ'] = $sale_categ->getAllSalesCategory();
        $data['sale'] = $sale->getAllSales();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

            if (isset($_POST['add_sale'])) {
                $sale->insert_sale($data);

            } else if (isset($_POST['delete_sale'])) {
                $sale->delete_sale($data);
            }

            $data['sale'] = $sale->getAllSales();
            $data['sale_categ'] = $sale_categ->getAllSalesCategory();


            $data['errors'] = array_merge(
                $sale->errors,
                $sale_categ->errors,
                $user->errors
            );
        }

        $this->view('superadmin/sale-superadmin', $data);
    }
}
