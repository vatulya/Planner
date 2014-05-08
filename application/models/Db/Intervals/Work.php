<?php

class Application_Model_Db_Intervals_Work extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'intervals_work';

    public function __construct()
    {
        parent::__construct();
    }

    public function getWorkIntervals($id = null)
    {
        $select = $this->_db->select()->from(array('iw' => self::TABLE_NAME), array('*'));
        if (!empty($id)) {
            $select->where('iw.id = ?', $id);
            $result = $this->_db->fetchRow($select);
        } else {
            $result = $this->_db->fetchAll($select);
        }
        return $result;
    }

    public function setWorkInterval($timeStart,$timeEnd,$colorHex,$description,$id = null)
    {
        $values = array(
            'time_start'  => $timeStart,
            'time_end'    => $timeEnd,
            'color_hex'   => $colorHex,
            'description' => $description,
        );
        if (empty($id)) {
            $result = $this->_db->insert(self::TABLE_NAME, $values);
        } else {
            $result = $this->_db->update(self::TABLE_NAME, $values, array('id = ?' => $id));
        }
        return $result;
    }

    public function deleteWorkInterval($id)
    {
        $this->_db->delete(self::TABLE_NAME, array('id = ?' => $id));
        return true;
    }
}