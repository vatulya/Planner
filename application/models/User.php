<?php

class Application_Model_User extends Application_Model_Abstract
{

    const USER_CHECK_IN = 'in';
    const USER_CHECK_OUT = 'out';

    const USER_DEFAULT_OWNER = 'Eigen';

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
        if ( ! $user) {
            $user = array();
        }
        $user = $this->_filterHiddenFields($user);
        return $user;
    }

    public function getUserById($id, $filterFields = true)
    {
        $user = $this->_modelDb->getUserById($id);
        if ($filterFields && is_array($user)) {
            $user = $this->_filterHiddenFields($user);
        }
        return $user;
    }

    public function getAllUsers(DateTime $checkingDate = null)
    {
        $users = $this->_modelDb->getAllUsers($checkingDate);
        foreach ($users as $key => $user) {
            $user = $this->_filterHiddenFields($user);
            $users[$key] = $user;
        }
        return $users;
    }

    public function getAllUsersByGroup($groupId, DateTime $checkingDate = null)
    {
        $users = $this->_modelDb->getAllUsersByGroup($groupId, $checkingDate);
        foreach ($users as $key => $user) {
            $user = $this->_filterHiddenFields($user);
            $users[$key] = $user;
        }
        return $users;
    }

    public function userCheck($userId, $check)
    {
        $now = new DateTime();
        $user = $this->_modelDb->getUserById($userId, $now);
        $check = ($check == Application_Model_User::USER_CHECK_IN || $check == Application_Model_User::USER_CHECK_OUT ? $check : null);
        if ( ! $check) {
            return $user;
        }
        $modelUserCheck = new Application_Model_Db_User_Checks();
        $allowed = $modelUserCheck->isAllowedCheckin($user, $check);
        if ($allowed) {
            $modelUserCheck->userCheck($user['id'], $check);
            $user = $this->_modelDb->getUserById($user['id'], $now); // update user data
        }
        return $user;
    }



    public function getParametersByUserId($userId)
    {
        $modelUserParameters = new Application_Model_Db_User_Parameters();
        $userParameters = $modelUserParameters->getParametersByUserId($userId);
        return $userParameters;
    }

    public function savePassword($userId, $newPassword, $currentPassword = '', $force = false)
    {
        /*
         * $currentPassword can be empty if we don't check it. This need if some admin changed password for some user.
         * $force - this param for future logic.
         */
        $user = $this->_modelDb->getUserById($userId);
        $newPassword = trim($newPassword);
        $modelAuth = new Application_Model_Auth();
        $result = false;
        // TODO: refactor it
        if ($force && ! empty($newPassword)) {
            $result = $modelAuth->_changePassword($userId, $newPassword);
        } else {
            if ( ! empty($user)) {
                if ( ! empty($newPassword)) {
                    $currentPasswordEncoded = Application_Model_AuthAdapter::encodePassword($currentPassword);
                    if (empty($currentPassword) || $user['password'] == $currentPasswordEncoded) {
                        $result = $modelAuth->_changePassword($userId, $newPassword);
                    }
                }
            }
        }
        return $result;
    }

    public function saveField($userId, $field, $value)
    {
        $result = $this->_modelDb->saveField($userId, $field, $value);
        return $result;
    }

    public function saveRole($userId, $role)
    {
        $result = false;
        $allowedRoles = Application_Model_Auth::getAllowedRoles();
        if ( ! empty($allowedRoles[$role])) {
            $result = $this->_modelDb->saveRole($userId, $role);
        }
        return $result;
    }

    public function saveGroups($userId, array $groups)
    {
        $modelUserGroups = new Application_Model_Db_User_Groups();
        $result = $modelUserGroups->saveUserGroups($userId, $groups);
        $user = $this->getUserById($userId);
        $adminGroups = $modelUserGroups->getUserGroupsAdmin($userId);
        if ($result) {
            if (count($adminGroups)) {
                if ($user['role'] < Application_Model_Auth::ROLE_GROUP_ADMIN) {
                    $result = $this->saveRole($userId, Application_Model_Auth::ROLE_GROUP_ADMIN);
                }
            } else {
                if ($user['role'] == Application_Model_Auth::ROLE_GROUP_ADMIN) {
                    $result = $this->saveRole($userId, Application_Model_Auth::ROLE_USER);
                }
            }
        }
        return $result;
    }

    public function create(array $user)
    {
        $result = false;
        if ( ! empty($user['email']) && Application_Model_Auth::getRole() >= Application_Model_Auth::ROLE_ADMIN) {
            $user['password'] = Application_Model_AuthAdapter::encodePassword($user['password']);
            $result = $this->_modelDb->insert($user);
        }
        return $result;
    }

    public function setAdmin($userId)
    {
        $user = $this->getUserById($userId);
        $newRole = false;
        if ($user['role'] < Application_Model_Auth::ROLE_ADMIN) {
            $newRole = Application_Model_Auth::ROLE_ADMIN;
        } elseif ($user['role'] == Application_Model_Auth::ROLE_ADMIN) {
            $modelUserGroups = new Application_Model_Db_User_Groups();
            $adminGroups = $modelUserGroups->getUserGroupsAdmin($userId);
            if (count($adminGroups) > 0) {
                $newRole = Application_Model_Auth::ROLE_GROUP_ADMIN;
            } else {
                $newRole = Application_Model_Auth::ROLE_USER;
            }
        }
        $result = $this->_modelDb->setRole($userId, $newRole);
        return $result;
    }

    public function delete($userId)
    {
        $result = $this->_modelDb->delete($userId);
        return $result;
    }

    public function saveRegularWorkHours($userId, $hours)
    {
        $result = false;
        if ($hours > 0 && $hours <= 40) {
            $modelUserParameters = new Application_Model_Db_User_Parameters();
            $userParametersOld = $modelUserParameters->getParametersByUserId($userId);
            $result = $modelUserParameters->setRegularWorkHours($userId, $hours);
            if ($result) {
                $this->recalculateOpenFreeHours($userId, $userParametersOld['regular_work_hours'], $hours);
            }
        }
        return $result;
    }

    public function recalculateOpenFreeHours($userId, $oldHours, $newHours)
    {
        $modelUserParameters = new Application_Model_Db_User_Parameters();
        if ($newHours == 40) {
            $modelParameters = new Application_Model_Db_Parameters();
            $newOpenFreeHours = $modelParameters->getParameter('default_open_free_hours');
        } else {
            $userParametersOld = $modelUserParameters->getParametersByUserId($userId);
            $deltaPercentage = $newHours / $oldHours;
            $newOpenFreeHours = $userParametersOld['open_free_hours'] * $deltaPercentage;
            $newOpenFreeHours = sprintf('%01.2f', $newOpenFreeHours);
        }
        $modelUserParameters->setOpenFreeHours($userId, $newOpenFreeHours);
    }

}
