<?php

class Planner_GroupSettingsController extends My_Controller_Action
{

    public $ajaxable = array(
        'index'               => array('html'),
        'get-edit-group-form' => array('html'),
        'get-group-planning'  => array('html'),
        'save-group-form'     => array('json'),
        'delete-group'        => array('json'),
        'save-group-planning' => array('json'),
    );

    protected $_modelGroup;

    public function init()
    {
        $this->view->me = $this->_me = $this->_helper->CurrentUser();
        $this->_modelGroup = new Application_Model_Group();
        parent::init();
    }

    public function indexAction()
    {
        $groups = $this->_modelGroup->getAllGroups();
        $this->view->groups = $groups;
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
        }
        $this->_helper->layout->disableLayout();
    }

    static public function getWeekDays($weekType = null)
    {
        $getWeekDays = function($weekType) {
            $week = array();
            $dayNumber = 0;
            while (++$dayNumber <= 6) {
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
        $status = $this->_modelGroup->saveGroupPlanning($this->_getParam('group'), $this->_getParam('group_planning'));
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, 'Error!', array());
        }
    }

}