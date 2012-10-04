<?php

class Application_Model_Db_User_Planning extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'user_planning';

    public function getUserDayPlanByGroup($user, $groupId, $date)
    {
        $select = $this->_db->select()
            ->from(array('ug' => self::TABLE_NAME), 'ug.group_id')
            ->where('ug.user_id = ?', $userId)
            ->where('ug.is_admin = ?', 1);
        $result = $this->_db->fetchCol($select);
        return $result;
    }
}
