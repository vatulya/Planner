<?php

class Application_Model_User extends Application_Model_Abstract
{

    const USER_CHECK_IN = 'in';
    const USER_CHECK_OUT = 'out';

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

    public function getUserWeekPlanByGroup($userId, $groupId,  $year, $week)
    {
        $weekPlan = array();
        $weekDays = My_DateTime::getWeekDays();
        $dateWeekStart = new My_DateTime($year . 'W' . sprintf("%02d", $week));
        $weekUserPlan = new Application_Model_Db_User_Planning();

        foreach ($weekDays as $numDay=>$nameDay) {
            $date = clone $dateWeekStart;
            $date->modify('+' . $numDay . 'day');
            $date = $date->format('Y-m-d');
            $weekPlan[$nameDay] = $weekUserPlan->getUserDayPlanByGroup($userId, $groupId, $date);
        }
        return $weekPlan;
    }

    public function getUserWorkTimeByGroup($userId, $groupId,  $year, $week)
    {
        $dateWeekStart = new DateTime($year . 'W' . sprintf("%02d", $week));
        $weekUserPlan = new Application_Model_Db_User_Planning();
        $date = $dateWeekStart->format('Y-m-d');
        $workTime = $weekUserPlan->getTotalWorkTimeByGroup($userId, $groupId, $date);

        return $workTime;
    }

    public function getUserDayById($dayId)
    {
        $userDay = new Application_Model_Db_User_Planning();
        $userDay = $userDay->getDayById($dayId);
        return $userDay;
    }

    public function getParametersByUserId($userId)
    {
        $modelUserParameters = new Application_Model_Db_User_Parameters();
        $userParameters = $modelUserParameters->getParametersByUserId($userId);
        $userParameters = $this->_calculateAdditionalParameters($userParameters);
        return $userParameters;
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

    protected function _calculateAdditionalParameters(array $userParameters)
    {
        $userParameters['allowed_free_hours'] = 100500; // TODO: calc 'based on settings how much they have minus what they used.'
        return $userParameters;
    }

}