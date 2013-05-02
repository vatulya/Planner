<?php

class Planner_OverviewController extends My_Controller_Action
{
    public $ajaxable = array(
        'index'                                     => array('html'),
        'export'                                    => array('html'),
        'update-history-hour'                       => array('json'),
        'recalculate-history-week-for-user-by-date' => array('json'),
        'delete-email'                              => array('json'),
        'add-new-email'                             => array('json'),
        'get-year-totals'                           => array('html'),
    );

    /**recalculate History Week For User By Date Action
     * @var Application_Model_User
     */
    protected $_modelUser;
    protected $_modelGroup;
    protected $_modelHistory;
    protected $_modelOverview;

    public function init()
    {
        $this->view->me = $this->_me = $this->_helper->CurrentUser();
        $this->_modelUser = new Application_Model_User();
        $this->_modelHistory = new Application_Model_History();
        $this->_modelOverview = new Application_Model_Overview();

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
        $this->_me      = $this->_helper->CurrentUser();
        $this->view->me = $this->_me = $this->_helper->CurrentUser();
        $this->_modelGroup    = new Application_Model_Group();
        $this->_modelPlanning = new Application_Model_Planning();

        $this->view->weekDays     =  $weekDays = My_DateTime::getWeekDays();
        $this->view->currentWeekYear = My_DateTime::getWeekYear();
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
        $subscribedEmail = new Application_Model_Db_User_Mail();
        $controller = $this->_request->getParam('controller');
        $this->view->emails              = $subscribedEmail->getListMail($controller);
        $this->view->week                = $week;
        $this->view->year                = $year;
        $this->view->historyDateWeekYear = $historyDateWeekYear;
        $this->view->groups              = $groupsUserData;
    }

    public function recalculateHistoryWeekForUserByDateAction()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $date = $request->getParam('date');
        $userId = $request->getParam('user_id');
        if (empty($date) || empty($userId))  {
            $this->_response(0, 'Error!', 'Date or User is empty.');
        }

        $status = $this->_modelHistory->recalculateHistoryWeekForUser($userId, $date);
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, 'Error!', "Recalculate is wrong.");
        }
    }

    public function exportAction()
    {
        $week  = $this->_getParam('week');
        $year  = $this->_getParam('year');

        $filename = $this->_modelOverview->getHistoryDataFile($week, $year);
        $fullPath = EXPORT_PATH_DIR . '/' . $filename;

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $this->getResponse()->setRawHeader( "Content-Type: application/vnd.ms-excel; charset=UTF-8" )
            ->setRawHeader( "Content-Disposition: attachment; filename=" .  $filename)
            ->setRawHeader( "Content-Transfer-Encoding: binary" )
            ->setRawHeader( "Expires: 0" )
            ->setRawHeader( "Cache-Control: must-revalidate, post-check=0, pre-check=0" )
            ->setRawHeader( "Pragma: public" )
            ->setRawHeader( "Content-Length: " . filesize( $fullPath ) )
            ->sendResponse();

        readfile( $fullPath );
    }

    public function updateHistoryHourAction()
    {
        $status  = false;
        if ($this->_me['role'] == Application_Model_Auth::ROLE_SUPER_ADMIN) {
            $field  = $this->_getParam('field');
            $value  = $this->_getParam('value');
            $userId  = $this->_getParam('user');
            $groupId  = $this->_getParam('group');
            $year  = $this->_getParam('year');
            $week  = $this->_getParam('week');
            if ($value >= 0 ) {
                if ($field == 'free_time') {
                    $parameter = new Application_Model_Db_User_Parameters();
                    $status = $parameter->setAdditionalFreeTime($userId, $value * 3600, $year);
                } else {
                    $status = $this->_modelHistory->updateHistoryWeekHour($userId, $groupId, $field, $value, $year, $week);
                }
            }
        }
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, 'Error!', "No valid time");
        }
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

    public function getYearTotalsAction()
    {
        $year  = $this->_getParam('year');
        $users = $this->_modelUser->getAllUsersForYear($year);
        if (!empty($users)) {
            foreach ($users as $keyUser => $user) {
                $users[$keyUser]['weekHours'] = $this->_modelHistory->getUserWeekDataByWeekYear($user['id'], false, false, $year);
            }
        }
        $this->view->users = $users;
        $this->view->prevYear = $year - 1;
        $this->view->year = $year;
    }

}