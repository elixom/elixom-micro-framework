<?php

namespace App\Controllers;

class DashboardController extends \App\Controllers\HtmlController
{
    protected $view = 'dashboard';
    public function __construct(){
        if (isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 'admin')){
            $this->views_before[] = 'menu.admin';
            $this->views_before[] = 'admin.toolbox';
            $this->view = 'admin.dashboard';
        }
        if (isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 'standard')){
            $this->views_before[] = 'menu.standard';
            $this->views_before[] = 'standard.toolbox';
            $this->view = 'standard.dashboard';
        }
    }
    
}
