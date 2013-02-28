<?php

class Application_Model_Db_Group_Exceptions extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'group_exceptions';

    public function getExceptions($groupId)
    {
        $select = $this->_db->select()
            ->from(array('ge' => self::TABLE_NAME))
            ->where('ge.group_id = ?', $groupId)
            ->order(array('ge.exception_date ASC'));
        $result = $this->_db->fetchAll($select);
        return $result;
    }

    public function checkExceptionByDate($groupId, $date)
    {
        $select = $this->_db->select()
            ->from(array('ge' => self::TABLE_NAME))
            ->where('ge.group_id = ?', $groupId)
            ->where('ge.exception_date = ?', $date);
        $result = $this->_db->fetchAll($select);
        return $result;
    }

    public function editGroupExceptions($groupId, array $oldSelectedDates, array $selectedDates, $maxFreePeople)
    {
        try {
            foreach ($oldSelectedDates as $date) {
                try {
                    $date = new DateTime($date);
                    $date = $date->format('Y-m-d');
                } catch (Exception $e) { continue; }
                $where = array(
                    'group_id = ?' => $groupId,
                    'exception_date = ?' => $date,
                );
                $this->_db->delete(self::TABLE_NAME, $where);
            }
            $this->insertGroupExceptions($groupId, $selectedDates, $maxFreePeople);
            return true;
        } catch (Exception $e) {
            throw new Exception('Error! Database error.');
        }
    }

    public function insertGroupExceptions($groupId, array $selectedDates, $maxFreePeople)
    {
        foreach ($selectedDates as $date) {
            try {
                $date = new DateTime($date);
                $date = $date->format('Y-m-d');
                $data = array(
                    'group_id' => $groupId,
                    'exception_date' => $date,
                    'max_free_people' => $maxFreePeople,
                );
                $this->_db->insert(self::TABLE_NAME, $data);
            } catch (Exception $e) { continue; } // Exception can be in 2 possible situations: Wrong date || This date already exists
        }
        return true;
    }

}
