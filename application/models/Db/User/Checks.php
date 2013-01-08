<?php

class Application_Model_Db_User_Checks extends Application_Model_Db_Abstract
{

    const TABLE_NAME = 'user_checks';

    public function userCheck($userId, $check)
    {
        $field = ($check == Application_Model_User::USER_CHECK_OUT ? 'check_out' : 'check_in');
        $date = new DateTime();
        $fields = array(
            'user_id'    => $userId,
            'check_date' => $date->format('Y-m-d'),
            $field       => $date->format('H:i:s'),
        );
        $select = $this->_db->select()
            ->from(array('uc' => self::TABLE_NAME), array('id'))
            ->where('uc.user_id = ?', $userId)
            ->where('uc.check_date = ?', $date->format('Y-m-d'));
        $id = $this->_db->fetchOne($select);
        if ($id) {
            $this->_db->update(self::TABLE_NAME, $fields, array('id = ?' => $id));
        } else {
            $this->_db->insert(self::TABLE_NAME, $fields);
        }
        return true;
    }

    public function isAllowedCheckin(array $user, $check)
    {
        $now = new DateTime();
        $allowed = false;
        if (empty($user['check_date'])) {
            $allowed = true;
        } else {
            if ($user['check_date'] == $now->format('Y-m-d')) {
                if (empty($user['check_in']) && $check == Application_Model_User::USER_CHECK_IN) {
                    $allowed = true;
                } elseif ( ! empty($user['check_in']) && empty($user['check_out']) && $check == Application_Model_User::USER_CHECK_OUT) {
                    $in = $user['check_date'] . ' ' . $user['check_in'];
                    $in = strtotime($in);
                    if ($in && $in < $now->format('U')) {
                        $allowed = true;
                    }
                }
            }
        }
        return $allowed;
    }

    public function getUserCheckTimeByIdDate($userId, $date)
    {
        $select = $this->_db->select()
            ->from(array('uc' => self::TABLE_NAME), array('*'))
            ->where('uc.user_id = ?', $userId)
            ->where('uc.check_date = ?', $date);
        $result = $this->_db->fetchRow($select);
        return $result;
    }
}