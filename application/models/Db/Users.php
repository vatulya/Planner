<?php

class Application_Model_Db_Users extends Application_Model_Db_Abstract
{

    const TABLE_NAME = 'users';

    public function getUserByEmail($email)
    {
        $select = $this->_db->select()
            ->from(array('u' => self::TABLE_NAME))
            ->where('u.email = ?', $email);
        $result = $this->_db->fetchRow($select);
        return $result;
    }

    public function getUserById($id)
    {
        $select = $this->_db->select()
            ->from(array('u' => self::TABLE_NAME))
            ->where('u.id = ?', $id);
        $result = $this->_db->fetchRow($select);
        return $result;
    }

    public function getAllUsers()
    {
        $select = $this->_db->select()
            ->from(array('u' => self::TABLE_NAME))
            ->order(array('full_name ASC'));
        $result = $this->_db->fetchAll($select);
        return $result;
    }

}