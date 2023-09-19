<?php

namespace App\Controllers;
use ZS;
use Compago\Tools\Password;


class AuthController extends \App\Controllers\HtmlController
{
    protected $view = 'login';
    public function logout($request, $response, $matchRoute) {
        $_SESSION['name'] = null;
        $_SESSION['user_type'] = null;
        $_SESSION['user_id'] = null;
        $_SESSION = array();
        return $response->withStatus(302)->withHeader('Location', url('/login'));
    }
    public function forgot($request, $response, $matchRoute) {
        return 'this page will provide password recovery';
    }
    public function register($request, $response, $matchRoute) {
        $this->view = 'public.signup';
        
        return $this($request, $response, $matchRoute);
    }
    private function setUser($user) {
        $_SESSION['name'] = $user->display_name;
        $_SESSION['user_type'] = $user->user_type;
        $_SESSION['user_id'] = $user->id;
    }
    public function createUser($request, $response, $matchRoute) {
        
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
            //fail
        }
        
        $data = array();
        $data['user_type'] = 'standard';
        $data['display_name'] = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $data['email'] = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        
        
        if (isset($_POST['access_code'])){
            $data['access_code'] = filter_var($_POST['access_code'], FILTER_SANITIZE_STRING);
        } else {
            $data['access_code'] = null;
        }
        //TODO use access code to set default user_type and addto school or to teacher
        //random password
        $data['pwd'] = Password::hash('plain');
        
        $new_user_id = ZS::getUser()->getEditor()->write($data);
        $user =  ZS::getUser()->get($new_user_id);
        self::setUser($user);
        return $response->withStatus(302)->withHeader('Location', url('/dashboard'));
    }
    public function authenticate($request, $response, $matchRoute) {
        //$data = $request->getParsedBody();
        $data = $_POST;
        $uname = filter_var($data['form-username'], FILTER_SANITIZE_STRING);
        $pass = filter_var($data['form-password'], FILTER_SANITIZE_STRING);
        
        $goto = 'login';
        if ($uname == 'admin'  && $uname == $pass){
            //$this->container->flash->addMessage('Message', 'admin is logged in');
            $_SESSION['name'] = $uname;
            $_SESSION['user_type'] = 'admin';
            $_SESSION['user_id'] = 1;
        } elseif ($uname == 'user' && $uname == $pass){
            //$this->container->flash->addMessage('Message', 'user is logged in');
            $_SESSION['name'] = $uname;
            $_SESSION['user_type'] = 'standard';
            $_SESSION['user_id'] = 2;
        } else {
            $filter = User::getUser()->getFilter();
            $filter->setLogin($uname);
            $users = User::getUser()->getCollection($filter);
            foreach ($users as $user){
                if (Password::verify($pass,$user->pwd)){
                    self::setUser($user);
                    $_SESSION['username'] = $uname;
                    break;
                }
            }
            
            
        }
        if (!empty($_SESSION['user_id'])){
            return $response->withStatus(302)->withHeader('Location', url('/dashboard'));
        } else {
            //$this->container->flash->addMessage('Error', 'Unable logged in');
            return $response->withStatus(302)->withHeader('Location', url('/login?failed'));
        }
        
   }
}
