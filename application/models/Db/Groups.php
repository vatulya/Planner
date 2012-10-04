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

}
