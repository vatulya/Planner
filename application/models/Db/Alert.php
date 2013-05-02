<?php

class Application_Model_Db_Alert extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'alert';

    public function getAlertsType()
    {
        $select = $this->_db->select()
            ->from(array('a' => self::TABLE_NAME));
        $result = $this->_db->fetchAll($select);
        return $result;
    }
}