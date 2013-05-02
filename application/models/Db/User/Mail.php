<?php
class Application_Model_Db_User_Mail extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'user_mail';

    public function __construct()
    {
        parent::__construct();
    }

    public function getListMail($type)
    {
        $select = $this->_db->select()
            ->from(array('up' => self::TABLE_NAME), array('*'))
            ->where('up.type = ?', $type)
        ;
        $result = $this->_db->fetchAll($select);
        return $result;
    }

    public function getMailArray($type)
    {
        $mailArray = array();
        $mails = $this->getListMail($type);
        if (is_array($mails)) {
            foreach ($mails as $mail) {
                $mailArray[] = $mail['email'];
            }
        }
        return $mailArray;
    }

    public function deleteEmailById($emailId)
    {
        return $this->_db->delete(self::TABLE_NAME, array('id = ?' => $emailId));
    }

    public function saveEmail($email, $type)
    {
        $result = $this->_db->insert(self::TABLE_NAME, array('email' => $email, 'type' => $type));
        return $result;
    }
}