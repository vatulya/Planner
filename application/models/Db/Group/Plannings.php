<?php

class Application_Model_Db_Group_Plannings extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'group_plannings';

    const WEEK_TYPE_ODD = 'odd';
    const WEEK_TYPE_EVEN = 'even';

    public function getGroupPlanning($groupId, $weekType = null, $day = null)
    {
        $select = $this->_db->select()
            ->from(array('gp' => self::TABLE_NAME), array('*', 'total_time' => 'TIMEDIFF(time_end,time_start)'))
            ->where('gp.group_id = ?', $groupId)
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

    public function saveGroupPlanning($groupId, array $planning)
    {
        $result = false;
        $this->_db->delete(self::TABLE_NAME, array('group_id = ?' => $groupId));
        if (empty($planning)) {
            $result = true;
        }
        foreach ($planning as $key => $day) {
            $data = array(
                'group_id'   => $groupId,
                'week_type'  => $day['week_type'],
                'day_number' => $day['day_number'],
                'time_start' => $day['time_start'],
                'time_end'   => $day['time_end'],
            );
            $result = $this->_db->insert(self::TABLE_NAME, $data);
            if ( ! $result) {
                break;
            }
        }
        return $result;
    }

}
