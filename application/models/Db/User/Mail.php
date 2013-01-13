<?php
class Application_Model_Db_User_Mail extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'user_mail';

    public function __construct()
    {
        parent::__construct();
    }

    public function getListMail()
    {
        $select = $this->_db->select()
            ->from(array('up' => self::TABLE_NAME), array('*'));
        $result = $this->_db->fetchAll($select);
        return $result;
    }

    public function deleteEmailById($emailId)
    {
        return $this->_db->delete(self::TABLE_NAME, array('id = ?' => $emailId));
    }

    public function saveEmail($email)
    {
        $result = $this->_db->insert(self::TABLE_NAME, array('email' => $email));
        return $result;
    }
}