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

    public function getGroupById($groupId)
    {
        $select = $this->_db->select()
            ->from(array('g' => self::TABLE_NAME))
            ->where('g.id = ?', $groupId);
        $result = $this->_db->fetchRow($select);
        return $result;
    }

    public function getGroupsByUserId($userId)
    {
        $select = $this->_db->select()
            ->from(array('ug' => Application_Model_Db_User_Groups::TABLE_NAME), array())
            ->join(array('g' => self::TABLE_NAME), 'ug.group_id = g.id', array('*'))
            ->where('ug.user_id = ?', $userId)
            ->order('g.group_name ASC');
        $result = $this->_db->fetchAll($select);
        return $result;
    }

    public function insertGroup(array $group)
    {
        $data = array(
            'group_name' => $group['group_name'],
            'color'      => $group['color'],
        );
        $result = $this->_db->insert(self::TABLE_NAME, $data);
        if ($result) {
            $groupId = $this->_db->query('SELECT LAST_INSERT_ID();')->fetchColumn();
            $settings = new Application_Model_Db_Group_Settings();
            $settings->setDefaultGroupSettings($groupId);
        }
        return $result;
    }

    public function updateGroup($groupId, array $group)
    {
        $data = array(
            'group_name' => $group['group_name'],
            'color'      => $group['color'],
        );
        $result = $this->_db->update(self::TABLE_NAME, $data, array('id = ?' => $groupId));
        return $result;
    }

    public function deleteGroup($groupId)
    {
        $this->_db->delete(self::TABLE_NAME, array('id = ?' => $groupId));
        $settings = new Application_Model_Db_Group_Settings();
        $settings->deleteGroupSettings($groupId);
        return true;
    }

    public function getNotAllowedForRequestsDates($groupId)
    {
        $maxFreePeople = 0;
        $select = $this->_db->select()
            ->from(Application_Model_Db_Group_Settings::TABLE_NAME, array('max_free_people'))
            ->where('group_id = ?', $groupId);
        $maxFreePeople = (int)$this->_db->fetchOne($select);
        $query = 'DROP TEMPORARY TABLE IF EXISTS tmp_free_people';
        $this->_db->query($query);
        $query = '
            CREATE TEMPORARY TABLE tmp_free_people (
                id INT NOT NULL AUTO_INCREMENT,
                calendar_date DATE NOT NULL,
                user_count INT NOT NULL DEFAULT 0,
                users_limit INT NOT NULL DEFAULT ' . $maxFreePeople . ',
                PRIMARY KEY(id),
                INDEX (calendar_date),
                INDEX (user_count)
            )
        ';
        $this->_db->query($query);
        $query = '
            INSERT INTO tmp_free_people (calendar_date, user_count)
            SELECT
                `date` AS calendar_date,
                "1" AS user_count
            FROM
                ' . Application_Model_Db_User_Planning::TABLE_NAME . '
            WHERE
                status1 IN (4,5,6) -- not work statuses
                AND `date` > NOW()
        ';
        $this->_db->query($query);
        $query = '
            INSERT INTO tmp_free_people (calendar_date, user_count)
            SELECT
                request_date AS calendar_date,
                "1" AS user_count
            FROM
                ' . Application_Model_Db_User_Requests::TABLE_NAME . '
            WHERE
                status IN ("' . Application_Model_Db_User_Requests::USER_REQUEST_STATUS_OPEN . '", "' . Application_Model_Db_User_Requests::USER_REQUEST_STATUS_APPROVED . '")
                AND request_date > NOW()
        ';
        $this->_db->query($query);
        $query = '
            UPDATE tmp_free_people tfp, ' . Application_Model_Db_Group_Exceptions::TABLE_NAME . ' ge
            SET
                tfp.users_limit = ge.max_free_people
            WHERE
                ge.group_id = 0 -- default. lowest prio
                AND tfp.calendar_date = ge.exception_date
        ';
        $this->_db->query($query);
        $query = '
            UPDATE tmp_free_people tfp, ' . Application_Model_Db_Group_Exceptions::TABLE_NAME . ' ge
            SET
                tfp.users_limit = ge.max_free_people
            WHERE
                ge.group_id = ' . (int)$groupId . '
                AND tfp.calendar_date = ge.exception_date
        ';
        $this->_db->query($query);
        $query = '
            SELECT
                calendar_date,
                SUM(user_count) AS cnt,
                users_limit
            FROM
                tmp_free_people
            GROUP BY
                calendar_date
            HAVING
                cnt >= users_limit
            ORDER BY calendar_date ASC
        ';
        $notAllowedDates = $this->_db->query($query)->fetchAll();
        return $notAllowedDates;
    }

}
