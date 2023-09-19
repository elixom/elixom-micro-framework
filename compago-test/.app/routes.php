<?php
/**
 * @author Edwards
 * @copyright 2010
 */
 

$app = app();
$router = $app->router;
$router->setParameterRegEx('userid','[\d]+');

$router->any('/debug','DebugController@');
$router->any('/logout','AuthController@logout');
$router->any('/dashboard','DashboardController@');

$app::startSession();
$user_type = (isset($_SESSION['user_type']))? $_SESSION['user_type']:'';


require_once 'api-routes.php';
if (($user_type == 'admin')){
    $router->get('/','DashboardController@');
    require_once 'admin-routes.php';
} elseif (($user_type == 'standard')){
    $router->any('/','DashboardController@');
    require_once 'standard-routes.php';
} else {
    
    $router->get('/','HomeController@');
    $router->get('/login','AuthController@');
    $router->get('/forgot','AuthController@forgot');
    $router->get('/register','AuthController@register');
    $router->get('/signup','AuthController@register');
    $router->post('/register','AuthController@createUser');
    $router->post('/signup','AuthController@createUser');
    $router->post('/login','AuthController@authenticate');
}



$router->get('*',function(){
    $view = app()->view('404');
    $arr = [];
    $arr[] = app()->view('layouts.head')->render();
    $arr[] = $view->render();
    $arr[] = app()->view('layouts.foot')->render();
    return implode('',$arr);
});
