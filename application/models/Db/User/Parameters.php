<?php

class Application_Model_Db_User_Parameters extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'user_parameters';

    public function getParametersByUserId($userId)
    {
        $select = $this->_db->select()
            ->from(array('up' => self::TABLE_NAME))
            ->where('up.user_id = ?', $userId);
        $result = $this->_db->fetchRow($select);

        if (empty($result)) {
            // no parameters? need check
            $selectCheck = $this->_db->select()
                ->from(array('u' => Application_Model_Db_Users::TABLE_NAME))
                ->where('u.id = ?', $userId);
            $check = $this->_db->fetchRow($selectCheck);
            if ($check) {
                // Yes. User exists, but without any parameters. This is wrong. Need create default settings
                $this->setUserDefaultParameters($userId);
                $result = $this->_db->fetchRow($select);
            }
        }

        return $result;
    }

    public function setUserDefaultParameters($userId)
    {
        $default = array(
            'user_id' => $userId,
            'used_free_hours' => 'NULL', // use default field value
        );
        $result = $this->_db->insert(self::TABLE_NAME, $default);
        return $result;
    }

    public function setRegularWorkHours($userId, $hours)
    {
        $data = array(
            'regular_work_hours' => (int)$hours,
        );
        $where = array(
            'user_id = ?' => $userId,
        );
        $select = $check = $this->_db->select()
            ->from(self::TABLE_NAME, 'regular_work_hours')
            ->where('user_id = ?', $userId);
        $regularWorkHours = (int)$this->_db->fetchOne($select);
        if ($regularWorkHours == $hours) {
            $result = 1;
        } else {
            $result = $this->_db->update(self::TABLE_NAME, $data, $where);
        }
        return $result;
    }

    public function setOpenFreeHours($userId, $hours)
    {
        $hours = sprintf('%01.2f', $hours);
        $data = array(
            'open_free_hours' => $hours,
        );
        $where = array(
            'user_id = ?' => $userId,
        );
        $select = $check = $this->_db->select()
            ->from(self::TABLE_NAME, 'open_free_hours')
            ->where('user_id = ?', $userId);
        $openFreeHours = $this->_db->fetchOne($select);
        $openFreeHours = sprintf('%01.2f', $openFreeHours);
        if ($openFreeHours == $hours) {
            $result = 1;
        } else {
            $result = $this->_db->update(self::TABLE_NAME, $data, $where);
        }
        return $result;
    }

}
