<?php

class Planner_GroupSettingsController extends My_Controller_Action
{

    public $ajaxable = array(
        'index'               => 'html',
        'get-edit-group-form' => 'html',
        'save-group-form'     => 'json',
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
        }
        $this->_helper->layout->disableLayout();
        $this->view->group = $group;
        $this->view->editForm = $editForm->prepareDecorators();
    }

    public function saveGroupFormAction()
    {
        $editForm = new Planner_Form_EditGroup();
        /** @var $request Zend_Controller_Request_Http */
        $request = $this->getRequest();
        $result = false;
        if ($request->isPost()) {
            if ($editForm->isValid($request->getPost())) {
                $result = $this->_modelGroup->saveGroup($editForm->getValues());
            } else {
                $result = $editForm->getErrorMessages();
            }
        }
        if ($result) {
            $this->_response(1, '', $result);
        } else {
            $this->_response(0, 'Error!', array());
        }
    }

}