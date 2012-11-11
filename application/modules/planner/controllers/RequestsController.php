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
        $userParameters = $this->_modelUser->getParametersByUserId($this->_getParam('user'));
        $userRequests = $this->_modelRequest->getRequestsByUserId($this->_getParam('user'));
        $this->view->me['parameters'] = $userParameters;
        $this->view->userRequests = $userRequests;
    }

    public function saveRequestAction()
    {
        $modelRequests = new Application_Model_Request();
        $selectedDates = $this->_getParam('selected_dates', array());
        $status = $modelRequests->saveRequest($this->_getParam('user'), $selectedDates);
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, 'Error!', array());
        }
    }

}