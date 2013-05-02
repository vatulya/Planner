<?php

class Planner_AlertsController extends My_Controller_Action
{
    const HISTORY_WEEK_NUM = 4;
    public $ajaxable = array(
        'index'           => array('html'),
        'get-user-alerts' => array('json'),
        'delete-email'                              => array('json'),
        'add-new-email'                             => array('json'),
    );

    /**
     * @var Application_Model_User
     */
    protected $_modelUser;

    public function init()
    {
        $this->view->me = $this->_me = $this->_helper->CurrentUser();
        $this->_modelUser = new Application_Model_User();
        $this->_modelHistory = new Application_Model_History();
        $this->_modelOverview = new Application_Model_Overview();
        $this->_modelAlert = new Application_Model_Alert();

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
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $week = $request->getParam('week');
        $year = $request->getParam('year');
        if (empty($week) || empty($year))  {
            $weekYear = My_DateTime::getWeekYear();
            $year = $weekYear['year'];
            $week = $weekYear['week'];
        }
        $this->view->nextWeekYear = My_DateTime::getNextYearWeek($year,$week);
        $this->view->prevWeekYear = My_DateTime::getPrevYearWeek($year,$week);

        $historyDateWeekYear = My_DateTime::getNumHistoryWeeks($year, $week);
        $groupsUserData = $this->_modelOverview->getAllGroupAndUserWeekSummary($week, $year);
        $groupsAlertSummData = $this->_modelAlert->getAllGroupWeekAlertSummary($week, $year);
        $subscribedEmail = new Application_Model_Db_User_Mail();
        $previousYearUsersAlerts = $this->_modelAlert->getUserAlertsByYear($year - 1);
        $previousYearGroupAlerts = $this->_modelAlert->getGroupAlertsByYear($year - 1);
        $controller = $this->_request->getParam('controller');
        $date = new My_DateTime();
        $date = $date->format('Y-m-d');
        $this->view->emails                  = $subscribedEmail->getListMail($controller);
        $this->view->week                    = $week;
        $this->view->year                    = $year;
        $this->view->historyDateWeekYear     = $historyDateWeekYear;
        $this->view->groups                  = $groupsUserData;
        $this->view->groupsAlertsSumm        = $groupsAlertSummData;
        $this->view->countNoCheckInUsers     = $this->_modelAlert->getCountAlertsByType($date, Application_Model_Planning::STATUS_DAY_GREEN);
        $this->view->countToManyPeopleOff    = $this->_modelAlert->getNumToManyPeopleOff($date);
        $this->view->previousYearUsersAlerts = $previousYearUsersAlerts;
        $this->view->previousYearGroupAlerts = $previousYearGroupAlerts;
    }

    public function getUserAlertsAction()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $week = $request->getParam('week');
        $year = $request->getParam('year');
        $userId = $this->_getParam('user_id');
        $groupId = $this->_getParam('group_id');
        $weekGroupAlerts = $this->_modelAlert->getUserAlertsByWeek($userId, $groupId, $week, $year);
        $group = new Application_Model_Group();
        $this->view->weekGroupAlerts = $weekGroupAlerts;
        $this->view->userId          = $userId;
        $this->view->group           = $group->getGroupById($groupId);
        $this->_helper->layout->disableLayout();
    }


    public function deleteEmailAction()
    {
        $emailId = $this->_getParam('email', 0);
        $status = false;
        if ($emailId > 0) {
            $userMail = new Application_Model_Db_User_Mail();
            $status = $userMail->deleteEmailById($emailId);
        }
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, 'Error!', array());
        }
    }

    public function addNewEmailAction()
    {
        $newEmail          = $this->_getParam('new_email', '');
        $status = false;
        //TODO maybe need add check for email format
        if (!empty($newEmail)) {
            $userMail = new Application_Model_Db_User_Mail();
            $status = $userMail->saveEmail($newEmail, $this->_request->getParam('controller'));
        }
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, 'Error!', array());
        }
    }
}