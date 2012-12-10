<?php

class Application_Model_Request extends Application_Model_Abstract
{
    protected $_modelDb;

    public function __construct()
    {
        $this->_modelDb = new Application_Model_Db_User_Requests();
    }

    public function getRequestsByUserId($userId, $status = '')
    {
        if (empty($status)) {
            $requests = $this->_modelDb->getAllByUserId($userId);
            $requests = $this->groupRequestsByStatus($requests);
        } else {
            $requests = $this->_modelDb->getAllByUserId($userId, $status);
        }
        return $requests;
    }

    public function saveRequest($userId, array $requestedDates)
    {
        $result = false;

        $requestedDates = My_DateTime::normalizeDate($requestedDates);
        $requestedDates = My_DateTime::sortDates($requestedDates);
        $requestedDates = My_DateTime::toStringDates($requestedDates);

        $userModelDb = new Application_Model_Db_Users();
        $user = $userModelDb->getUserById($userId);
        if ( ! $user) {
            return $result;
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
        // TODO: here need add check correct requested dates
        $result = $this->_modelDb->insert($user['id'], $requestedDates);

        $subOpenHours = 0;
        foreach ($requestedDates as $date) {
            // TODO: Here need correct calculation work hours of requested dates and sub this hours from Open user hours
            $subOpenHours += 8; // as default work hours
        }
        if ($subOpenHours > 0) {
            $modelDbUserParameters = new Application_Model_Db_User_Parameters();
            $parameters = $modelDbUserParameters->getParametersByUserId($userId);
            $openHours = $parameters['open_free_hours'] - $subOpenHours;
            $modelDbUserParameters->setOpenFreeHours($userId, $openHours);
        }

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
        }
        return $result;
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