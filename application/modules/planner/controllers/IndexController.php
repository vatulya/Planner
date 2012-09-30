<?php

class Management_IndexController extends My_Controller_Action
{

    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $this->_forward('index', 'checking');
    }

}