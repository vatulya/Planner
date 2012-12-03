<?php

class Planner_GroupSettingsController extends My_Controller_Action
{

    public $ajaxable = array(
        'index'                              => array('html'),
        'get-edit-group-form'                => array('html'),
        'get-group-planning'                 => array('html'),
        'save-group-form'                    => array('json'),
        'delete-group'                       => array('json'),
        'save-group-planning'                => array('json'),
        'save-group-setting-max-free-people' => array('json'),
        'save-group-exceptions'              => array('json'),
        'save-group-holidays'                => array('json'),
        'delete-group-holidays'              => array('json'),
        'save-alert-over-limit'              => array('json'),
    );

    /**
     * @var Application_Model_Group
     */
    protected $_modelGroup;

    public function init()
    {
        $this->view->me = $this->_me = $this->_helper->CurrentUser();
        $this->_modelGroup = new Application_Model_Group();
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
        $generalHolidays = $this->_modelGroup->getGeneralHolidays();
        $assign = array(
            'generalGroup'    => $generalGroup,
            'groups'          => $groups,
            'generalHolidays' => $generalHolidays,
        );
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
            $this->_response(0, 'Error!', array());
        }
    }

    public function getGroupPlanningAction()
    {
        $groupId = $this->_getParam('group');
        if ($groupId && ($group = $this->_modelGroup->getGroupById($groupId))) {
            $planning = $this->_modelGroup->getGroupPlanning($groupId);
            $planningTmp = array();
            foreach ($planning as $row) {
                $key = $row['week_type'] . '-' . $row['day_number'];
                $planningTmp[$key] = $row;
            }
            $planning = $planningTmp;
            $this->view->weekDays = self::getWeekDays();
            $this->view->group = $group;
            $this->view->groupPlanning = $planning;
            $this->view->groupSettings = $this->_modelGroup->getGroupSettings($groupId);
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
        $status = $this->_modelGroup->saveGroupPlanning($this->_getParam('group'), $this->_getParam('group_planning', array()), $this->_getParam('group_pause', array()));
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, 'Error!', array());
        }
    }

    public function saveGroupSettingMaxFreePeopleAction()
    {
        $status = $this->_modelGroup->saveGroupSetting($this->_getParam('group'), 'max_free_people', $this->_getParam('max_free_people', 0));
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, 'Error!', array());
        }
    }

    public function saveGroupExceptionsAction()
    {
        $groupId          = $this->_getParam('group', 0);
        $selectedDates    = $this->_getParam('selected_dates', array());
        $maxFreePeople    = $this->_getParam('max_free_people');
        $editDates        = $this->_getParam('edit_dates', array());
        $status = $this->_modelGroup->saveGroupExceptions($groupId, $editDates, $selectedDates, $maxFreePeople);
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, 'Error!', array());
        }
    }

    public function saveGroupHolidaysAction()
    {
        $groupId          = $this->_getParam('group', 0);
        $selectedDate     = $this->_getParam('selected_dates', array());
        $holidayName      = $this->_getParam('holiday_name', '');
        $status = $this->_modelGroup->saveGroupHoliday($groupId, $selectedDate, $holidayName);
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, 'Error!', array());
        }
    }

    public function deleteGroupHolidaysAction()
    {
        $holidayId = $this->_getParam('holiday', 0);
        $status = false;
        if ($holidayId > 0) {
            $status = $this->_modelGroup->deleteGroupHolidayById($holidayId);
        }
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, 'Error!', array());
        }
    }

    public function saveAlertOverLimitAction()
    {
        $limit = $this->_getParam('alert_over_limit_free_people', 0);
        $status = $this->_modelGroup->saveGroupSetting(0, 'alert_over_limit_free_people', $limit);
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, 'Error!', array());
        }
    }

}