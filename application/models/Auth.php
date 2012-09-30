<?php

class Application_Model_Auth extends Application_Model_Abstract
{

    const ROLE_GUEST       = "GUEST";
    const ROLE_USER        = "USER";
    const ROLE_GROUP_ADMIN = "GROUP_ADMIN";
    const ROLE_ADMIN       = "ADMIN";
    const ROLE_SUPER_ADMIN = "SUPER_ADMIN";

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