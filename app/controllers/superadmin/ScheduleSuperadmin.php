<?php

class ScheduleSuperadmin extends Controller
{
    public function index()
    {   
        $user = new User();
        $schedule = new Schedule();
        $sched_t = new ScheduleType();
        $itemv = new ItemVariation();
        $sale = new Sale();
        $admin = new Admin();
        $data = [];

        $data['user'] = $user->getUserDetails();

        $schedt = $sched_t->getAllScheduleType();
        $data['schedt'] = $schedt;

        $item_vars = $itemv->getAllToBeRHandedItemVariations();
        $data['item_vars'] = $item_vars;

        $sales = $sale->getAllAddedSales();
        $data['sales'] = $sales;

        $sales_s = $sale->getAllScheduledSales();
        $data['sales_s'] = $sales_s;

        $admins = $admin->getAllAdmins();
        $data['admins'] = $admins;

        $schedules = $schedule->getAllSchedule();
        $data['schedules'] = $schedules;

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

            if (isset($_POST['add_sched'])) {
                $schedule->insert_schedule($data);
                $data['schedules'] = $schedule->getAllSchedule();
                $data['sales'] = $sale->getAllAddedSales();
                $data['item_vars'] = $itemv->getAllToBeRHandedItemVariations();
            }

            if (isset($_POST['edit_sched'])) {
                $schedule->edit_schedule($data);
                $data['schedules'] = $schedule->getAllSchedule();
                $data['sales'] = $sale->getAllAddedSales();
                $data['item_vars'] = $itemv->getAllToBeRHandedItemVariations();          
            }

            if (isset($_POST['del_sched'])) {
                $schedule->delete_schedule($data);
                $data['schedules'] = $schedule->getAllSchedule();
                $data['sales'] = $sale->getAllAddedSales();
                $data['item_vars'] = $itemv->getAllToBeRHandedItemVariations();
            }
        }

        $data['regular_schedules'] = $schedule->getAllRegularSchedule();
        $data['sale_schedules'] = $schedule->getAllSaleSchedule();


        //redirect('schedulesuperadmin?success=1');

        $data['errors'] = array_merge(
            $schedule->errors,
            $user->errors
        );
        
        $this->view('superadmin/schedule-superadmin', $data);
    }
}
