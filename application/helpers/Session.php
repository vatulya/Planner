<?php

class Helper_Session extends Zend_Controller_Action_Helper_Abstract
{

    protected $_session;

    public function getSession()
    {
        if ( ! $this->_session) {
            $session = new Zend_Session_Namespace();
            $this->_session = $session;
        }
        return $this->_session;  // can be null;
    }

    public function direct()
    {
        return $this->getSession();
    }

}