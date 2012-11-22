<?php

class Planner_UserSettingsController extends My_Controller_Action
{

    public $ajaxable = array(
        'index'           => array('html'),
        'save-user-field' => array('json'),
        'save-user-groups' => array('json'),
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
            $user['admin_groups'] = $modelGroup->getUserGroupsAdmin($user);
            $users[$key] = $user;
        }
        $roles = Application_Model_Auth::getAllowedRoles();
        $groups = $modelGroup->getAllGroups();

        $this->view->assign(array(
            'groups' => $groups,
            'roles'  => $roles,
            'users'  => $users,
        ));
    }

    public function saveUserGroupsAction()
    {
        $status  = false;

        $userId = $this->_getParam('user');
        $groups = $this->_getParam('groups');
        foreach ($groups as $key => $group) {
            $group = explode(':', $group);
            $groups[$key] = array(
                'group' => (int)$group[0],
                'admin' => (bool)$group[1],
            );
        }
        $status = $this->_modelUser->saveGroups($userId, $groups);
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, 'Error!', array());
        }
    }

    public function saveUserFieldAction()
    {
        $status  = false;

        $userId = $this->_getParam('user');
        $field  = $this->_getParam('field');
        $value  = $this->_getParam('value');
        $errors = $this->_checkUserField($userId, $field, $value);
        if (empty($errors)) {
            if ($field == 'password') {
                // TODO: refactor change-password logic
                $status = $this->_modelUser->savePassword($userId, $value[0]);
            } else {
                $status = $this->_modelUser->saveField($userId, $field, $value);
            }
        }
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, 'Error!', $errors);
        }
    }

    protected function _checkUserField($userId, $field, $value)
    {
        // TODO: maybe this logic need move into model ?
        $errors = array();
        switch ($field) {
            case 'email':
                $validator = new Zend_Validate_EmailAddress();
                if ($validator->isValid($value)) {
                    $used = $this->_modelUser->getUserByEmail($value);
                    if ( ! $used) {
                        $checked = true;
                    } else {
                        $errors[] = 'This email already exists';
                    }
                } else {
                    $errors[] = 'Wrong email';
                }
                break;

            case 'password':
                // TODO: move this logic into validator
                if (is_array($value) && isset($value[0], $value[1])) {
                    $newPassword = trim($value[0]);
                    $newPasswordRepeat = trim($value[1]);
                    if ( ! empty($newPassword)) {
                        if ($newPassword === $newPasswordRepeat) {
                            // nothing. return empty array of errors
                        } else {
                            $errors[] = 'Strings is not same';
                        }
                    } else {
                        $errors[] = 'Empty password';
                    }
                } else {
                    $errors[] = 'Wrong data';
                }
                break;

            case 'birthday':
                $checked = false;
                try {
                    $date = new DateTime($value);
                    $timestamp = $date->getTimestamp();
                    if ($timestamp > -946772400 && $timestamp < 1262300400) { // 1940-01-01 --- 2010-01-01
                        $checked = true;
                    }
                } catch(Exception $e) { /* error */ }
                if ( ! $checked) {
                    $errors[] = 'Wrong birthday date';
                }
                break;

            default:
                // nothing. return empty array of errors
                break;

        }

        return $errors;
    }

}