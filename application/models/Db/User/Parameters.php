<?php

class Application_Model_Db_User_Parameters extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'user_parameters';

    public function getParametersByUserId($userId, $year = '')
    {
        $this->setUserDefaultParameters($userId);
        if (empty($year)) {
            $weekYear = My_DateTime::getWeekYear();
            $year = $weekYear['year'];
        }
        $select = $this->_db->select()
            ->from(array('up' => self::TABLE_NAME))
            ->where('up.user_id = ?', $userId)
            ->where('up.year    = ?', $year);
        $result = $this->_db->fetchRow($select);
        return $result;
    }

    public function setUserDefaultParameters($userId)
    {
        $defaultTotalFreeTime = 0;
        $weekYear = My_DateTime::getWeekYear();
        $default = array(
            $userId,
            $defaultTotalFreeTime,
            $defaultTotalFreeTime,
            $weekYear['year'],
        );
        $query = '
            INSERT IGNORE INTO user_parameters SET
                user_id = ?,
                total_free_time = ?,
                used_free_time = ?,
                year = ?
        ';
        $this->_db->query($query, $default);
        return true;
    }

    public function setAdditionalFreeTime($userId, $seconds, $year)
    {
        return $this->_setParam($userId, $seconds, 'additional_free_time', $year);
    }

    public function setUsedFreeTime($userId, $seconds)
    {
        return $this->_setParam($userId, $seconds, 'used_free_time');
    }

    public function setTotalFreeTime($userId, $seconds)
    {
        return $this->_setParam($userId, $seconds, 'total_free_time');
    }

    private function _setParam($userId, $time, $fieldName, $year = '')
    {
        if (empty($year)) {
            $weekYear = My_DateTime::getWeekYear();
            $year = $weekYear['year'];
        }
        $userId = (int)$userId;
        $time = (int)$time;
        $year = (int)$year;
        //$fieldName = $this->_db->quote($fieldName);
        $query = '
            INSERT INTO ' . self::TABLE_NAME
            . ' SET ' . $fieldName . "=" . $time . ", year=" . $year . ", user_id=" . $userId
            . " ON DUPLICATE KEY UPDATE " . $fieldName . "=" . $time;
        return $this->_db->query($query);
    }
}
