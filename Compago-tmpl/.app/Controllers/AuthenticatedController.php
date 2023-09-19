<?php

namespace App\Controllers;

class AuthenticatedController extends \App\Controllers\HtmlController
{
    protected $view = 'dashboard';
    public function __construct(){
        if (isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 'admin')){
            $this->views_before[] = 'menu.admin';
            $this->view = 'admin.dashboard';
        }
        if (isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 'standard')){
            $this->views_before[] = 'menu.standard';
            $this->view = 'standard.dashboard';
        }
    }
    
}
