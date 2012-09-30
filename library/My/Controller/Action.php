<?php
class My_Controller_Action extends  Zend_Controller_Action
{

    public function init()
    {
        $this->_helper->getHelper('AjaxContext')->initContext();
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