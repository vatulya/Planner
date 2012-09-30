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
            'action' => $this->_helper->url->url(array('controller' => 'auth', 'action' => 'login'), 'planner_full', true),
            'class'  => 'well form-horizontal',
        ));
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $data = $request->getPost();
                if ($this->_modelAuth->login($data['email'], $data['password'])) {
                    $params = $request->getParams();
                    return $this->_helper->redirector->gotoRoute(array(), 'planner', true);
                }
                $form->addErrorMessage('Wrong login');
            }
        }
        $this->view->loginForm = $form->prepareDecorators();
    }

    public function logoutAction()
    {
        $this->_modelAuth->logout();
        return $this->_helper->redirector->gotoRoute(array(), 'planner', true);
    }

    public function registerAction()
    {
        /** @var $request Zend_Controller_Request_Http */
        $request = $this->getRequest();
        $form = new Planner_Form_Register(array(
            'action' => $this->_helper->url->url(array('controller' => 'auth', 'action' => 'register'), 'planner_full', true),
            'class'  => 'well form-horizontal',
        ));
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $data = $request->getPost();
                if ($this->_modelAuth->register($data)) {
                    return $this->_helper->redirector->gotoRoute(array(), 'planner', true);
                }
            }
        }
        $this->view->registerForm = $form->prepareDecorators();
    }

    public function editUserInformationAction()
    {
        $user = $this->_getUser();
        /** @var $request Zend_Controller_Request_Http */
        $request = $this->getRequest();
        $form = new Planner_Form_EditUser(array(
            'action' => $this->_helper->url->url(array('controller' => 'auth', 'action' => 'edit-user-information'), 'planner_full', true),
            'class'  => 'well form-horizontal',
        ));
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $data = $request->getPost();
                if ($this->_modelAuth->update($user['id'], $data)) {
                    return $this->_helper->redirector->gotoRoute(array(), 'planner', true);
//                    $this->view->message = array('User information updated.'); // TODO: User helper FlashMessenger
                }
            }
        } else {
            $form->populate($user);
        }
        $this->view->user = $user;
        $form->populate(array('userId' => $user['id']));
        $this->view->editForm = $form->prepareDecorators();
    }

    public function changePasswordAction()
    {
        $user = $this->_getUser();
        /** @var $request Zend_Controller_Request_Http */
        $request = $this->getRequest();
        $form = new Planner_Form_ChangePassword(array(
            'action' => $this->_helper->url->url(array('controller' => 'auth', 'action' => 'change-password'), 'planner_full', true),
            'class'  => 'well form-horizontal',
        ));
        if ($request->isPost()) {
            $hash = Application_Model_AuthAdapter::encodePassword($request->getPost('password_old'));
            if ($hash !== $user['password']) {
                $form->addError('Current password is wrong');
            }
            if ($form->isValid($request->getPost())) {
                $data = array(
                    'user_id' => $user['id'],
                    'password' => $form->getValue('password'),
                );
                if ($this->_modelAuth->changePassword($data)) {
                    return $this->_helper->redirector->gotoRoute(array(), 'planner', true);
                }
            }
        }
        $this->view->user = $user;
        $form->populate(array('userId' => $user['id']));
        $this->view->registerForm = $form->prepareDecorators();
    }

    protected function _getUser()
    {
        $userId = $this->_getParam('userId');
        if ($this->_me['role'] !== Application_Model_Auth::ROLE_ADMIN) {
            $userId = $this->_me['id'];
        }
        $this->_setParam('userId', $userId);

        $user = $this->_modelUser->getUserById($userId, false);
        return $user;
    }

}

