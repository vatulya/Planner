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

    public function saveUserGroups($userId, array $groups)
    {
        $this->_db->delete(self::TABLE_NAME, array('user_id = ?' => $userId));
        $partResult = true;
        $result = false;
        foreach ($groups as $group) {
            if ($group['group'] > 0) {
                $admin = (int)$group['admin'];
                $data = array(
                    'user_id' => $userId,
                    'group_id' => $group['group'],
                    'is_admin' => $admin,
                );
                $partResult = $this->_db->insert(self::TABLE_NAME, $data);
                if ($partResult) {
                    $result = true;
                }
            }
        }
        return $result;
    }
}
