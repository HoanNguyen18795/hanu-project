<?php
/**
 * This source code may regarded as a mini PHP framework designed with MVC pattern
 * providing basic CRUD method and some useful components.
 * @author Phan Thế Minh
 * Date: 8/9/2016
 * @copyright Copyright (c) 2016 by Phan Thế Minh - 4C13 Hanoi University, all rights reserved.
 * @version 1.0.0.2
 */

namespace Library\Core;

use PDO;

interface UserModel
{
    /**
     * insertUser() this function will choose/define which data from $_POST method to be inserted to table User
     * @param array $post
     * @return bool
     */
    public function insertUser($post);

    /**
     * @param string $name
     * @param string $password
     * @return object result
     */
    public function getUserLogin($login, $password);

    /**
     * function get user logged in by cookie
     * @param string $token
     * @return object $result
     */
    public function retrieveLoginByToken($token);

    /**
     * function update token
     * @param string $token
     * @param int $id
     * @return boolean
     */
    public function updateToken($token, $id);

    /**
     * update last login time
     * @param \DateTime $time
     * @param int $id
     * @return boolean
     */
    public function updateLastLogin($time, $id);

    /**
     * @param array $post
     * @param int $id
     * @return boolean
     */
    public function modifyUser($post, $id);

    /**
     * @param string $username
     * @return mixed
     */
    public function getUserByUsername($username);


}

interface ProductModel
{
    /**
     * @param array $post
     * @return boolean
     */
    public function insertProduct($post);

    /**
     * @param array $post
     * @param int $id
     * @return boolean
     */
    public function modifyProduct($post, $id);

}

interface ProfileModel
{
    /**
     * @param array $post
     * @return boolean
     */
    public function insertProfile($post, $userId);

    /**
     * @param array $post
     * @param int $id
     * @return boolean
     */
    public function modifyProfile($post, $id);
}

interface ProductDetailModel
{

}

abstract class Model
{
    private $db;
    protected $table;
    protected $primary;
    public $insertedId;

    public function __construct($co)
    {
        $this->db = $co;
    }

    /**
     * findById() return all fields selected of an element id
     * @param int $value_primary
     * @param string $fields
     * @return array
     */
    public function findById($value_primary, $fields = '*')
    {
        if (!empty($value_primary)) {
            $q = "SELECT $fields FROM `" . $this->table . "` WHERE `" . $this->primary . "`='$value_primary'";
            $sql = $this->db->prepare($q);
            $sql->execute();
            $sql->setFetchMode(PDO::FETCH_OBJ);

            return $sql->fetchAll();
        }
        return false;
    }

    /**
     * fetchAll() return all element of table with selected criteria
     * @param string $where
     * @param string $fields
     * @return array
     */
    public function fetchAll($where = 1, $fields = '*')
    {
        $q = "SELECT $fields FROM `" . $this->table . "` WHERE $where";
        $sql = $this->db->prepare($q);
        $sql->execute();
        $sql->setFetchMode(PDO::FETCH_OBJ);
        return $sql->fetchAll();
    }

    /**
     * function allow to join two tables and fetch data
     * @param string $joinTable
     * @param string $joinOn example: $joinOn = 'role.id = user.id_role'
     * @param string $fields example: $field =  'user.username as usn, role.name as rname'
     * @return array
     */
    public function fetchJoinedTable($joinTable, $joinOn, $fields = '*')
    {
        $q = "SELECT $fields FROM `" . $this->table . "` JOIN $joinTable  ON  $joinOn ";
        $sql = $this->db->prepare($q);
        $sql->execute();
        $sql->setFetchMode(PDO::FETCH_OBJ);
        return $sql->fetchAll();
    }

    /**
     * function fetchByClause()
     * @param string $clause
     * @param string $fields
     * @return $array
     */
    public function fetchByClause($clause = 'WHERE 1', $fields = '*')
    {
        $q = "SELECT $fields FROM `" . $this->table . "` $clause ";
        $sql = $this->db->prepare($q);
        $sql->execute();
        $sql->setFetchMode(PDO::FETCH_OBJ);
        return $sql->fetchAll();
    }

    /**
     * insert() allows to add an occurence inside table
     * @param array $data
     * @return boolean
     */
    public function insert($data)
    {
        $fieldsList = '';
        $valuesList = '';

        foreach ($data as $k => $v) {
            $fieldsList .= "`$k`, ";
            $valuesList .= $this->db->quote($v) . ", ";
        }
        $fieldsList = substr($fieldsList, 0, -2);
        $valuesList = substr($valuesList, 0, -2);

        $query = "INSERT INTO `" . $this->table . "` ($fieldsList) VALUES ($valuesList)";
        $sql = $this->db->prepare($query);
        $sql->execute();
        if ($sql) {
            $this->insertedId = $this->db->lastInsertId();
            return true;
        } else {
            return false;
        }
    }

    /**
     * update() allows to update data with selected criteria
     * @param array $data
     * @param string $where
     * @return boolean
     */
    public function update($data, $where)
    {
        if (!empty($where)) {
            $fieldsListAndValue = '';
            foreach ($data as $k => $v) {
                $fieldsListAndValue .= "`$k`=" . $this->db->quote($v) . ", ";
            }
            $fieldsListAndValue = substr($fieldsListAndValue, 0, -2);

            $q = "UPDATE " . $this->table . " SET $fieldsListAndValue WHERE $where";
            $sql = $this->db->prepare($q);
            return $sql->execute();
        }
        return false;
    }

    /**
     * delete() allows you to delete an occurrence of the table with his id
     * @param type $value_primary
     * @return boolean
     */
    public function delete($value_primary)
    {
        if (!empty($value_primary)) {
            $query = "DELETE FROM `" . $this->table . "` WHERE `" . $this->primary . "`='$value_primary' LIMIT 1";
            $sql = $this->db->prepare($query);
            return $sql->execute();
        }
        return false;
    }

    /**
     * delete() allows you to delete all occurrences
     * @param type $where
     * @return boolean
     */
    public function massDelete($where)
    {
        if (!empty($where)) {
            $query = "DELETE FROM `" . $this->table . "` WHERE $where";
            $sql = $this->db->prepare($query);
            return $sql->execute();
        }
        return false;
    }

    /**
     * string hasher with blowfish encryption
     * @param string $input
     * @param int $rounds
     * @param string $salt
     * @return string hashed password
     */
    public function blowfishHasher($input, $rounds = 7, $salt = "ptm")
    {
        $salt_chars = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));
        for ($i = 0; $i < 22; $i++) {
            $salt .= $salt_chars[array_rand($salt_chars)];
        }
        return crypt($input, sprintf('$2a$%02d$', $rounds) . $salt);
    }

    /**
     * Password/Token validator.
     * @param string $password_entered
     * @param string $password_hash
     * @return bool
     */
    public function validHasher($password_entered, $password_hash)
    {
        if (crypt($password_entered, $password_hash) == $password_hash) {
            return true;
        } else {
            return false;
        }
    }
}