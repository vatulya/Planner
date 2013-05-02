<?php

class Application_Model_Db_User_Alerts extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'user_alerts';

    public function getUserAlertsByDate($userId, $groupId, $date)
    {
        $select = $this->_db->select()
            ->from(array('ua' => self::TABLE_NAME))
            ->where('ua.group_id = ?', $groupId)
            ->where('ua.user_id = ?', $userId)
            ->where('ua.date = ?', $date);
        $result = $this->_db->fetchRow($select);
        return $result;
    }

    public function getNoSendedAlerts($userId, $groupId, $date)
    {
        $select = $this->_db->select()
            ->from(array('ua' => self::TABLE_NAME))
            ->joinInner(array('s' => 'status'), 's.id = ua.type')
            ->where('ua.notice_mailed = ?', 0)
            ->where('ua.group_id = ?', $groupId)
            ->where('ua.user_id = ?', $userId)
            ->where('ua.date = ?', $date);
        $result = $this->_db->fetchRow($select);
        return $result;
    }

    public function getUserAlertsByDateInterval($userId, $groupId, $dateStart, $dateEnd)
    {
        $select = $this->_db->select()
            ->from(array('ua' => self::TABLE_NAME), array('*'))
            ->joinInner(array('s' => 'status'), 's.id = ua.type')
            ->where('ua.group_id = ?', $groupId)
            ->where('ua.user_id = ?', $userId)
            ->where('ua.date >= ?', $dateStart)
            ->where('ua.date < ?', $dateEnd)
            ->order('date ASC');
        $result = $this->_db->fetchAll($select);
        return $result;
    }

    public function saveAlert($date, $type, $userId, $groupId)
    {
        try {
            $result = $this->_db->insert(self::TABLE_NAME,
                array(
                    'date'     => $date,
                    'type'     => $type,
                    'user_id'  => $userId,
                    'group_id' => $groupId
                )
            );
        } catch (Zend_Db_Exception $e) {
            // ignored
            $result = false;
        }
        return $result;
    }

    public function updateAlertsNoticedMailed($userId, $groupId, $date)
    {
        try {
            $result = $this->_db->update(self::TABLE_NAME,
                array('notice_mailed' => 1,),
                array(
                    "user_id = '" . (int)$userId . "'",
                    "group_id = '" . (int)$groupId . "'",
                    "date = '" . $date . "'",
                )
            );
        } catch (Zend_Db_Exception $e) {
            // ignored
            $result = false;
        }
        return $result;
    }

    public function getUserAlertsByYear($year)
    {
        $select = $this->_db->select()
            ->from(array('ua' => self::TABLE_NAME),
            array(
                '*',
                'count(ua.id)'
            )
        )
            //->where('ua.group_id = ?', $groupId)
            //->where('ua.user_id = ?', $userId)
            //->where('ua.date = ?', $year)
            ->group(array('ua.group_id', 'ua.user_id'));
        $result = $this->_db->fetchAll($select);
        return $result;
    }

    public function getCountAlertsByType($date, $type = false)
    {
        $select = $this->_db->select()
            ->from(array('ua' => self::TABLE_NAME), array('count(ua.id)'))
            ->where('ua.date = ?', $date)
            ;
        if ($type !== false) {
            $select->where('ua.type = ?', $type)
            ->group(array('ua.type'));
        } else {
            $select->group(array('ua.date'));
        }
        $result = $this->_db->fetchOne($select);
        return $result;
    }
}