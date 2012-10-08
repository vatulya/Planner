<?php

class Planner_GroupSettingsController extends My_Controller_Action
{

    public $ajaxable = array(
    );

    protected $_modelGroup;

    public function init()
    {
        parent::init();
        $this->_modelGroup = new Application_Model_Group();
    }

    public function indexAction()
    {
        $groups = $this->_modelGroup->getAllGroups();
        $this->view->groups = $groups;
    }

}