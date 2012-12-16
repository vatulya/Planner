<?php
class Application_Model_Day
{

    protected $_user;

    protected $_date;

    static protected $_holidays;

    static protected $_groupsPlanning;

    /**
     * @var array
     * format OLD FULL VERSION:
     * array(
     *     'user' => $this->_user,
     *     'date' => $this->_date,
     *     'groups' => array(
     *         %group_id% => $group,
     *     ),
     *     'planning' => array(
     *         'groups' => array(
     *             %group_id% => $groupPlanning,
     *         ),
     *         'user' => array(
     *             %group_id% => $useGroupPlanning,
     *         ),
     *         'result' => $planning,
     *     ),
     *     'request' => $request,
     *     'holidays' => array(
     *         'groups' => array(
     *             %group_id% => $holiday,
     *         ),
     *         'global' => $holiday,
     *     )
     * );
     *
     * format:
     * array(
     *     'work_hours' => 8
     * )
     */
    protected $_data = null;

    protected $_workHours = null;

    /**
     * @param string $date
     * @param string|int|array $user Here you can set user ID or user data array
     * @return Application_Model_Day
     */
    static public function factory($date, $user)
    {
        return new self($date, $user);
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
        if ( ! isset($this->_workHours)) { $this->_fillWorkHours(); }
        return ($this->_workHours > 0);
    }

    public function getWorkHours()
    {
        if ( ! isset($this->_workHours)) { $this->_fillWorkHours(); }
        return $this->_workHours;
    }

    static function TimeToDecimal($seconds)
    {
        // OLD logic :) just for fun
//        $minutes = floor($seconds / 60);       // 8000 / 60  = 133 minutes
//        $hours   = floor($minutes / 60);       // 133  / 60  = 2 hours
//        $seconds = (int)($seconds % $minutes); // 8000 % 133 = 20 seconds
//        $minutes = (int)($minutes % 60);       // 133  % 60  = 13 minutes
//        $decimalMinutes = $minutes / 60;       // 13   / 60  = 0.22
//        $decimalHours = $hours + $decimalMinutes; // 2 + 0.22 = 2.22 (8000 seconds = 2.22 hours
        // NEW logic
        $hours = $seconds / 3600; // 2.222222
        $hours = sprintf('%01.2f', $hours); // 2.22
        return $hours;
    }

    protected function _fillWorkHours()
    {
        if ($this->isHoliday()) {
            $this->_workHours = 0;
            return true;
        }

        $groupsPlanning = self::getGroupsPlanning($this->_date);
        $groups         = $this->_modelGroup->getGroupsByUserId($this->_user['id']);
        foreach ($groups as $group) {
            $groupId = $group['id'];
            if (isset($groupsPlanning[$groupId])) {
                $workStart  = $groupsPlanning[$groupId]['time_start'];
                $workEnd    = $groupsPlanning[$groupId]['time_end'];
//                $pauseStart = $groupsPlanning[$groupId]['time_end'];
//                $pauseEnd = $groupsPlanning[$groupId]['time_end'];
                $pauseStart = null;
                $pauseEnd   = null;
                $workSeconds = self::getWorkHoursByMarkers($workStart, $workEnd, $pauseStart, $pauseEnd);
                $this->_workHours += $workSeconds;
            }
        }

        return true;
    }

    static public function getWorkHoursByMarkers($workStart, $workEnd, $pauseStart, $pauseEnd)
    {
        $workStart  = new My_DateTime($workStart);
        $workEnd    = new My_DateTime($workEnd);
        $pauseStart = new My_DateTime($pauseStart);
        $pauseEnd   = new My_DateTime($pauseEnd);
        $work  = My_DateTime::diffInSeconds($workStart, $workEnd);
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

    /**
     * DEPRECATED
     */
    static public function refreshHolidays()
    {
        return self::_fillHolidays();
    }

    static public function getGroupsPlanning(My_DateTime $date)
    {
        if (static::$_groupsPlanning === null) {
            self::_fillGroupsPlanning();
        }
        $weekType = $date->format('W') % 2 ? Application_Model_Db_Group_Plannings::WEEK_TYPE_ODD : Application_Model_Db_Group_Plannings::WEEK_TYPE_EVEN;
        $dayNumber = $date->format('N');
        if (isset(static::$_groupsPlanning[$weekType][$dayNumber])) {
            return static::$_groupsPlanning[$weekType][$dayNumber];
        }
        return array();
    }

    static protected function _fillGroupsPlanning()
    {
        $modelGroup = new Application_Model_Group();
        $groups = $modelGroup->getAllGroups();
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
            $groupPlannings = $modelGroup->getGroupPlanning($group['id']);
            foreach ($groupPlannings as $groupPlanning) {
                $week  = $groupPlanning['week_type'];
                $day   = $groupPlanning['day_number'];
                $group = $groupPlanning['group_id'];
                if (isset($plannings[$week][$day])) {
                    $plannings[$week][$day][$group] = $groupPlanning;
                }
            }
        }
        static::$_groupsPlanning = $plannings;
        return true;
    }

    /**
     * DEPRECATED
     */
    static public function refreshGroupsPlanning()
    {
        return self::_fillGroupsPlanning();
    }

}