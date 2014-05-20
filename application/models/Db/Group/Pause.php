<?php

class Application_Model_Db_GROUP_Pause extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'group_pause';

    public function __construct()
    {
        parent::__construct();
    }

    public function getPauseIntervals($groupId, $userId, $weekType = null, $day = null)
    {
        $select = $this->_db->select()
            ->from(array('gp' => self::TABLE_NAME))
            ->where('gp.group_id = ?', $groupId)
            ->where('gp.user_id = ?', $userId)
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

    public function savePauseInterval($data)
    {
        $values = array(
            'user_id'         => $data['user_id'],
            'group_id'        => $data['group_id'],
            'pause_id'        => $data['pause_id'],
            'week_type'       => $data['week_type'],
            'day_number'      => $data['day_number'],
        );
        if (empty($data['id'])) {
            $result = $this->_db->insert(self::TABLE_NAME, $values);
        } else {
            $result = $this->_db->update(self::TABLE_NAME, $values, array('id = ?' => $data['id']));
        }
        return $result;
    }
}