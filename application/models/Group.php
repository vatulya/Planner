<?php

class Application_Model_Group extends Application_Model_Abstract
{

    protected $_modelDb;

    public function __construct()
    {
        $this->_modelDb = new Application_Model_Db_Groups();
    }

    public function getAllGroups()
    {
        $groups = $this->_modelDb->getAllGroups();
        return $groups;
    }

    public function getGroupById($groupId)
    {
        $group = $this->_modelDb->getGroupById($groupId);
        return $group;
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

    public function saveGroup(array $group) {
        $result = false;
        if (Application_Model_Auth::getRole() >= Application_Model_Auth::ROLE_ADMIN) {
            if ( ! empty($group['id'])) {
                $result = $this->_modelDb->updateGroup($group['id'], $group);
            } else {
                $result = $this->_modelDb->insertGroup($group);
            }
        }
        return $result;
    }

    static public function getAllowedColors()
    {
        $colors = array(
            'FFC0CB','FFFACD','FFFFE0','FAFAD2','FFEFD5','E6E6FA',
            'D8BFD8','98FB98','90EE90','E0FFFF','AFEEEE','B0C4DE',
            'B0E0E6','ADD8E6','FFF8DC','FFEBCD','FFE4C4','FFDEAD',
            'F5DEB3','F0FFF0','F0FFFF','F0F8FF','F5F5DC','FAEBD7',
            'FFE4E1','DCDCDC','D3D3D3','C0C0C0','778899','708090',
        );
        return $colors;
    }

}
