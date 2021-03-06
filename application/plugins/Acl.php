<?php

class Application_Plugin_Acl extends Zend_Controller_Plugin_Abstract {

    /**
     * @var Zend_Acl
     */
    protected $_acl;

    protected $_aclRoute = '%s.%s';

    /**
     * @var Application_Model_Db_Users
     */
    protected $_user;

//    protected $_roleAliases = array(
//        Application_Model_Auth::ROLE_GUEST       => 'GUEST',
//        Application_Model_Auth::ROLE_USER        => 'USER',
//        Application_Model_Auth::ROLE_GROUP_ADMIN => 'GROUP_ADMIN',
//        Application_Model_Auth::ROLE_ADMIN       => 'ADMIN',
//        Application_Model_Auth::ROLE_SUPER_ADMIN => 'SUPER_ADMIN',
//    );

    /**
     * Location to go to if the user isn't authenticated
     * @var array
     */
    protected $_noAuth = array(
        'module'     => 'planner',
        'controller' => 'auth',
        'action'     => 'login'
    );

    /**
     * Location to go to if the user isn't authorized
     * @var array
     */
    protected $_noAcl = array(
        'module'     => 'planner',
        'controller' => 'index',
        'action'     => 'index'
    );

    public function getUser()
    {
        if ( ! $this->_user) {
            $this->_user = Zend_Controller_Action_HelperBroker::getStaticHelper('CurrentUser')->getCurrentUser();
        }
        return $this->_user;
    }

    public function isLogined() {
        if ($this->getUser()) {
            return true;
        }
        return false;
    }

    public function getRole() {
        $role = Application_Model_Auth::getRole(true);
//        if (isset($this->_roleAliases[$role])) {
//            $role = $this->_roleAliases[$role];
//        }
        return $role;
    }

    public function isAllowed($resource = null, $privelege = null) {
        $role = $this->getRole();
        return $this->_acl->isAllowed($role, $resource, $privelege);
    }

    public function direct() {
        return $this->getUser();
    }

    public function routeShutdown(Zend_Controller_Request_Abstract $request) {
        $this->_acl = $this->_getAcl();
        $module     = $request->getModuleName();
        $controller = $request->getControllerName();
        $action     = $request->getActionName();

        $resource = sprintf($this->_aclRoute, $module, $controller);
        if ( ! $this->_acl->has($resource)) {
            $resource = $module;
        }
        if ( ! $this->_acl->has($resource)) {
            $resource = null;
        }
        if ($this->isAllowed($resource, $action)) {
            return;
        }

        // Auth fail

        $returnUrl = urlencode($request->getRequestUri());
        $request->setParam('return', $returnUrl);

        if ($this->isLogined()) {
            // Loggined, but don't have access
            $module     = $this->_noAcl['module'];
            $controller = $this->_noAcl['controller'];
            $action     = $this->_noAcl['action'];
        } else {
            // NOT Loggined
            $module     = $this->_noAuth['module'];
            $controller = $this->_noAuth['controller'];
            $action     = $this->_noAuth['action'];
        }

        $request
            ->setModuleName($module)
            ->setControllerName($controller)
            ->setActionName($action)
            ->setDispatched(false);
    }

    protected function _getAcl() {
        $front = Zend_Controller_Front::getInstance();
        /** @var $bootstrap Zend_Application_Bootstrap_Bootstrap */
        if (!$bootstrap = $front->getParam('bootstrap')) {
            throw new RuntimeException('Bootstrap not found');
        }
        if (!($acl = $bootstrap->getResource('acl')) || !($acl instanceof Zend_Acl)) {
            throw new RuntimeException('Can not load ACL object.');
        }
        return $acl;
    }

}