<?php

class Planner_GroupSettingsController extends My_Controller_Action
{

    public $ajaxable = array(
        'index'               => array('html'),
        'get-edit-group-form' => array('html'),
        'get-group-planning'  => array('html'),
        'save-group-form'     => array('json'),
        'delete-group'        => array('json'),
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
//            $planning = $this->_modelGroup->getGroupPlanning($groupId);
            $planning = array(
                'odd' => array(),
                'even' => array(),
            );
            $this->view->group = $group;
            $this->view->groupPlanning = $planning;
        }
        $this->_helper->layout->disableLayout();
    }

}