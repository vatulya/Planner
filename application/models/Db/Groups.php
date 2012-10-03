<?php

class Application_Model_Db_Groups extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'groups';
    protected $_modelUser;

    public function __construct()
    {
        $this->_modelUser = new Application_Model_Db_Users();
        parent::__construct();
    }

    public function getAllGroups()
    {
        $select = $this->_db->select()
            ->from(array('g' => self::TABLE_NAME));
        $result = $this->_db->fetchAll($select);
        return $result;
    }

}
