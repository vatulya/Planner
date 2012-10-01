<?php

class Application_Model_User extends Application_Model_Abstract
{

    protected $_modelDb;

    protected $_hiddenFields = array(
        'password',
    );

    public function __construct()
    {
        $this->_modelDb = new Application_Model_Db_Users();
    }

    protected function _filterHiddenFields(array $array)
    {
        foreach ($this->_hiddenFields as $field) {
            if (isset($array[$field])) {
                unset($array[$field]);
            }
        }
        return $array;
    }

    public function getUserByEmail($email)
    {
        $user = $this->_modelDb->getUserByEmail($email);
        $user = $this->_filterHiddenFields($user);
        return $user;
    }

    public function getUserById($id, $filterFields = true)
    {
        $user = $this->_modelDb->getUserById($id);
        if ($filterFields) {
            $user = $this->_filterHiddenFields($user);
        }
        return $user;
    }

    public function getAllUsers()
    {
        $users = $this->_modelDb->getAllUsers();
        foreach ($users as $key => $user) {
            $user = $this->_filterHiddenFields($user);
            $users[$key] = $user;
        }
        return $users;
    }

}