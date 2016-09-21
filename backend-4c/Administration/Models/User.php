<?php
/**
 * Created by PhpStorm.
 * User: Phan Minh
 * Date: 13/09/2016
 * Time: 11:37 CH
 */

namespace Administration\Models;

use Library\Core\Model as MainModel;

class User extends MainModel
{
    protected $table = 'user';
    protected $primary = 'id';

    public function __construct($co)
    {
        parent::__construct($co);
    }

    /**
     * insertUser() this function will choose/define which data from $_POST method to be inserted to table User
     * @param array $post
     * @return bool
     */
    public function insertUser($post)
    {
        return $this->insert(array(
            'username' => $post['username'],
            'password' => $this->blowfishHasher($post['password']),
            'token' => md5(uniqid() . time()), //create random token
            'id_role' => $post['role'],
            'created' => date("Y:m:d H:i:s")
        ));
    }

    /**
     * TODO getUserLogin
     * @param string $name
     * param string $password
     * @return object result
     */
    public function getUserLogin($login, $password)
    {
        $result = $this->fetchAll("username= '$login'");
        foreach ($result as $k => $v) {
            if ($this->validHasher($password, $result[$k]->password)) {
                return $result[$k];
                break;
            }
        }
        return null;
    }

    /**
     * function get user logged in by cookie
     * @param string $token
     * @return object $result
     */
    public function retrieveLoginByToken($token)
    {
        $result = $this->fetchAll("token= '$token'");
        return isset($result[0]) ? $result[0] : null;
    }

    /**
     * function update token
     * @param $token
     * @param $id
     * @return boolean
     */
    public function updateToken($token, $id)
    {
        return $this->update(array(
            'token' => isset($token) ? $token : null
        ),
            ' id = ' . $id);
    }

    /**
     * update last login time
     * @param $time
     * @param $id
     * @return boolean
     */
    public function updateLastLogin($time, $id)
    {
        return $this->update(array(
            'last_login' => isset($time) ? $time : ''
        ), ' id = ' . $id);
    }

    /**
     *
     */
    public function getAllUserAndRole($table = 'role, profile', $on = 'user.id_role = role.id')
    {
        return $this->fetchJoinedTable($table , $on,  'user.username as usn, role.name as rname' );
    }
}