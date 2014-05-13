<?php

class Planner_GroupSettingsController extends My_Controller_Action
{

    public $ajaxable = array(
        'index'                              => array('html'),
        'get-edit-group-form'                => array('html'),
        'get-edit-status-form'               => array('html'),
        'get-edit-interval-form'             => array('html'),
        'get-group-planning'                 => array('html'),
        'save-group-form'                    => array('json'),
        'save-status-form'                   => array('json'),
        'save-interval-form'                 => array('json'),
        'delete-group'                       => array('json'),
        'save-group-planning'                => array('json'),
        'save-user-planning'                 => array('json'),
        'save-group-setting-max-free-people' => array('json'),
        'save-group-exceptions'              => array('json'),
        'save-group-holidays'                => array('json'),
        'delete-group-holidays'              => array('json'),
        'save-alert-over-limit'              => array('json'),
        'save-default-total-free-hours'      => array('json'),
    );

    /**
     * @var Application_Model_Group
     */
    protected $_modelGroup;

    protected $_modelStatus;

    protected $_modelIntervals;

    public function init()
    {
        $this->view->me = $this->_me = $this->_helper->CurrentUser();
        $this->_modelGroup = new Application_Model_Group();
        $this->_modelStatus = new Application_Model_Status();
        parent::init();
    }

    public function indexAction()
    {
        $year = date('Y');
        $generalGroup = $this->_modelGroup->getGeneralGroup();
        $generalGroup['settings'] = $this->_modelGroup->getGeneralGroupSettings();
        $generalGroup['exceptions'] = $this->_modelGroup->getGeneralExceptions();
        $generalGroup['grouped_exceptions'] = $this->_modelGroup->groupExceptions($generalGroup['exceptions'], $year);
        $groups = $this->_modelGroup->getAllGroups();
        foreach ($groups as $key => $group) {
            $group['settings'] = $this->_modelGroup->getGroupSettings($group['id']);
            $group['exceptions'] = $this->_modelGroup->getExceptions($group['id']);
            $group['grouped_exceptions'] = $this->_modelGroup->groupExceptions($group['exceptions'], $year);
            $groups[$key] = $group;
        }
        $generalHolidays      = $this->_modelGroup->getGeneralHolidays();
        $modelParameters      = new Application_Model_Parameter();
        $defaultTotalFreeHours = $modelParameters->getDefaultTotalFreeHours();
        $modelWorkIntervals = new Application_Model_WorkIntervals();
        $workIntervals = $modelWorkIntervals->getWorkIntervals();
        $assign = array(
            'generalGroup'          => $generalGroup,
            'groups'                => $groups,
            'generalHolidays'       => $generalHolidays,
            'defaultTotalFreeHours' => $defaultTotalFreeHours,
            'workIntervals'         => $workIntervals,
        );
        $this->view->statuses = $this->_modelStatus->getAllstatus();
        $this->view->assign($assign);
    }

    public function getEditGroupFormAction()
    {
        $groupId = $this->_getParam('group');
        $editForm = new Planner_Form_EditGroup(array(
            'class' => 'edit-group-form',
            'action' => $this->_helper->url->url(array('controller' => 'group-settings', 'action' => 'save-group-form'), 'planner', true),
            'id' => 'form-edit-group',
        ));
        if ($groupId) {
            $group = $this->_modelGroup->getGroupById($groupId);
            if ($group) {
                $editForm->populate($group);
            } else {
                return false;
            }
            $this->view->group = $group;
        }
        $this->_helper->layout->disableLayout();
        $this->view->editForm = $editForm->prepareDecorators();
    }

    public function getEditStatusFormAction()
    {
        $statusId = $this->_getParam('status_id');
        $editForm = new Planner_Form_EditStatus(array(
            'class' => 'edit-status-form',
            'action' => $this->_helper->url->url(array('controller' => 'group-settings', 'action' => 'save-status-form'), 'planner', true),
            'id' => 'form-edit-status',
        ));
        if ($statusId) {
            $status = $this->_modelStatus->getDataById($statusId);
            if ($status) {
                if (!empty($status['color_hex'])) {
                    $status['color'] = $status['color_hex'];
                }
                $editForm->populate($status);
            } else {
                return false;
            }
            $this->view->status = $status;
        }
        $this->_helper->layout->disableLayout();
        $this->view->editForm = $editForm->prepareDecorators();
    }

    public function getEditIntervalFormAction()
    {
        $intervalId = $this->_getParam('interval_id');
        $editForm = new Planner_Form_EditInterval(array(
            'class' => 'edit-interval-form',
            'action' => $this->_helper->url->url(array('controller' => 'group-settings', 'action' => 'save-interval-form'), 'planner', true),
            'id' => 'form-interval-status',
        ));
        if ($intervalId) {
            $modelWorkIntervals = new Application_Model_WorkIntervals();
            $workInterval = $modelWorkIntervals->getWorkInterval($intervalId);
            if ($workInterval) {
                $editForm->populate($workInterval);
            } else {
                return false;
            }
            $this->view->interval = $workInterval;
        }
        $this->_helper->layout->disableLayout();
        $this->view->editForm = $editForm->prepareDecorators();
    }

    public function saveStatusFormAction()
    {
        $editForm = new Planner_Form_EditStatus();
        /** @var $request Zend_Controller_Request_Http */
        $request = $this->getRequest();
        $data = array();
        $status = false;
        if ($request->isPost()) {
            if ($editForm->isValid($request->getPost())) {
                $data = $this->_modelStatus->saveStatus($editForm->getValues());
                $status = true;
            } else {
                $data = $editForm->getErrors();
            }
        }
        if ($status) {
            $this->_response(1, '', $data);
        } else {
            $this->_response(0, 'Error!', $data);
        }
    }


    public function saveIntervalFormAction()
    {
        $editForm = new Planner_Form_EditInterval();
        /** @var $request Zend_Controller_Request_Http */
        $request = $this->getRequest();
        $data = array();
        $status = false;
        if ($request->isPost()) {
            if ($editForm->isValid($request->getPost())) {
                $modelWorkIntervals = new Application_Model_WorkIntervals();
                $data = $modelWorkIntervals->saveWorkInterval($editForm->getValues());
                $status = true;
            } else {
                $data = $editForm->getErrors();
            }
        }
        if ($status) {
            $this->_response(1, '', $data);
        } else {
            $this->_response(0, 'Error!', $data);
        }
    }

    public function saveGroupFormAction()
    {
        $editForm = new Planner_Form_EditGroup();
        /** @var $request Zend_Controller_Request_Http */
        $request = $this->getRequest();
        $data = array();
        $status = false;
        if ($request->isPost()) {
            if ($editForm->isValid($request->getPost())) {
                $data = $this->_modelGroup->saveGroup($editForm->getValues());
                $status = true;
            } else {
                $data = $editForm->getErrors();
            }
        }
        if ($status) {
            $this->_response(1, '', $data);
        } else {
            $this->_response(0, 'Error!', $data);
        }
    }

    public function deleteGroupAction()
    {
        $status = $this->_modelGroup->deleteGroup($this->_getParam('group'));
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, 'Error! You don\'t have permissions.', array());
        }
    }

    public function getGroupPlanningAction()
    {
        $groupId = $this->_getParam('group');
        if ($groupId && ($group = $this->_modelGroup->getGroupById($groupId))) {
            $groupId = $group['id'];
//            $planning = $this->_modelGroup->getGroupPlanning($groupId);
//            $planningTmp = array();
//            foreach ($planning as $row) {
//                $key = $row['week_type'] . '-' . $row['day_number'];
//                $planningTmp[$key] = $row;
//            }
//            $planning = $planningTmp;

            $modelUser = new Application_Model_User();
            $usersPlanning = array();
            $users = $modelUser->getAllUsersByGroup($groupId);
            foreach ($users as $user) {
                $userPlanning = $this->_modelGroup->getSpecialUserPlanning($groupId, $user['id']);
                $planningTmp = array();
                foreach ($userPlanning as $row) {
                    $key = $row['week_type'] . '-' . $row['day_number'];
                    $planningTmp[$key] = $row;
                }
                $usersPlanning[$user['id']] = $planningTmp;
            }

            $assign = array(
                'weekDays'      => self::getWeekDays(),
                'group'         => $group,
                'users'         => $users,
                'usersPlanning' => $usersPlanning,
                //'groupPlanning' => $planning,
                'groupSettings' => $this->_modelGroup->getGroupSettings($groupId),
            );
            $this->view->assign($assign);
        }
        $this->_helper->layout->disableLayout();
    }

    static public function getWeekDays($weekType = null)
    {
        $getWeekDays = function($weekType) {
            $week = array();
            $dayNumber = 0;
            while (++$dayNumber <= 7) {
                $day = array(
                    'week_type'  => $weekType,
                    'day_number' => $dayNumber,
                    'time_start' => '',
                    'time_end'   => '',
                );
                $key = $weekType . '-' . $dayNumber;
                $week[$key] = $day;
            }
            return $week;
        };
        $return = array();
        if ($weekType == Application_Model_Db_Group_Plannings::WEEK_TYPE_ODD || $weekType == Application_Model_Db_Group_Plannings::WEEK_TYPE_EVEN) {
            $return = $getWeekDays($weekType);
        } else {
            $return[Application_Model_Db_Group_Plannings::WEEK_TYPE_ODD] = $getWeekDays(Application_Model_Db_Group_Plannings::WEEK_TYPE_ODD);
            $return[Application_Model_Db_Group_Plannings::WEEK_TYPE_EVEN] = $getWeekDays(Application_Model_Db_Group_Plannings::WEEK_TYPE_EVEN);
        }
        return $return;
    }

    public function saveGroupPlanningAction()
    {
        $message = 'Error! Something wrong.';
        try {
            $status = $this->_modelGroup->saveGroupPlanning($this->_getParam('group'), $this->_getParam('group_planning', array()));
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, $message, array());
        }
    }

    public function saveUserPlanningAction()
    {
        $message = 'Error! Something wrong.';
        try {
           $status = $this->_modelGroup->saveUserPlanning($this->_getParam('group'), $this->_getParam('user'), $this->_getParam('user_planning', array()));
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, $message, array());
        }
    }

    public function saveGroupSettingMaxFreePeopleAction()
    {
        $message = 'Error! Something wrong.';
        try {
            $status = $this->_modelGroup->saveGroupSetting($this->_getParam('group'), 'max_free_people', $this->_getParam('max_free_people', 0));
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, $message, array());
        }
    }

    public function saveGroupExceptionsAction()
    {
        $groupId          = $this->_getParam('group', 0);
        $selectedDates    = $this->_getParam('selected_dates', array());
        $maxFreePeople    = $this->_getParam('max_free_people');
        $editDates        = $this->_getParam('edit_dates', array());
        $message = 'Error! Something wrong.';
        try {
            $status = $this->_modelGroup->saveGroupExceptions($groupId, $editDates, $selectedDates, $maxFreePeople);
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, $message, array());
        }
    }

    public function saveGroupHolidaysAction()
    {
        $groupId          = $this->_getParam('group', 0);
        $selectedDate     = $this->_getParam('selected_dates', array());
        $holidayName      = $this->_getParam('holiday_name', '');
        $message = 'Error! Something wrong.';
        try {
            $status = $this->_modelGroup->saveGroupHoliday($groupId, $selectedDate, $holidayName);
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, $message, array());
        }
    }

    public function deleteGroupHolidaysAction()
    {
        $holidayId = $this->_getParam('holiday', 0);
        $status = false;
        $message = 'Error! Something wrong.';
        try {
            if ($holidayId > 0) {
                $status = $this->_modelGroup->deleteGroupHolidayById($holidayId);
            } else {
                $message = 'Error! Wrong holiday ID.';
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, $message, array());
        }
    }

    public function saveAlertOverLimitAction()
    {
        $message = 'Error! Something wrong.';
        $limit = $this->_getParam('alert_over_limit_free_people', 0);
        try {
            $status = $this->_modelGroup->saveGroupSetting(0, 'alert_over_limit_free_people', $limit);
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, $message, array());
        }
    }

    public function saveDefaultTotalFreeHoursAction()
    {
        $message = 'Error! Something wrong.';
        $value = $this->_getParam('default_total_free_hours', 216);
        $modelParameter = new Application_Model_Parameter();
        try {
            $status = $modelParameter->setDefaultTotalFreeHours($value);
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, $message, array());
        }
    }

}