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
                $oldGroup = $this->_modelDb->getGroupById($group['id']);
                if (empty($oldGroup)) {
                    return $result;
                }
                if ($oldGroup == $group) {
                    $result = true;
                    return $result;
                }
                $result = $this->_modelDb->updateGroup($group['id'], $group);
            } else {
                $result = $this->_modelDb->insertGroup($group);
            }
        }
        return $result;
    }

    public function deleteGroup($groupId)
    {
        if (Application_Model_Auth::getRole() >= Application_Model_Auth::ROLE_ADMIN) {
            $this->_modelDb->deleteGroup($groupId);
        }
        return true;
    }

    static public function getAllowedColors()
    {
        $colors = array(
            'FFFFFF','DCDCDC','D3D3D3','C0C0C0','808080','708090',
            'FFFFE0','F5F5DC','FFF8DC','FFFACD','FAFAD2','FFEFD5',
            'FAEBD7','FFE4C4','FFDEAD','FFC0CB','FFE4E1','E6E6FA',
            'D8BFD8','B0C4DE','ADD8E6','B0E0E6','AFEEEE','E0FFFF',
            'F0F8FF','F0FFFF','F0FFF0','90EE90','3CB371','DA70D6',
        );
        return $colors;
    }

}
