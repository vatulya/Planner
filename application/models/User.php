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

    public function getAllUsersFromHistory($groupId, $year, $week)
    {
        $historyDateInterval = My_DateTime::getWeekHistoryDateInterval($year, $week);
        $modelPlanning = new Application_Model_Db_User_Planning();
        $usersNormalized = array();
        $users = $modelPlanning->getUsersByDateInterval($groupId, $historyDateInterval['start'], $historyDateInterval['end']);
        foreach ($users as $user) {
            $user = $this->_filterHiddenFields($user);
            $usersNormalized[$user['id']] = $user;
            $usersNormalized[$user['id']]['user_id'] = $user['id'];
            //$usersNormalized[$user['id']] = $this->getUserById($user['id']);
        }
        $currentDate = new My_DateTime();
        $currentDate = $currentDate->getTimestamp();
        $endWeek = My_DateTime::getTimestampNextWeekByYearWeek($year, $week);
        if ($endWeek > $currentDate) {
            $modelUser = new Application_Model_User();
            $users = $modelUser->getAllUsersByGroup($groupId);
            foreach ($users as $user) {
                $usersNormalized[$user['id']] = $user;
            }
        }
        return $usersNormalized;
    }

    public function getAllUsersForYear($year)
    {
        $modelPlanning = new Application_Model_Db_User_Planning();
        $yearDateInterval = My_DateTime::getYearDateInterval($year);
        $users = $modelPlanning->getUsersByDateInterval(false, $yearDateInterval['start'], $yearDateInterval['end']);
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

    public function getWorkHoursByDate($userId, $date)
    {
        $modelGroup = new Application_Model_Group();
        $modelDbPlanning = new Application_Model_Db_User_Planning();

        $groups = $modelGroup->getGroupsByUserId($userId);
        $workHours = 0;
        foreach ($groups as $group) {
            $dayPlan = $modelDbPlanning->getUserDayPlanByGroup($userId, $group['id'], $date);
            if ( ! empty($dayPlan) && $dayPlan['status1'] === Application_Model_Planning::STATUS_DAY_GREEN) {
                // TODO: move this code to separate method
                $start = new DateTime($dayPlan['time_start']);
                $end   = new DateTime($dayPlan['time_end']);
                $diff = $end->diff($start);
                $workHours += $diff->format('%h'); // 8
                $decimalMinutes = $diff->format('%i');
                $decimalMinutes = $decimalMinutes / 60;
                $workHours += $decimalMinutes; // 8.11111
            }
        }
        $workHours = sprintf('%01.2f', $workHours); // 8.11
        return $workHours;
    }

















    public function getAllowedFreeTime($userId)
    {
        $parameters = $this->getParametersByUserId($userId);
        return $parameters['allowed_free_time'];
    }

    /**
     * DEPRECATED
     * This method can be used ONLY when Module_Day will correct work with History dates
     * @param $userId
     * @return mixed
     */
    public function calculateAllowedFreeTime($userId)
    {
        $modelRequest      = new Application_Model_Request();
        $approvedRequests  = $modelRequest->getRequestsByUserId($userId, Application_Model_Db_User_Requests::USER_REQUEST_STATUS_APPROVED);
        $userTotalFreeTime = $this->getTotalFreeTime($userId);
        $approvedTime      = 0; // in secs
        foreach ($approvedRequests as $request) {
            $day = Application_Model_Day::factory($request['request_date'], $userId);
            $approvedTime += $day->getWorkHours();
        }
        $userAllowedFreeTime = $userTotalFreeTime - $approvedTime;
        $modelDbUserParameters = new Application_Model_Db_User_Parameters();
        $modelDbUserParameters->setAllowedFreeTime($userId, $userAllowedFreeTime);
        return $userAllowedFreeTime;
    }

    public function getTotalFreeTime($userId)
    {
        $parameters = $this->getParametersByUserId($userId);
        return $parameters['total_free_time'];
    }

    public function saveRegularWorkHours($userId, $hours)
    {
        $result = false;
        $modelParameters = new Application_Model_Parameter();
        if ($hours > 0 && $hours <= $modelParameters->getDefaultWorkHours() && $userId > 0) {
            $modelUserParameters = new Application_Model_Db_User_Parameters();
            $userParametersOld   = $modelUserParameters->getParametersByUserId($userId);

            $result = $modelUserParameters->setRegularWorkHours($userId, $hours);
            if ($result) {
                $userParametersNew = $modelUserParameters->getParametersByUserId($userId);
                $deltaPercentage = $userParametersNew['regular_work_hours'] / $userParametersOld['regular_work_hours']; // 20 / 40 = 0.5
                $modelParameters->recalculateAllUsersTotalFreeTime($deltaPercentage, $userId);
            }
        }
        return $result;
    }

}
