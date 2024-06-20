<?php

class ItemsDetailsSuperadmin extends Controller
{
    public function index()
    {
        $user = new User();
        $item = new Item();
        $item_size = new ItemSize();
        $item_color = new ItemColor();
        $data = [];

        $data['user'] = $user->getUserDetails();
        
        $items = $item->getAllItems();
        $data['items'] = $items;

        $sizes = $item_size->getAllSizes();
        $data['sizes'] = $sizes;

        $colors = $item_color->getAllColors();
        $data['colors'] = $colors;
        
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
            
            if (isset($_POST['add_size'])) {
                $item_size->insert_size($data);
                $data['sizes'] = $item_size->getAllSizes();
            }
            else if (isset($_POST['add_color'])) {
                $item_color->insert_color($data);
                $data['colors'] = $item_color->getAllColors();
            }

            if (isset($_POST['edit_size'])) {
                $item_size->update_size($data);
                $data['sizes'] = $item_size->getAllSizes();
            }
            else if (isset($_POST['edit_col'])) {
                $item_color->update_color($data);
                $data['colors'] = $item_color->getAllColors();
            }

            if (isset($_POST['del_size'])) {
                $item_size->delete_size($data);
                $data['sizes'] = $item_size->getAllSizes();
            }
            else if (isset($_POST['del_col'])) {
                $item_color->delete_color($data);
                $data['colors'] = $item_color->getAllColors();
            }
        }

        $data['errors'] = array_merge(
            $item_size->errors,
            $item_color->errors,
            $user->errors
        );

        $this->view('superadmin/items-details-superadmin', $data);
    }
}
