<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initUserSession()
    {
        $session = new Zend_Session_Namespace();
        $session->authRole = Application_Model_Auth::getRole();
//        $session->backUrl = $_SERVER['HTTP_REFERER'];
    }

    protected function _initAcl()
    {
        return include APPLICATION_PATH . '/configs/acl.php';
    }

    protected function _initRoute()
    {

        $router = Zend_Controller_Front::getInstance()->getRouter();
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/routes.ini', 'production');
        $router->addConfig($config, 'routes');
    }

    protected function _initAutoload()
    {
        $this->getApplication()->getAutoloader()->registerNamespace('Sch');
        $this->getApplication()->getAutoloader()->registerNamespace('My_');
        $this->getApplication()->getAutoloader()->registerNamespace('Stemmer');
    }

    protected function _initPlannerAutoload()
    {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'Planner_',
            'basePath'  => APPLICATION_PATH .'/modules/planner',
            'resourceTypes' => array (
                'form' => array(
                    'path' => 'forms',
                    'namespace' => 'Form',
                ),
                'model' => array(
                    'path' => 'models',
                    'namespace' => 'Model',
                ),
            )
        ));
        return $autoloader;
    }

}

