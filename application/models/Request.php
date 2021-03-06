<?php

class Application_Model_Request extends Application_Model_Abstract
{
    protected $_modelDb;

    public function __construct()
    {
        $this->_modelDb = new Application_Model_Db_User_Requests();
    }

    public function getRequestsByUserId($userId, $status = '', $date = '')
    {
        if (empty($status)) {
            $requests = $this->_modelDb->getAllByUserId($userId);
            $requests = $this->groupRequestsByStatus($requests);
        } else {
            $requests = $this->_modelDb->getAllByUserId($userId, $status, $date);
        }
        return $requests;
    }

    public function saveRequest($userId, array $requestedDates, $checkAllowedFreeTime = true)
    {
        $result = false;

        $requestedDates = My_DateTime::normalizeDate($requestedDates);
        $requestedDates = My_DateTime::sortDates($requestedDates);
        $requestedDates = My_DateTime::toStringDates($requestedDates);

        $userModel = new Application_Model_User();
        $user = $userModel->getUserById($userId);
        if ( ! $user) {
            throw new Exception('Error! Wrong user.');
        }
        $oldRequests = $this->_modelDb->getAllByUserId($user['id']);
        foreach ($oldRequests as $request) {
            $key = array_search($request['request_date'], $requestedDates);
            if ($key !== false) {
                unset($requestedDates[$key]);
            }
        }
        $requestedDates = My_DateTime::normalizeDate($requestedDates); // remove wrong dates
        $requestedDates = My_DateTime::toStringDates($requestedDates);
        if (empty($requestedDates)) {
            $result = true;
            return $result;
        }
        if ($checkAllowedFreeTime) {
            $checkAllowedFreeTime = 0;
            foreach ($requestedDates as $date) {
                $day = Application_Model_Day::factory($date, $user);
                $checkAllowedFreeTime += (int)$day->getWorkTime();
            }
            if ($checkAllowedFreeTime > 0) {
                $modelDbUserParameters = new Application_Model_Db_User_Parameters();
                $parameters            = $modelDbUserParameters->getParametersByUserId($userId);
                $checkAllowedFreeTime  = $parameters['total_free_time'] + $parameters['additional_free_time'] - $checkAllowedFreeTime;
                if ($checkAllowedFreeTime < 0) {
                    throw new Exception('Error! This user don\'t have so much free time.');
                    // Error. Too much free days
                    $result = false;
                    return $result;
                }
            }
        }
        // TODO: here need add check correct requested dates
        $result = $this->_modelDb->insert($user['id'], $requestedDates);

        return $result;
    }

    public function setStatusById($requestId, $requestStatus, $comment, $adminId)
    {
        $checked = false;
        $result = false;
        $allowedStatuses = array(
            Application_Model_Db_User_Requests::USER_REQUEST_STATUS_OPEN,
            Application_Model_Db_User_Requests::USER_REQUEST_STATUS_APPROVED,
            Application_Model_Db_User_Requests::USER_REQUEST_STATUS_REJECTED,
            Application_Model_Db_User_Requests::USER_REQUEST_STATUS_REFUNDED,
        );
        $comment = trim(strip_tags($comment));
        if (in_array($requestStatus, $allowedStatuses)) {
            if ($requestStatus == Application_Model_Db_User_Requests::USER_REQUEST_STATUS_REJECTED) {
                if ( ! empty($comment)) {
                    $checked = true;
                }
            } else {
                $checked = true;
            }
        }
        if ($checked) {
            $result = $this->_modelDb->setStatusById($requestId, $requestStatus, $comment, $adminId);
            if ($result) {
                if ($requestStatus === Application_Model_Db_User_Requests::USER_REQUEST_STATUS_APPROVED
                    || $requestStatus === Application_Model_Db_User_Requests::USER_REQUEST_STATUS_REFUNDED
                ) {
                    $request = $this->_modelDb->getById($requestId);
                    $modelUser = new Application_Model_User();
                    $userParameters = $modelUser->getParametersByUserId($request['user_id']);
                    $day = Application_Model_Day::factory($request['request_date'], $request['user_id']);
                    $workTime = $day->getWorkTime();
                    if ($requestStatus === Application_Model_Db_User_Requests::USER_REQUEST_STATUS_APPROVED) {
                        $newUserAllowedFreeTime = $userParameters['used_free_time'] + $workTime;
                    } else {
                        $newUserAllowedFreeTime = $userParameters['used_free_time'] - $workTime;
                    }
                    $modelDbUserParameters = new Application_Model_Db_User_Parameters();
                    $modelDbUserParameters->setUsedFreeTime($request['user_id'], $newUserAllowedFreeTime);
                }
            }
        }
        return $result;
    }

    public function createExtremelyRequest($userId, $date, $adminId)
    {
        if ($this->saveRequest($userId, array($date), false)) {
            $request = $this->_modelDb->getAllByUserId($userId, '', $date);
            return $this->setStatusById($request['id'], Application_Model_Db_User_Requests::USER_REQUEST_STATUS_APPROVED, 'Extremely approve holiday', $adminId);
        }
        return false;
    }

    protected function groupRequestsByStatus(array $requests)
    {
        $grouped = array(
            Application_Model_Db_User_Requests::USER_REQUEST_STATUS_OPEN     => array(),
            Application_Model_Db_User_Requests::USER_REQUEST_STATUS_APPROVED => array(),
            Application_Model_Db_User_Requests::USER_REQUEST_STATUS_REJECTED => array(),
        );
        foreach ($requests as $request) {
            $status = $request['status'];
            if (array_key_exists($status, $grouped)) {
                $grouped[$status][] = $request;
            }
        }
        return $grouped;
    }

}