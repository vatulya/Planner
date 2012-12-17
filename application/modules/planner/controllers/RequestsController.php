<?php

class Planner_RequestsController extends My_Controller_Action
{

    public $ajaxable = array(
        'index' => array('html'),
        'save-request' => array('json'),
    );

    /**
     * @var Application_Model_User
     */
    protected $_modelUser;
    /**
     * @var Application_Model_Request
     */
    protected $_modelRequest;

    public function init()
    {
        $this->view->me = $this->_me = $this->_helper->CurrentUser();
        $this->_modelUser = new Application_Model_User();
        $this->_modelRequest = new Application_Model_Request();

        if ( ! $this->_getParam('user')) {
            $this->_setParam('user', $this->_me['id']);
        } else {
            $allowed = false;
            if ($this->_me['role'] >= Application_Model_Auth::ROLE_ADMIN) {
                $allowed = true;
            }
            if ( ! $allowed) {
                $this->_setParam('user', $this->_me['id']);
            }
        }

        parent::init();
    }

    public function indexAction()
    {
        $userId = $this->_getParam('user');
        $userParameters = $this->_modelUser->getParametersByUserId($userId);
        $userAllowedFreeTime = $this->_modelUser->getAllowedFreeTime($userId);
        $userRequests = $this->_modelRequest->getRequestsByUserId($userId);
        $openRequestsWorkTime = 0;
        foreach ($userRequests[Application_Model_Db_User_Requests::USER_REQUEST_STATUS_OPEN] as $request) {
            $day = Application_Model_Day::factory($request['request_date'], $userId);
            $openRequestsWorkTime += (int)$day->getWorkTime();
        }
        $userPreAllowedFreeTime  = $userAllowedFreeTime - $openRequestsWorkTime;
        $userPreAllowedFreeHours = My_DateTime::TimeToDecimal($userPreAllowedFreeTime);
        $userPreUsedFreeHours    = My_DateTime::TimeToDecimal($userParameters['total_free_time'] - $userPreAllowedFreeTime);
        $blocked = $this->_getBlockedDatesByUserId($userId);
        $assign = array(
            'userRequests'       => $userRequests,
            'blocked'            => $blocked,
            'preAllowedFreeHours' => $userPreAllowedFreeHours,
            'preUsedFreeHours'    => $userPreUsedFreeHours,
        );
        $this->view->me['parameters'] = $userParameters;
        $this->view->assign($assign);
    }

    public function saveRequestAction()
    {
        $userId = $this->_getParam('user');
        $modelRequests = new Application_Model_Request();
        $selectedDates = $this->_getParam('selected_dates', array());
        $blocked = $this->_getBlockedDatesByUserId($userId);
        $check = array_intersect($blocked, $selectedDates);
        if (count($check)) {
            $status = false;
        } else {
            $status = $modelRequests->saveRequest($userId, $selectedDates);
        }

        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, 'Error!', array());
        }
    }

    protected function _getBlockedDatesByUserId($userId)
    {
        $blocked = array();
        $modelGroup = new Application_Model_Group();
        $groups = $modelGroup->getGroupsByUserId($userId);
        foreach ($groups as $group) {
            $groupBlocked = $modelGroup->getNotAllowedForRequestsDates($group['id']);
            foreach ($groupBlocked as $block) {
                try {
                    $date = new DateTime($block['calendar_date']);
                    $date = $date->format('Y-m-d');
                    $blocked[$date] = $date;
                } catch (Exception $e) {continue;}
            }
        }
        return $blocked;
    }

}