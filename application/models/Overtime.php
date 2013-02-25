<?php

class Application_Model_Overtime extends Application_Model_Abstract
{

    protected $_modelOvertime;

    public function __construct()
    {
        $this->_modelOvertime = new Application_Model_Db_User_Overtime();
    }

    public function saveUserOvertimeDay($formData)
    {
        $overtimeData = array(
            'user_id'    => $formData['user_id'],
            'group_id'   => $formData['group_id'],
            'date'       => $formData['date'],
            'time_start' => $formData['time_start'],
            'time_end'   => $formData['time_end']
        );
        $result = true;
        if (!empty($formData['user_id']) && !empty($formData['date']) && !empty($formData['group_id'])) {
            $this->_modelOvertime->deleteOvertime($overtimeData);
        } else {
            return false;
        }
        if ( !empty($formData['time_start']) && !empty($formData['time_end'])) {
            $result = $this->_modelOvertime->saveUserDayOvertimeByDate($overtimeData);
        }
        return $result;
    }

    public function getUserDayOvertimeByDate($userId, $groupId, $date)
    {
        $overTime = $this->_modelOvertime->getUserDayOvertimeByDate($userId, $groupId, $date);
        if(!empty($overTime['time_start']) && !empty($overTime['time_end'])) {
            $workSeconds = Application_Model_Day::getWorkHoursByMarkers(
                $overTime['time_start'],
                $overTime['time_end'],
                "00:00:00", "00:00:00"
            );
            $overTime['total_time'] =  $workSeconds;
        }
        return $overTime;
    }
}