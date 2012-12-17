<?php

class Application_Model_Db_User_Parameters extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'user_parameters';

    public function getParametersByUserId($userId)
    {
        $this->setUserDefaultParameters($userId);
        $select = $this->_db->select()
            ->from(array('up' => self::TABLE_NAME))
            ->where('up.user_id = ?', $userId);
        $result = $this->_db->fetchRow($select);
        return $result;
    }

    public function setUserDefaultParameters($userId)
    {
        $modelDbParameters = new Application_Model_Db_Parameters();
        $defaultTotalFreeTime = $modelDbParameters->getDefaultTotalFreeHours() * 3600;
        $default = array(
            ':user_id'            => $userId,
            ':total_free_time'    => $defaultTotalFreeTime,
            ':allowed_free_time'  => $defaultTotalFreeTime,
            ':regular_work_hours' => 40,
        );
        $default = array(
            $userId,
            $defaultTotalFreeTime,
            $defaultTotalFreeTime,
            40,
        );
        $query = '
            INSERT IGNORE INTO user_parameters SET
                user_id = ?,
                total_free_time = ?,
                allowed_free_time = ?,
                regular_work_hours = ?
        ';
        $this->_db->query($query, $default);
        return true;
    }

    public function setRegularWorkHours($userId, $hours)
    {
        $data = array(
            'regular_work_hours' => (int)$hours,
        );
        $where = array(
            'user_id = ?' => $userId,
        );
        $select = $check = $this->_db->select()
            ->from(self::TABLE_NAME, 'regular_work_hours')
            ->where('user_id = ?', $userId);
        $regularWorkHours = (int)$this->_db->fetchOne($select);
        if ($regularWorkHours == $hours) {
            $result = 1;
        } else {
            $result = $this->_db->update(self::TABLE_NAME, $data, $where);
        }
        return $result;
    }

    public function setAllowedFreeTime($userId, $seconds)
    {
        $data = array(
            'allowed_free_time' => $seconds,
        );
        $where = array(
            'user_id = ?' => $userId,
        );
        $select = $check = $this->_db->select()
            ->from(self::TABLE_NAME, 'allowed_free_time')
            ->where('user_id = ?', $userId);
        $allowedFreeTime = (int)$this->_db->fetchOne($select);
        if ($allowedFreeTime == $seconds) {
            $result = 1;
        } else {
            $result = $this->_db->update(self::TABLE_NAME, $data, $where);
        }
        return $result;
    }

    public function setTotalFreeTime($userId, $seconds)
    {
        $data = array(
            'total_free_time' => $seconds,
        );
        $where = array(
            'user_id = ?' => $userId,
        );
        $select = $check = $this->_db->select()
            ->from(self::TABLE_NAME, 'total_free_time')
            ->where('user_id = ?', $userId);
        $allowedFreeTime = (int)$this->_db->fetchOne($select);
        if ($allowedFreeTime == $seconds) {
            $result = 1;
        } else {
            $result = $this->_db->update(self::TABLE_NAME, $data, $where);
        }
        return $result;
    }

}
