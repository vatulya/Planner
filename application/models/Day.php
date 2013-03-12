<?php
class Application_Model_Day
{

    protected $_user;

    protected $_date;

    static protected $_holidays;

    static protected $_groupsPlanning = array();

    /**
     * For fill this value please use method _fillWorkTime()
     * @var int Work time in seconds
     */
    protected $_workHours = null;

    /**
     * For fill this value please use method _fillWorkedTime()
     * @var int Real Work time in seconds
     */
    protected $_workedHours = null;

    /**
     * For fill this value please use method _fillWorkTime()
     * @var array Planned work time start and end
     */
    protected $_workPlanning = null;

    /**
     * @param string $date
     * @param string|int|array $user Here you can set user ID or user data array
     * @return Application_Model_Day|Application_Model_History_Day
     */
    static public function factory($date, $user)
    {
        $day = new self($date, $user);
        return $day;
    }

    /**
     * @param string $date
     * @param string|int|array $user Here you can set user ID or user data array
     */
    protected function __construct($date, $user)
    {
        if ($date instanceof My_DateTime) {
            $this->_date = $date;
        } else {
            $this->_date = new My_DateTime($date);
        }

        $this->_modelUser  = new Application_Model_User();
        $this->_modelGroup = new Application_Model_Group();

        if (is_array($user)) {
            $this->_user = $user;
        } else {
            $this->_user = $this->_modelUser->getUserById($user);
        }
    }

    public function isWorkday()
    {
        if ( ! isset($this->_workHours)) { $this->_fillWorkTime(); }
        return ($this->_workHours > 0);
    }

    public function getWorkTime()
    {
        if ( ! isset($this->_workHours)) { $this->_fillWorkTime(); }
        return (int)$this->_workHours;
    }

    public function getWorkedTime()
    {
        if ( ! isset($this->_workedHours)) { $this->_fillWorkedTime(); }
        return $this->_workedHours;
    }

    public function getWorkPlanning()
    {
        if ( ! isset($this->_workPlanning)) { $this->_fillWorkTime(); }
        return $this->_workPlanning;
    }

    protected function _fillWorkTime()
    {
        if ($this->isHoliday()) {
            $this->_workHours = 0;
            $this->_workPlanning = array();
            return true;
        }

        $workPlanning = array();
        $groupsPlanning = $this->getGroupsPlanning();
        foreach ($groupsPlanning as $groupId => $group) {
            $workStart  = My_DateTime::factory($groupsPlanning[$groupId]['time_start']);
            $workEnd    = My_DateTime::factory($groupsPlanning[$groupId]['time_end']);
            $pauseStart = My_DateTime::factory($groupsPlanning[$groupId]['pause_start']);
            $pauseEnd   = My_DateTime::factory($groupsPlanning[$groupId]['pause_end']);
//            $pauseStart = null;
//            $pauseEnd   = null;

            $workSeconds = Application_Model_Day::getWorkHoursByMarkers($workStart, $workEnd, $pauseStart, $pauseEnd);

            $this->_workHours += $workSeconds;

            if (empty($workPlanning['time_start'])) {
                $workPlanning['time_start'] = $workStart;
            } elseif ( ! empty($workPlanning['time_start']) && My_DateTime::compare($workPlanning['time_start'], $workStart) === 1) {
                $workPlanning['time_start'] = $workStart;
            }
            if (empty($workPlanning['time_end'])) {
                $workPlanning['time_end'] = $workEnd;
            } elseif ( ! empty($workPlanning['time_end']) && My_DateTime::compare($workPlanning['time_end'], $workEnd) === 1) {
                $workPlanning['time_end'] = $workEnd;
            }
        }

        if ( ! empty($workPlanning)) {
            $workPlanning['time_start'] = $workPlanning['time_start']->format('H:i:s');
            $workPlanning['time_end']   = $workPlanning['time_end']->format('H:i:s');
        }

        $this->_workPlanning = $workPlanning;
        return true;
    }

    protected function _fillWorkedTime()
    {
        $modelChecks = new Application_Model_Db_User_Checks();
        $checks = $modelChecks->getUserWorkTime($this->_user['id'], $this->_date);
        $work = 0;
        foreach ($checks as $check) {
            $work += My_DateTime::diffInSeconds($check['check_in'], $check['check_out']);
        }
        $this->_workedHours = $work;
        return true;
    }

    static public function getWorkHoursByMarkers($workStart, $workEnd, $pauseStart = false, $pauseEnd = false)
    {
        $work  = My_DateTime::diffInSeconds($workStart, $workEnd);
        if ($pauseStart === false || $pauseStart === false) {
            return $work;
        }
        $pause = My_DateTime::diffInSeconds($pauseStart, $pauseEnd);
        $workSeconds = $work - $pause;
        return $workSeconds;
    }

    public function isHoliday()
    {
        $today = $this->_date->format('Y-m-d');
        $holidays = self::getHolidays();
        if (isset($holidays[$today])) {
            return true;
        }
        return false;
    }

    static public function getHolidays()
    {
        if (static::$_holidays === null) {
            self::_fillHolidays();
        }
        return static::$_holidays;
    }

    static protected function _fillHolidays()
    {
        $modelGroup = new Application_Model_Group();
        $holidays = $modelGroup->getGeneralHolidays();
        $holidaysData = array();
        foreach ($holidays as $holiday) {
            $holidaysData[$holiday['holiday_date']] = $holiday;
        }
        static::$_holidays = $holidaysData;
        return true;
    }

    public function getGroupsPlanning()
    {
        $userId = $this->_user['id'];
        if ( ! isset(static::$_groupsPlanning[$userId]) || static::$_groupsPlanning[$userId] === null) {
            static::_fillGroupsPlanning($userId);
        }
        $weekType = $this->_date->format('W') % 2 ? Application_Model_Db_Group_Plannings::WEEK_TYPE_ODD : Application_Model_Db_Group_Plannings::WEEK_TYPE_EVEN;
        $dayNumber = $this->_date->format('N');
        if (isset(static::$_groupsPlanning[$userId][$weekType][$dayNumber])) {
            return static::$_groupsPlanning[$userId][$weekType][$dayNumber];
        }
        return array();
    }

    protected function _fillGroupsPlanning($userId)
    {
        $modelGroup = new Application_Model_Group();
        $groups = $modelGroup->getGroupsByUserId($userId);
        $days = array(
            1 => array(), // monday
            2 => array(), // tuesday
            3 => array(), // wednesday
            4 => array(), // thursday
            5 => array(), // friday
            6 => array(), // saturday
            7 => array(), // sunday
        );
        $plannings = array(
            Application_Model_Db_Group_Plannings::WEEK_TYPE_ODD  => $days,
            Application_Model_Db_Group_Plannings::WEEK_TYPE_EVEN => $days,
        );
        foreach ($groups as $group) {
            $groupPlannings = $modelGroup->getGroupPlanning($group['id'], $userId);
            foreach ($groupPlannings as $groupPlanning) {
                $week  = $groupPlanning['week_type'];
                $day   = $groupPlanning['day_number'];
                $group = $groupPlanning['group_id'];
                if (isset($plannings[$week][$day])) {
                    $plannings[$week][$day][$group] = $groupPlanning;
                }
            }
        }
        static::$_groupsPlanning[$userId] = $plannings;
        return true;
    }

}