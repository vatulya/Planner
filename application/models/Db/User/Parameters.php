<?php

class Application_Model_Db_User_Parameters extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'user_parameters';

    public function getUserParameters($userId)
    {
        $select = $this->_db->select()
            ->from(array('up' => self::TABLE_NAME))
            ->where('up.user_id = ?', $userId);
        $result = $this->_db->fetchRow($select);

        if (empty($result)) {
            // no parameters? need check
            $selectCheck = $this->_db->select()
                ->from(array('u' => Application_Model_Db_Users::TABLE_NAME))
                ->where('u.id = ?', $userId);
            $check = $this->_db->fetchRow($selectCheck);
            if ($check) {
                // Yes. User exists, but without any parameters. This is wrong. Need create default settings
                $this->setDefaultUserParameters($userId);
                $result = $this->_db->fetchRow($select);
            }
        }

        return $result;
    }

    public function setDefaultUserParameters($userId)
    {
        $default = array(
            'user_id' => $userId,
            'used_free_hours' => 'NULL', // use default field value
        );
        $result = $this->_db->insert(self::TABLE_NAME, $default);
        return $result;
    }
}
