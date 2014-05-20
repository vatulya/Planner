<?php

class Application_Model_Db_Intervals_Pause extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'intervals_pause';

    public function __construct()
    {
        parent::__construct();
    }

    public function getPauseIntervals($id = null)
    {
        $select = $this->_db->select()->from(array('ip' => self::TABLE_NAME), array('*'))
            ->order(array('ip.time_start ASC', 'ip.time_end ASC'));
        if (!empty($id)) {
            $select->where('ip.id = ?', $id);
            $result = $this->_db->fetchRow($select);
        } else {
            $result = $this->_db->fetchAll($select);
        }
        return $result;
    }

    public function savePauseInterval($timeStart,$timeEnd,$id = null)
    {
        $values = array(
            'time_start'       => $timeStart,
            'time_end'         => $timeEnd,
        );
        if (empty($id)) {
            $result = $this->_db->insert(self::TABLE_NAME, $values);
        } else {
            $result = $this->_db->update(self::TABLE_NAME, $values, array('id = ?' => $id));
        }
        return $result;
    }

    public function deletePauseInterval($id)
    {
        $this->_db->delete(self::TABLE_NAME, array('id = ?' => $id));
        return true;
    }
}