<?php

class SaleDeetSuperadmin extends Controller
{
    public function index() 
    {
        $user = new User();
        $sale = new Sale();
        $item_variation = new ItemVariation();
        $salei_item = new SaleItem();
        $salef_freebie = new SaleFreebie();
        $data = [];

        $data['user'] = $user->getUserDetails();
        $data['sales'] = $sale->getAllAddedSales();
        $data['item_var'] = $item_variation->getAllToBeRHandedItemVariations();
        $data['sale_item'] = $salei_item->getAllSaleItem();
        $data['sale_freebie'] = $salef_freebie->getAllSaleFreebie();

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

            if (isset($_POST['add_sale_item'])) {
                $salei_item->insert_sale_item($data);
                $data['sale_item'] = $salei_item->getAllSaleItem();

            } 
            
            if (isset($_POST['add_sale_freebie'])) {
                $salef_freebie->insert_sale_freebie($data);
                $data['sale_freebie'] = $salef_freebie->getAllSaleFreebie();

            } 
            
            if (isset($_POST['delete_sale_item'])) {
                $salei_item->delete_sale_item($data);
                $data['sale_item'] = $salei_item->getAllSaleItem();

            }
            
            if (isset($_POST['delete_sale_freebie'])) {
                $salef_freebie->delete_sale_freebie($data);
                $data['sale_freebie'] = $salef_freebie->getAllSaleFreebie();
            }

            $data['errors'] = array_merge(
                $item_variation->errors,
                $salei_item->errors,
                $salef_freebie->errors,
                $user->errors
            );
        }

        $this->view('superadmin/sale-deet-superadmin', $data);
    }
}
