<?php

class Planner_CheckingController extends My_Controller_Action
{

    public $ajaxable = array(
        'index'                     => array('html'),
        'user-check'                => array('json'),
        'get-user-checking-history' => array('html'),
    );

    /**
     * @var Application_Model_User
     */
    protected $_modelUser;

    /**
     * @var Application_Model_Group
     */
    protected $_modelGroup;

    public function init()
    {
        $this->view->me = $this->_me = $this->_helper->CurrentUser();
        $this->_modelUser  = new Application_Model_User();
        $this->_modelGroup = new Application_Model_Group();
        $group = $this->_getParam('group');
        $allowed = false;
        if ($this->_me['role'] >= Application_Model_Auth::ROLE_ADMIN) {
            $allowed = true;
        } elseif ($group && in_array($group, $this->_me['admin_groups'])) {
            $allowed = true;
        }
        if ( ! $allowed) {
            $this->_setParam('user', $this->_me['id']);
        }
        parent::init();
    }

    public function indexAction()
    {
        $date = new My_DateTime();
        $users = $this->_modelUser->getAllUsers();
        $groups = $this->_modelGroup->getAllGroups();
        foreach ($groups as $key => $group) {
            if ( ! $this->_modelGroup->checkIsWorkDay($group['id'])) {
                unset($groups[$key]);
                continue; // get groups what must work today only
            }
            $groupUsers = $this->_modelUser->getAllUsersByGroup($group['id'], $date);
            $isAdmin = false;
            if ($this->_me['role'] >= Application_Model_Auth::ROLE_ADMIN) {
                $isAdmin = true;
            } else {
                if (in_array($group['id'], $this->_me['admin_groups'])) {
                    $isAdmin = true;
                }
            }
            $groups[$key]['is_admin'] = $isAdmin;
            $groups[$key]['users'] = $groupUsers;
        }
        $modelUserCheck = new Application_Model_Db_User_Checks();
        $lastCheck = $modelUserCheck->getUserLastCheck($this->_me['id']);
        $assign = array(
            'users'     => $users,
            'groups'    => $groups,
            'date'      => $date->format('d.m.Y'),
            'today'     => $date->format('Y-m-d'),
            'lastCheck' => $lastCheck,
        );
        $this->view->assign($assign);
    }

    public function userCheckAction()
    {
        $message = 'Error! Something wrong.';
        try {
            $user = $this->_modelUser->userCheck($this->_getParam('user'), $check = $this->_getParam('check'));
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        if ( ! empty($user)) {
            $this->_response(1, '', $user);
        } else {
            $this->_response(0, $message, array());
        }
    }

    public function getUserCheckingHistoryAction()
    {
        $userId = $this->_getParam('user');
        $date   = $this->_getParam('date');
        if (empty($date)) {
            $date = new My_DateTime();
        } else {
            $date = My_DateTime::factory($date);
        }
        if ( ! $date) {
            throw new Exception('Error! Wrong date ID.');
        }
        $user = $this->_modelUser->getUserById($userId);
        if ( ! $user) {
            throw new Exception('Error! Wrong user ID.');
        }
        $groups = $this->_modelGroup->getGroupsByUserId($user['id']);
        $admin = $this->_me['admin_groups'];
        $checkGroup = array_filter($groups, function ($group) use ($admin) {
            if (in_array($group['id'], $admin)) {
                return true;
            }
            return false;
        });
        if ($this->_me['id'] == $user['id'] // this is me
            || $this->_me['role'] >= Application_Model_Auth::ROLE_ADMIN // i am admin
            || ! empty($checkGroup)) // i am group admin
        {
            // TODO: maybe here need catch Exception?
            $userCheckins = $this->_modelUser->getUserCheckings($userId, $date);
        }
        if ($this->_me['role'] >= Application_Model_Auth::ROLE_ADMIN // i am admin
            || ! empty($checkGroup)) // i am group admin
        {
            $this->_isAdmin = true;
        } else {
            $this->_isAdmin = false;
        }

        $this->view->date     = $date->format('Y-m-d');
        $this->view->checkins = $userCheckins;
    }

}