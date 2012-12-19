<?php

class Application_Model_Db_Group_Holidays extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'group_holidays';

    public function getHolidays($groupId)
    {
        $select = $this->_db->select()
            ->from(array('gh' => self::TABLE_NAME))
            ->where('gh.group_id = ?', $groupId)
            ->order(array('gh.holiday_date ASC'));
        $result = $this->_db->fetchAll($select);
        return $result;
    }

    public function checkHolidayByDate($date)
    {
        $select = $this->_db->select()
            ->from(array('gh' => self::TABLE_NAME))
            ->where('gh.holiday_date = ?', $date);
        $result = $this->_db->fetchAll($select);
        return $result;
    }

    public function insertGroupHoliday($groupId, $date, $name)
    {
        try {
            $date = new DateTime($date);
            $date = $date->format('Y-m-d');
            $data = array(
                'group_id'     => $groupId,
                'holiday_date' => $date,
                'holiday_name' => $name,
            );
            $this->_db->insert(self::TABLE_NAME, $data);
        } catch (Exception $e) { return false; } // Exception can be in 2 possible situations: Wrong date || This date already exists
        return true;
    }

    public function deleteGroupHoliday($groupId, $date)
    {
        $where = array(
            'group_id = ?'     => $groupId,
            'holiday_date = ?' => $date,
        );
        $this->_db->delete(self::TABLE_NAME, $where);
        return true;
    }

    public function deleteGroupHolidayById($holidayId)
    {
        $where = array(
            'id = ?' => $holidayId,
        );
        $this->_db->delete(self::TABLE_NAME, $where);
        return true;
    }

}
