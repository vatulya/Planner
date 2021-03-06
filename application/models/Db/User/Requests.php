<?php

class Application_Model_Db_User_Requests extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'user_requests';

    const USER_REQUEST_STATUS_OPEN = 'open';
    const USER_REQUEST_STATUS_APPROVED = 'approved';
    const USER_REQUEST_STATUS_REJECTED = 'rejected';
    const USER_REQUEST_STATUS_REFUNDED = 'refunded';

    public function getAllByUserId($userId, $status = '', $date = '')
    {
        try {
            $select = $this->_db->select()
                ->from(array('ur' => self::TABLE_NAME))
                ->where('ur.user_id = ?', $userId);
            if ( ! empty($status)) {
                $select->where('ur.status = ?', $status);
            }
            if (empty($date)) {
                $select->order('request_date ASC');
                $result = $this->_db->fetchAll($select);
            } else {
                $select->where('ur.request_date = ?', $date);
                $result = $this->_db->fetchRow($select);
            }
        } catch (Exception $e) {
            throw new Exception('Error! Database error!');
        }
        return $result;
    }

    public function insert($userId, array $requestedDates)
    {
        $requestId = $this->_generateRequestId();
        $data = array(
            'user_id' => $userId,
            'request_id' => $requestId,
            'created' => date_create()->format('Y-m-d H:i:s'),
        );
        $result = true;
        foreach ($requestedDates as $date) {
            $data['request_date'] = $date;
            $result = $this->_db->insert(self::TABLE_NAME, $data);
        }
        return $result;

    }

    public function setStatusById($requestId, $status, $comment, $adminId)
    {
        $data = array(
            'status'   => $status,
            'comment'  => $comment,
            'admin_id' => $adminId,
        );
        $result = $this->_db->update(self::TABLE_NAME, $data, array('id = ?' => $requestId));
        return $result;
    }

    public function getById($id)
    {
        $select = $this->_db->select()
            ->from(self::TABLE_NAME)
            ->where('id = ?', $id);
        $result = $this->_db->fetchRow($select);
        return $result;
    }

    protected function _generateRequestId()
    {
        $date = new DateTime();
        $h = $date->format('H');
        $m = $date->format('i');
        $m = floor($m / 15) * 15; // like 53 / 15 = 3 * 15 = 45 min
        $s = 0;
        $date->setTime($h, $m, $s); // rounded to :15 mins
        $requestId = $date->getTimestamp();
        return $requestId;
    }
}
