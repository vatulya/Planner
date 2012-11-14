<?php

class Planner_UserSettingsController extends My_Controller_Action
{

    public $ajaxable = array(
        'index'           => array('html'),
        'save-user-field' => array('json'),
    );

    /**
     * @var Application_Model_User
     */
    protected $_modelUser;

    public function init()
    {
        $this->view->me = $this->_me = $this->_helper->CurrentUser();
        $this->_modelUser = new Application_Model_User();

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
        $modelGroup = new Application_Model_Group();
        $users = $this->_modelUser->getAllUsers();
        foreach ($users as $key => $user) {
            $user['groups'] = $modelGroup->getGroupsByUserId($user['id']);
//            $user['hours'];
//            $user['time_work']['start']; $user['time_work']['end'];
            $users[$key] = $user;
        }
        $roles = Application_Model_Auth::getAllowedRoles();
        $this->view->roles = $roles;
        $this->view->users = $users;
    }

    public function saveUserFieldAction()
    {
        $status  = false;

        $userId = $this->_getParam('user');
        $field  = $this->_getParam('field');
        $value  = $this->_getParam('value');
        $checked = $this->_checkUserField($userId, $field, $value);
        if ($checked) {
            $status = $this->_modelUser->saveField($userId, $field, $value);
        }
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, 'Error!', array());
        }
    }

    protected function _checkUserField($userId, $field, $value)
    {
        $checked = false;
        switch ($field) {
            case 'email':
                $validator = new Zend_Validate_EmailAddress();
                if ($validator->isValid($value)) {
                    $checked = true;
                }
                break;

            case 'birthday':
                try {
                    $date = new DateTime($value);
                    $timestamp = $date->getTimestamp();
                    if ($timestamp > -946772400 && $timestamp < 1262300400) { // 1940-01-01 --- 2010-01-01
                        $checked = true;
                    }
                } catch(Exception $e) { /* error */ }
                break;

            default:
                $checked = true;
                break;

        }

        return $checked;
    }

}