<?php

class Controller
{

    public function __construct()
    {
        $this->checkLogin();
    }

    public function view ($name, $data = [])
    {
        if (!empty($data))
            extract($data);
        
        $filename = "../app/views/".$name.".view.php";

        if (file_exists($filename)) 
        {
            require $filename;
        } else {
            $filename = "../app/views/404.view.php";
            require $filename;
        }
    }

    protected function checkLogin()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
        }
    }

    protected function redirect($url)
    {
        header("Location: $url");
        exit;
    }
}