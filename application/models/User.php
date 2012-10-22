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
        $weekDays = Planner_Model_Date::getWeekDays();
        $dateWeekStart = new DateTime($year . 'W' . sprintf("%02d", $week));
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

}