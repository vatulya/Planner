<?php

class Application_Model_Group extends Application_Model_Abstract
{

    protected $_modelDb;
    protected $_modelDbUserGroups;

    public function __construct()
    {
        $this->_modelDb = new Application_Model_Db_Groups();
        $this->_modelDbUserGroups = new Application_Model_Db_User_Groups();
    }

    public static function getAllGroups()
    {
        $modelDb = new Application_Model_Db_Groups();
        $groups = $modelDb->getAllGroups();
        return $groups;
    }

    public function getUserGroupsAdmin(array $user)
    {
        $admin = array();
        if (empty($user['role']) || empty($user['id'])) {
            return $admin;
        }
        if ($user['role'] == Application_Model_Auth::ROLE_GROUP_ADMIN) {
            $modelUserGroups = new Application_Model_Db_User_Groups();
            $admin = $modelUserGroups->getUserGroupsAdmin($user['id']);
        }
        return $admin;
    }

    public function getAllUsers($groupId)
    {
        $users = $this->_modelDbUserGroups->getAllUsersId($groupId);
        foreach($users as $key=>$userId) {
            $user = new Application_Model_User();
            $users[$key] = $user->getUserById($userId);
        }
        return $users;
    }
}
