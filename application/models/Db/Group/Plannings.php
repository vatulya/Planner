<?php

class Application_Model_Db_Group_Plannings extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'group_plannings';

    const WEEK_TYPE_ODD = 'odd';
    const WEEK_TYPE_EVEN = 'even';

    public function getGroupPlanning($groupId, $userId, $weekType = null, $day = null)
    {
        $select = $this->_db->select()
            ->from(array('gp' => self::TABLE_NAME))
            ->where('gp.group_id = ?', $groupId)
            ->where('gp.user_id = ?', $userId)
            ->where('gp.enabled = ?', 1)
            ->order(array('gp.day_number ASC'));
        if ($weekType) {
            $select->where('gp.week_type = ?', $weekType);
        } else {
            $select->order(array('gp.week_type DESC', 'gp.day_number ASC'));
        }
        if ($day !== null) {
            $select->where('gp.day_number = ?', (int)$day);
        }
        $result = $this->_db->fetchAll($select);
        return $result;
    }

    public function saveGroupPlanning($data)
    {
        $this->_db->delete(self::TABLE_NAME, array(
            'group_id = ?' => $data['group_id'],
            'user_id = ?' => $data['user_id'],
            'week_type = ?' => $data['week_type'],
            'day_number = ?' => $data['day_number'],)
        );
        $result = $this->_db->insert(self::TABLE_NAME, $data);
        return $result;
    }

    public function getUserPlanning($userId, $weekType, $day)
    {
        $select = $this->_db->select()
            ->from(array('gp' => self::TABLE_NAME))
            ->where('gp.user_id = ?', $userId)
            ->where('gp.enabled = ?', 1)
            ->where('gp.week_type = ?', $weekType)
            ->where('gp.day_number = ?', (int)$day);
        $result = $this->_db->fetchAll($select);
        return $result;
    }

    public function deleteGroupPlanning($id)
    {
        $this->_db->delete(self::TABLE_NAME, array('id = ?' => $id));
    }

}
