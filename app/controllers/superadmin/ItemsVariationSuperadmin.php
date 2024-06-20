<?php

class ItemsVariationSuperadmin extends Controller
{
    public function index()
    {   
        $user = new User();
        $itemv = new ItemVariation();
        $item = new Item();
        $item_size = new ItemSize();
        $item_color = new ItemColor();
        $item_status = new ItemStatus();
        $data = [];

        $data['user'] = $user->getUserDetails();

        $items = $item->getAllItems();
        $data['items'] = $items;

        $sizes = $item_size->getAllSizes();
        $data['sizes'] = $sizes;

        $colors = $item_color->getAllColors();
        $data['colors'] = $colors;

        $statuses = $item_status->getAllStatuses();
        $data['statuses'] = $statuses;

        $item_vars = $itemv->getAllItemVariations();
        $data['item_vars'] = $item_vars;

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

            if (isset($_POST['add_itemv'])) {
                $itemv->insert_item_variation($data);
            }

            if (isset($_POST['edit_itemv'])) {
                $itemv->update_item_variation($data);
            }

            if (isset($_POST['del_itemv'])) {
                $itemv->delete_item_var($data);
            }

            // if (empty($itemv->errors)) {
            //     // Redirect to the same page to prevent form resubmission if there are no errors
            //     // header("Location: " . $_SERVER['REQUEST_URI']);
            //     redirect('itemsvariationsuperadmin');
            //     exit();
            // }
        }

        $data['item_vars'] = $itemv->getAllItemVariations();

        $data['errors'] = array_merge(
            $itemv->errors,
            $user->errors
        );
        
        $this->view('superadmin/items-variation-superadmin', $data);
    }
}
