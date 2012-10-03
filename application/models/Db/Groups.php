<?php

class Application_Model_Db_Groups extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'groups';

    public function getAllGroups()
    {
        $select = $this->_db->select()
            ->from(array('g' => self::TABLE_NAME));
        $result = $this->_db->fetchRow($select);
        return $result;
    }
}
