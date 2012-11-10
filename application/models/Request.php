<?php

class Application_Model_Request extends Application_Model_Abstract
{
    protected $_modelDb;

    public function __construct()
    {
        $this->_modelDb = new Application_Model_Db_User_Requests();
    }

    public function getUserRequests($userId)
    {
        $requests = array();
        $requests = $this->_modelDb->getAllByUserId($userId);
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
        return $result;
    }

}