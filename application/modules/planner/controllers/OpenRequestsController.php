<?php

class Planner_OpenRequestsController extends My_Controller_Action
{

    public $ajaxable = array(
        'index' => array('html'),
        'set-status' => array('json'),
        'get-user-requests' => array('html'),
    );

    /**
     * @var Application_Model_User
     */
    protected $_modelUser;

    public function init()
    {
        $this->view->me = $this->_me = $this->_helper->CurrentUser();
        $this->_modelUser = new Application_Model_User();

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
        $modelGroup = new Application_Model_Group();
        $modelRequest = new Application_Model_Request();
        $groups = array();
        if ($this->_me['role'] >= Application_Model_Auth::ROLE_ADMIN) {
            $groups = $modelGroup->getAllGroups();
        } else { // group Admin
            foreach ($this->_me['admin_groups'] as $groupId) {
                $groups[] = $modelGroup->getGroupById($groupId);
            }
        }
        foreach ($groups as $key => $group) {
            $users = $this->_modelUser->getAllUsersByGroup($group['id']);
            $group['users'] = $users;
            foreach ($group['users'] as $userKey => $user) {
                $user['requests'] = $modelRequest->getRequestsByUserId($user['id']);
                $group['users'][$userKey] = $user;
            }
            $groups[$key] = $group;
        }

        $this->view->groups = $groups;
    }

    public function setStatusAction()
    {
        $message = 'Error! Something wrong.';
        $modelRequest = new Application_Model_Request();
        $modelGroup = new Application_Model_Group();
        $userId = $this->_getParam('user_id');
        $allowed = false;
        $status = false;
        try {
            if ($this->_me['role'] >= Application_Model_Auth::ROLE_ADMIN) {
                $allowed = true;
            } else { // group Admin
                $userGroups = $modelGroup->getGroupsByUserId($userId);
                foreach ($userGroups as $group) {
                    if (in_array($group['id'], $this->_me['admin_groups'])) {
                        $allowed = true;
                    }
                }
            }
            $requestStatus = $this->_getParam('status');
            $comment = $this->_getParam('comment');
            if ($allowed) {
                $id = $this->_getParam('id');
                if (empty($id) && $this->_getParam('type') == 'extremely_approve') {
                    $status = $modelRequest->createExtremelyRequest($this->_getParam('user_id'), $this->_getParam('request_date'), $this->_me['id']);
                } else {
                    $status = $modelRequest->setStatusById($this->_getParam('id'), $requestStatus, $comment, $this->_me['id']);
                }
            } else {
                $message = 'Error! Sorry. You don\'t have permissions.';
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, $message, array());
        }
    }

    public function getUserRequestsAction()
    {
        $userId = $this->_getParam('user_id');
        $modelRequest = new Application_Model_Request();
        $openRequests = $modelRequest->getRequestsByUserId($userId,Application_Model_Db_User_Requests::USER_REQUEST_STATUS_OPEN);
        $rejectedRequests = $modelRequest->getRequestsByUserId($userId,Application_Model_Db_User_Requests::USER_REQUEST_STATUS_REJECTED);
        $this->view->openRequests = $openRequests;
        $this->view->rejectedRequests = $rejectedRequests;
        $this->view->userId = $userId;
        $this->_helper->layout->disableLayout();
    }
}