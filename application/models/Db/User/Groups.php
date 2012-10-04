<?php

class Application_Model_Db_User_Groups extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'user_groups';

    public function getUserGroupsAdmin($userId)
    {
        $select = $this->_db->select()
            ->from(array('ug' => self::TABLE_NAME), 'ug.group_id')
            ->where('ug.user_id = ?', $userId)
            ->where('ug.is_admin = ?', 1);
        $result = $this->_db->fetchCol($select);
        return $result;
    }
}
