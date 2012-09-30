<?php

class Planner_AuthController extends My_Controller_Action
{

    /**
     * @var Application_Model_Auth
     */
    protected $_modelAuth;

    /**
     * @var Application_Model_User
     */
    protected $_modelUser;

    protected $_me;

    public function init()
    {
        parent::init();
        $this->_modelAuth = new Application_Model_Auth();
        $this->_modelUser = new Application_Model_User();
        $this->_me = $this->_modelAuth->getCurrentUser();
    }

    public function indexAction()
    {
        $this->loginAction();
    }

    public function loginAction()
    {
        /** @var $request Zend_Controller_Request_Http */
        $request = $this->getRequest();
        $form = new Planner_Form_Login(array(
            'action' => $this->_helper->url->url(array('controller' => 'auth', 'action' => 'login'), 'management_full', true),
            'class'  => 'well form-horizontal',
        ));
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $data = $request->getPost();
                if ($this->_modelAuth->login($data['email'], $data['password'])) {
                    $params = $request->getParams();
                    return $this->_helper->redirector->gotoRoute(array(), 'management', true);
                }
                $form->addErrorMessage('Wrong login');
            }
        }
        $this->view->loginForm = $form->prepareDecorators();
    }

    public function logoutAction()
    {
        $this->_modelAuth->logout();
        return $this->_helper->redirector->gotoRoute(array(), 'management', true);
    }

}

