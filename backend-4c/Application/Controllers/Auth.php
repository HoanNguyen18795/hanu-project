<?php
/**
 * Created by PhpStorm.
 * User: Phan Minh
 */

namespace Application\Controllers;

use Application\Controllers\AppController as MainController;
use Library\Tools as Tools;

class Auth extends MainController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function loginAction()
    {
        global $connection;
        $co = $connection->getCo();
        if (!empty($_SESSION['User'])) {
            header('location:/admin');
        }
        $token = md5(uniqid() . time());
        if (!empty($_POST['login']) && !empty($_POST['password'])) {
            $modelUser = new \Administration\Models\User($co);
            $result = $modelUser->getUserLogin($_POST['login'], $_POST['password']);
            $roleModel = new \Administration\Models\Role($co);
            $role = $roleModel->findById($result->id_role);
            if (!empty($result) && !empty($role)) {
                $_SESSION['User']['id'] = $result->id;
                $_SESSION['User']['username'] = $result->username;
                $_SESSION['User']['role_level'] = $role[0]->level;
                $_SESSION['User']['role_name'] = $role[0]->name;
                $_SESSION['User']['token'] = $token;
                if (!empty($_POST['remember']) && $modelUser->updateToken($token, $result->id)) {
                    setcookie("user", $_SESSION['User']['username'], time() + (86400 * 30));
                    setcookie("token", "$token", time() + (86400 * 30));
                    setcookie("token", "$token", time() + (86400 * 30));
                }
                $modelUser->updateLastLogin(date("Y:m:d H:i:s"), $result->id);
                if ($role[0]->level < 2) {
                    header('location:/admin/');
                } else {
                    header('location:/');
                }
            } else {
                $alert = Tools\Alert::render('Wrong username or password!', 'danger');
            }
        }
        $this->addDataView(array('viewTitle' => 'Thành viên',
            'viewSiteName' => 'Đăng Nhập',
            'alert' => (!empty($alert) ? $alert : '')));
        $this->setLayout('login');
    }

    public function logoutAction()
    {
        unset($_SESSION['User']);
        setcookie("user", "", 1);
        setcookie("token", "", 1);
        $this->addDataView(array("viewTitle" => "Đăng xuất"));
        // unset cookies
//        if (isset($_SERVER['HTTP_COOKIE'])) {
//            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
//            foreach($cookies as $cookie) {
//                $parts = explode('=', $cookie);
//                $name = trim($parts[0]);
//                setcookie($name, '', time()-1000);
//                setcookie($name, '', time()-1000, '/');
//            }
//        }
        header("location:/");

    }
}