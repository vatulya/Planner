<?php

class Application_Model_Db_Group_Pause extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'group_pause';

    public function __construct()
    {
        parent::__construct();
    }

    public function getPauseIntervals($dayId)
    {
        $select = $this->_db->select()
            ->from(array('gp' => self::TABLE_NAME))
            ->where('gp.planning_id = ?', $dayId);
        $result = $this->_db->fetchAll($select);
        return $result;
    }

    public function savePauseInterval($data)
    {
        $result = $this->_db->insert(self::TABLE_NAME, $data);
        return $result;
    }

    public function deletePauseInterval($data)
    {
        $this->_db->delete(self::TABLE_NAME, array(
            'pause_id = ?' => $data['pause_id'],
            'planning_id = ?' => $data['planning_id']
        ));
        return true;
    }
}