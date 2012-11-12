<?php

class Application_Model_Db_Groups extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'groups';

    public function getAllGroups()
    {
        $select = $this->_db->select()
            ->from(array('g' => self::TABLE_NAME))
            ->order(array('g.group_name ASC'));
        $result = $this->_db->fetchAll($select);
        return $result;
    }

    public function getGroupById($groupId)
    {
        $select = $this->_db->select()
            ->from(array('g' => self::TABLE_NAME))
            ->where('g.id = ?', $groupId);
        $result = $this->_db->fetchRow($select);
        return $result;
    }

    public function getGroupsByUserId($userId)
    {
        $select = $this->_db->select()
            ->from(array('ug' => Application_Model_Db_User_Groups::TABLE_NAME), array())
            ->join(array('g' => self::TABLE_NAME), 'ug.group_id = g.id', array('*'))
            ->where('ug.user_id = ?', $userId);
        $result = $this->_db->fetchAll($select);
        return $result;
    }

    public function insertGroup(array $group)
    {
        $data = array(
            'group_name' => $group['group_name'],
            'color'      => $group['color'],
        );
        $result = $this->_db->insert(self::TABLE_NAME, $data);
        if ($result) {
            $groupId = $this->_db->query('SELECT LAST_INSERT_ID();')->fetchColumn();
            $settings = new Application_Model_Db_Group_Settings();
            $settings->setDefaultGroupSettings($groupId);
        }
        return $result;
    }

    public function updateGroup($groupId, array $group)
    {
        $data = array(
            'group_name' => $group['group_name'],
            'color'      => $group['color'],
        );
        $result = $this->_db->update(self::TABLE_NAME, $data, array('id = ?' => $groupId));
        return $result;
    }

    public function deleteGroup($groupId)
    {
        $this->_db->delete(self::TABLE_NAME, array('id = ?' => $groupId));
        $settings = new Application_Model_Db_Group_Settings();
        $settings->deleteGroupSettings($groupId);
        return true;
    }

}
