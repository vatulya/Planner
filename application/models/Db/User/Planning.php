<?php

class Application_Model_Db_User_Planning extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'user_planning';

    public function __construct()
    {
        parent::__construct();
    }

    public function getUserDayPlanByGroup($userId, $groupId, $date)
    {
        $select = $this->_db->select()
            ->from(array('up' => self::TABLE_NAME), array('*'))
            ->where('up.user_id = ?', $userId)
            ->where('up.group_id = ?', $groupId)
            ->where('up.date = ?', $date);
            //->where('up.date <= ADDDATE( ?, INTERVAL 6 DAY)', $date)
        $result = $this->_db->fetchRow($select);
        return $result;
    }

    public function createNewDayUserPlanByGroup($dayPlan)
    {
        try {
            $this->_db->insert(self::TABLE_NAME,$dayPlan);
        } catch (Exception $e ) {
            //for this day plan already exist
            echo "exist - use \n" ;
        }
    }

    public function getTotalWorkTimeByGroup($userId, $groupId, $date, $weekLenght = 7)
    {
        $select = $this->_db->select()
            ->from(
                array('up' => self::TABLE_NAME),
                array("SUM( TIME_FORMAT(TIMEDIFF(up.time_end, up.time_start), '%H') )")
            )
            ->where('up.user_id = ?', $userId)
            ->where('up.group_id = ?', $groupId)
            ->where('up.status1 = ?', Application_Model_Planning::STATUS_DAY_GREEN)
            ->where('up.date >= ?', $date)
            ->where('up.date <= ADDDATE( ?, INTERVAL ' . $weekLenght . ' DAY)', $date);
        $result = $this->_db->fetchOne($select);
        if (empty($result)) {
            $result = 0;
        }
        return $result;
    }

    public function getDayById($dayId)
    {
        $select = $this->_db->select()
            ->from(array('up' => self::TABLE_NAME))
            ->where('up.id = ?', $dayId);
        $result = $this->_db->fetchRow($select);
        return $result;
    }

    public function saveStatus($fields)
    {
        $id = $fields['id'];
        unset($fields['id']);
        unset($fields['use_status2']);
        unset($fields['color']);
        $this->_db->update(self::TABLE_NAME, $fields, array('id = ?' => $id));
        return true;
    }

    public function saveWorkTime($formData)
    {
        //TODO realize logic for update
        $this->_db->update(self::TABLE_NAME, $fields, array('id = ?' => $id));
    }

    public function getUsersByDateInterval($groupId, $dateStart, $dateEnd)
    {
        $select = $this->_db->select()
            ->from(array('up' => self::TABLE_NAME), array('id' => 'up.user_id'))
            ->joinInner(array('u' => Application_Model_Db_Users::TABLE_NAME), 'up.user_id = u.id')
            ->where('up.date >= ?', $dateStart)
            ->where('up.date <= ?', $dateEnd)
            ->group('up.user_id')
            ->order(array('u.full_name ASC'));
        if ($groupId !== false) {
            $select->where('up.group_id = ?', $groupId);
        }
        $result = $this->_db->fetchAll($select);
        return $result;
    }

    public function getGroupsByDateInterval($dateStart, $dateEnd)
    {
        $select = $this->_db->select()
            ->from(array('up' => self::TABLE_NAME), array('id' => 'up.group_id'))
            ->joinInner(array('g' => Application_Model_Db_Groups::TABLE_NAME), 'up.group_id = g.id')
            ->where('up.date >= ?', $dateStart)
            ->where('up.date <= ?', $dateEnd)
            ->group('up.group_id')
            ->order(array('g.group_name ASC'));
        ;
        $result = $this->_db->fetchAll($select);
        return $result;
    }
}
