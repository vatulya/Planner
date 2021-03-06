<?php

class Application_Model_Db_User_Checks extends Application_Model_Db_Abstract
{

    const TABLE_NAME = 'user_checks';

    public function getUserLastCheck($userId)
    {
        $date = new My_DateTime();
        // Get last check IN
        $select = $this->_db->select()
            ->from(array('uc' => self::TABLE_NAME))
            ->where('uc.user_id = ?', $userId)
            ->where('uc.check_date = ?', $date->format('Y-m-d'))
            ->order(array('uc.check_date DESC', 'uc.check_in DESC'))
            ->limit(1);
        $lastCheck = $this->_db->fetchRow($select);
        if ( ! $lastCheck) {
            $lastCheck = array();
        }
        return $lastCheck;
    }

    public function checkUserIn($userId)
    {
        $lastCheckIn = $this->getUserLastCheck($userId);
        if (!empty($lastCheckIn['check_in']) && empty($lastCheckIn['check_out'])) {
            return true;
        }
        return false;
    }

    public function userCheckIn($userId)
    {
        $date = new My_DateTime();
        $fields = array(
            'user_id'    => $userId,
            'check_date' => $date->format('Y-m-d'),
            'check_in'   => $date->format('H:i:s'),
        );
        $this->_db->insert(self::TABLE_NAME, $fields);
        return true;
    }

    public function userCheckOut($userId)
    {
        $latest = $this->getUserLastCheck($userId);
        $date = new My_DateTime();
        $fields = array(
            'check_out' => $date->format('H:i:s'),
        );
        $this->_db->update(self::TABLE_NAME, $fields, array('id = ?' => $latest['id']));
        return true;
    }

    public function getUserCheckTimeByIdDate($userId, $date)
    {
        $select = $this->_db->select()
            ->from(array('uc' => self::TABLE_NAME), array('*'))
            ->where('uc.user_id = ?', $userId)
            ->where('uc.check_date = ?', $date);
        $result = $this->_db->fetchRow($select);
        return $result;
    }

    public function getById($id)
    {
        $select = $this->_db->select()
            ->from(array('uc' => self::TABLE_NAME))
            ->where('uc.id = ?', $id);
        $result = $this->_db->fetchRow($select);
        return $result;
    }

    public function update($id, DateTime $in, DateTime $out)
    {
        $fields = array(
            'check_in'  => $in->format('H:i:s'),
            'check_out' => $out->format('H:i:s'),
        );
        $this->_db->update(self::TABLE_NAME, $fields, array('id = ?' => $id));
        return true;
    }

    public function getUserWorkTime($userId, DateTime $date)
    {
        $select = $this->_db->select()
            ->from(array('uc' => self::TABLE_NAME))
            ->where('uc.user_id = ?', $userId)
            ->where('uc.check_date = ?', $date->format('Y-m-d'))
            ->where('uc.check_out IS NOT NULL');
        $result = $this->_db->fetchAll($select);
        return $result;
    }

}