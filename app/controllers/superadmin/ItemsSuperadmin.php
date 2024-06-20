<?php

class ItemsSuperadmin extends Controller
{
    public function index()
    {
        $user = new User();
        $item = new Item();
        $item_cat = new ItemCategory();
        $data = [];

        $data['user'] = $user->getUserDetails();

        $categories = $item_cat->getAllCategories();
        $data['categories'] = $categories;

        $items = $item->getAllItems();
        $data['items'] = $items;

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

            if (isset($_POST['add_category'])) {
                $item_cat->insert_category($data);
                $data['categories'] = $item_cat->getAllCategories();
            } 
            else if (isset($_POST['add_item'])) {
                $item->insert_item($data);
                $data['items'] = $item->getAllItems();
            }

            if(isset($_POST['edit_cat'])) {
                $item_cat->update_category($data);
                $data['categories'] = $item_cat->getAllCategories();
            }
            else if (isset($_POST['edit_item'])) {
                $item->update_item($data);
                $data['items'] = $item->getAllItems();
            }

            if (isset($_POST['del_cat'])) {
                $item_cat->delete_category($data);
                $data['categories'] = $item_cat->getAllCategories();
            } 
            else if (isset($_POST['del_item'])) {
                $item->delete_item($data);
                $data['items'] = $item->getAllItems();
            }
        }

        $data['errors'] = array_merge(
            $item_cat->errors,
            $item->errors,
            $user->errors
        );

        $this->view('superadmin/items-superadmin', $data);
    }
}
