<?php

class Helper_CurrentUser extends Zend_Controller_Action_Helper_Abstract
{

    protected $_currentUser;

    public function getCurrentUser()
    {
        if ( ! $this->_currentUser) {
            $auth = new Application_Model_Auth();
            $this->_currentUser = $auth->getCurrentUser();
            unset($this->_currentUser['password']);
        }
        return $this->_currentUser;  // can be null;
    }

    public function direct()
    {
        return $this->getCurrentUser();
    }

}
