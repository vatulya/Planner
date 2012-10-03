<?php

class Application_Model_Auth extends Application_Model_Abstract
{

    const ROLE_GUEST       = 0;
    const ROLE_USER        = 20;
    const ROLE_GROUP_ADMIN = 50;
    const ROLE_ADMIN       = 80;
    const ROLE_SUPER_ADMIN = 100;

    public function __construct()
    {
        $this->_modelDb = new Application_Model_Db_Users();
    }

    public function login($email, $password)
    {
        $adapter = new Application_Model_AuthAdapter($email, $password);
        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($adapter);
        if ($result->isValid()) {
            return $result->getIdentity();
        }
        return false;
    }

    public function logout()
    {
        $auth = Zend_Auth::getInstance();
        $auth->clearIdentity();
        return true;
    }

    public function getCurrentUser()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            return $auth->getIdentity();
        }
        return null;
    }

    static public function getRole()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $user = $auth->getIdentity();
            return $user['role'];
        }
        return self::ROLE_GUEST;
    }

}
