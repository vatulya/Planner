<?php

class Planner_IndexController extends My_Controller_Action
{

    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $this->_forward('checking', null, null, array('action' => 'checking'));
    }

    public function checkingAction()
    {
        $a = 1;
//        $this->view->activeMenu = 'checking';
    }

}