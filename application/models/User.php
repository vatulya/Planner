<?php

class Application_Model_User extends Application_Model_Abstract
{

    const USER_CHECK_IN = 'in';
    const USER_CHECK_OUT = 'out';

    const USER_DEFAULT_OWNER = 'Eigen';
    const STATUS_DAY_WHITE = '1';
    const STATUS_DAY_GREEN = '2';
    const STATUS_DAY_YELLOW = '3';

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

    public function getUserWeekPlanByGroup($userId, $groupId,  $year, $week)
    {
        $weekPlan = array();
        $weekDays = My_DateTime::getWeekDays();
        $dateWeekStart = new My_DateTime($year . 'W' . sprintf("%02d", $week));
        $currentDate = new My_DateTime();
        $currentDate = $currentDate->getTimestamp();
        $weekUserPlan = new Application_Model_Db_User_Planning();
        $groupPlanning = new Application_Model_Group();


        foreach ($weekDays as $numDay=>$nameDay) {
            $date = clone $dateWeekStart;
            $date->modify('+' . $numDay . 'day');
            $dateFormat = $date->format('Y-m-d');
           // echo $date->getTimestamp() . "----" . $currentDate . "----"  .$dateFormat. "<br>";
            if ($date->getTimestamp() >= $currentDate) {
                $weekGroupPlanning = $groupPlanning->getGroupPlanningByDate($groupId, $dateFormat);
                if (!empty($weekGroupPlanning[0])) {
                    $weekGroupPlanning = $weekGroupPlanning[0];
                }
                $dayPlan = array();
                $dayPlan['group_id'] = $groupId;
                $dayPlan['user_id'] = $userId;
                $dayPlan['date'] = $dateFormat;
                if(empty($weekGroupPlanning['time_start']) || empty($weekGroupPlanning['time_end'])) {
                    $dayPlan['status1'] = self::STATUS_DAY_WHITE;
                } else {
                    $dayPlan['status1'] = self::STATUS_DAY_GREEN;
                    $dayPlan['time_start'] = $weekGroupPlanning['time_start'];
                    $dayPlan['time_end'] = $weekGroupPlanning['time_end'];
                }
                $weekPlan[$nameDay] = $this->_setStatusByRules($dayPlan);
              //  echo "<pre> - $groupId  ----day " . $dateFormat ;
              //  var_dump($weekPlan[$nameDay]);

            }  else {
                $weekPlan[$nameDay] = $this->_setStatusByRules($weekUserPlan->getUserDayPlanByGroup($userId, $groupId, $dateFormat));
            }
        }
        return $weekPlan;
    }

    public function createNewDayUserPlanByGroup($userId, $groupId, $date)
    {
        $groupPlanning = new Application_Model_Group();
        $weekGroupPlanning = $groupPlanning->getGroupPlanningByDate($groupId, $date);
        if (!empty($weekGroupPlanning[0])) {
            $weekGroupPlanning = $weekGroupPlanning[0];
        }
        $dayPlan = array();
        $dayPlan['group_id'] = $groupId;
        $dayPlan['user_id'] = $userId;
        $dayPlan['date'] = $date;
        if(empty($weekGroupPlanning['time_start']) || empty($weekGroupPlanning['time_end'])) {
            $dayPlan['status1'] = self::STATUS_DAY_WHITE;
        } else {
            $dayPlan['status1'] = self::STATUS_DAY_GREEN;
            $dayPlan['time_start'] = $weekGroupPlanning['time_start'];
            $dayPlan['time_end'] = $weekGroupPlanning['time_end'];
        }
        $planning = new Application_Model_Db_User_Planning();
        $planning->createNewDayUserPlanByGroup($dayPlan);
    }

    private function _setStatusByRules($result)
    {
        if(!empty($result)) {
            $status = new Application_Model_Status();
            $userRequest = new Application_Model_Db_User_Requests();
            $userMissing = new Application_Model_Db_User_Missing();
            $approveUserDayRequest = $userRequest->getAllByUserId($result['user_id'], Application_Model_Db_User_Requests::USER_REQUEST_STATUS_APPROVED, $result['date']);
            $missingUserDayStatuses = $userMissing->getUserDayMissingPlanByDate($result['user_id'], $result['date']);
            if ($result['status1'] == self::STATUS_DAY_GREEN) {
                if (!empty($approveUserDayRequest)) {
                    $result['status1'] = $status->getDataById(self::STATUS_DAY_YELLOW);
                    $result = $this->_resetTimeAndSecondStatus($result);
                } else {
                    $result['status1'] = $status->getDataById(self::STATUS_DAY_GREEN);
                    if (!empty($result['time_start']) && !empty($result['time_end'])) {
                        $date = date_create($result['time_start']);
                        $result['time_start'] = date_format($date, 'H:i');
                        $date = date_create($result['time_end']);
                        $result['time_end'] = date_format($date, 'H:i');
                    }
                    if (!empty($missingUserDayStatuses)) {
                        $result['status2'] = $status->getDataById($missingUserDayStatuses['status']);
                    }  else {
                        $result['status_color2'] = "";
                    }
                }
            } else {
                $result['status1'] = $status->getDataById($result['status1']);
                $result = $this->_resetTimeAndSecondStatus($result);
            }
        } else {
            $result = $this->_setDefaultStatusForEmptyPlan();
        }
        return $result;
    }

    private function _setDefaultStatusForEmptyPlan() {
        $status = new Application_Model_Status();
        $result['status1'] = $status->getDataById(self::STATUS_DAY_WHITE);
        return $result;
    }

    private function _resetTimeAndSecondStatus($userDayPlan) {
        unset($userDayPlan['time_start']);
        unset($userDayPlan['time_end']);
        unset($userDayPlan['time_start2']);
        unset($userDayPlan['time_end2']);
        unset($userDayPlan['status2']);
        return $userDayPlan;
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
        $result = $userDay->getDayById($dayId);
        $userRequest = new Application_Model_Db_User_Requests();
        $approveUserDayRequest = $userRequest->getAllByUserId($result['user_id'], Application_Model_Db_User_Requests::USER_REQUEST_STATUS_APPROVED, $result['date']);
        if ($result['status1'] == self::STATUS_DAY_GREEN && !empty($approveUserDayRequest)) {
            $result['status1'] = self::STATUS_DAY_YELLOW;
            $result = $this->_resetTimeAndSecondStatus($result);
        }
        return $result;
    }

    public function getParametersByUserId($userId)
    {
        $modelUserParameters = new Application_Model_Db_User_Parameters();
        $userParameters = $modelUserParameters->getParametersByUserId($userId);
        $userParameters = $this->_calculateAdditionalParameters($userParameters);
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

    protected function _calculateAdditionalParameters(array $userParameters)
    {
        $userParameters['allowed_free_hours'] = 100500; // TODO: calc 'based on settings how much they have minus what they used.'
        return $userParameters;
    }

}
