<?php
class My_Controller_Action extends Zend_Controller_Action
{

    protected $_navMenu = array(
        'checking', 'planning', 'requests', 'open-requests', 'group-settings', 'user-settings', 'overview', 'alert',
    );

    protected $_me = null;

    public function init()
    {
        $this->_helper->getHelper('AjaxContext')->initContext();
        $this->view->activeMenu = '';
        $activeMenu = $this->_getParam('controller', '');
        if (in_array($activeMenu, $this->_navMenu)) {
            $this->view->activeMenu = $activeMenu;
        }
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