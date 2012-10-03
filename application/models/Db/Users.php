<?php

class Application_Model_Db_Users extends Application_Model_Db_Abstract
{

    const TABLE_NAME = 'users';

    public function getUserByEmail($email, DateTime $checkingDate = null)
    {
        $select = $this->_db->select()
            ->from(array('u' => self::TABLE_NAME))
            ->where('u.email = ?', $email);
        $select = $this->_addCheckinByDate($select, $checkingDate);
        $result = $this->_db->fetchRow($select);
        return $result;
    }

    public function getUserById($id, DateTime $checkingDate = null)
    {
        $select = $this->_db->select()
            ->from(array('u' => self::TABLE_NAME))
            ->where('u.id = ?', $id);
        $select = $this->_addCheckinByDate($select, $checkingDate);
        $result = $this->_db->fetchRow($select);
        return $result;
    }

    public function getAllUsers(DateTime $checkingDate = null)
    {
        $select = $this->_db->select()
            ->from(array('u' => self::TABLE_NAME))
            ->order(array('full_name ASC'));
        $select = $this->_addCheckinByDate($select, $checkingDate);
        $result = $this->_db->fetchAll($select);
        return $result;
    }

    protected function _addCheckinByDate($select, DateTime $checkingDate = null)
    {
        if ($checkingDate) {
            $table     = array('uc' => Application_Model_Db_User_Checks::TABLE_NAME);
            $condition = 'u.id = uc.user_id AND uc.check_date = "' . $checkingDate->format('Y-m-d') . '"';
            $fields    = array('uc.check_date', 'uc.check_in', 'uc.check_out');
            $select->joinLeft($table, $condition, $fields);
        }
        return $select;
    }

}