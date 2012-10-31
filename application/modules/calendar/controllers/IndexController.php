<?php

class Calendar_IndexController extends Zend_Controller_Action
{

    public $ajaxable = array(
        'index' => array('html'),
    );

    public function init()
    {
        $this->_helper->getHelper('AjaxContext')->initContext();
        $this->_helper->layout()->disableLayout();
    }

    public function indexAction()
    {
        $showDate = $this->_getParam('show_date');
        try {$showDate = new DateTime($showDate);} catch (Exception $e) {$showDate = new DateTime();}
        $this->view->showDate = $showDate;
    }

    protected function _response($status = 0, $message = '', $data = array())
    {
        $response = array(
            'status'  => (int)$status,
            'message' => (string)$message,
            'data'    => (array)$data,
        );
        $this->view->response = $response;
    }

}