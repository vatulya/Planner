<?php

class Management_IndexController extends My_Controller_Action
{

    public function init()
    {
        parent::init();
        $this->_modelUser    = new Application_Model_User();
        $this->_me           = $this->_helper->CurrentUser();
        $this->view->me      = $this->_helper->CurrentUser();
        if ($this->_me['role'] === Application_Model_Auth::ROLE_ADMIN) {
            $viewScriptPath = $this->view->getScriptPath('');
            $viewScriptPath = preg_replace('/views.*/', 'views_admin' . DIRECTORY_SEPARATOR, $viewScriptPath);
            $this->view->addBasePath($viewScriptPath);
        } else {
            $this->_setParam('userId', $this->_me['id']);
        }
    }

    public function indexAction()
    {

    }

}