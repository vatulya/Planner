<?php

class Application_Model_Db_Users extends Application_Model_Db_Abstract
{

    const TABLE_NAME = 'users';

    protected $_allowedSaveFields = array(
        'email', 'full_name', 'address', 'phone',
        'emergency_phone', 'emergency_full_name', 'birthday', 'owner',
    );

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
            ->order(array('u.full_name ASC'));
        $select = $this->_addCheckinByDate($select, $checkingDate);
        $result = $this->_db->fetchAll($select);
        return $result;
    }

    public function getAllUsersByGroup($groupId, DateTime $checkingDate = null)
    {
        $select = $this->_db->select(array())
            ->from(array('ug' => Application_Model_Db_User_Groups::TABLE_NAME))
            ->join(array('u' => self::TABLE_NAME), 'ug.user_id = u.id', array('*'))
            ->where('ug.group_id = ?', $groupId)
            ->order(array('u.full_name ASC'));
        $select = $this->_addCheckinByDate($select, $checkingDate);
        $result = $this->_db->fetchAll($select);
        return $result;
    }

    public function saveRole($userId, $role)
    {
        $result = false;
        $checkUser = $this->getUserById($userId);
        if ($checkUser['role'] == $role) {
            $result = true;
        } else {
            $data = array(
                'role' => (int)$role,
            );
            $result = $this->_db->update(self::TABLE_NAME, $data, array('id = ?' => $userId));
        }
        return $result;
    }

    public function saveField($userId, $field, $value)
    {
        $checkUser = $this->getUserById($userId);
        $result = false;
        if (in_array($field, $this->_allowedSaveFields)) {
            $data = array(
                $field => $value,
            );
            if ($checkUser[$field] == $value) {
                $result = true;
            } else {
                $result = $this->_db->update(self::TABLE_NAME, $data, array('id = ?' => $userId));
            }
        }
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