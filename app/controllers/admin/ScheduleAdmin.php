<?php

class ScheduleAdmin extends Controller
{
    public function index()
    {
        $user = new User();
        $schedule = new Schedule();
        $data = [];

        $admin_id = $_SESSION['user_id'];
        $data['user'] = $user->getUserDetails();
        
        $schedules = $schedule->getSchedulesByAdmin($admin_id);
        $data['schedules'] = $schedules;

        $regularSchedules = $schedule->getAllRegularScheduleByAdmin($admin_id);
        $data['regularSchedules'] = $regularSchedules;

        $saleSchedules = $schedule->getAllSaleScheduleByAdmin($admin_id);
        $data['saleSchedules'] = $saleSchedules;

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

        $this->view('admin/schedule-admin', $data);
    }
}
