<?php

class Planner_Form_RequestDetails extends Sch_Form
{

    public function init()
    {
        $id = new Zend_Form_Element_Hidden('id');
        $this->addElement($id);

        $id = new Zend_Form_Element_Hidden('user_id');
        $this->addElement($id);

        $id = new Zend_Form_Element_Hidden('request_id');
        $this->addElement($id);

        $id = new Zend_Form_Element_('request_date');
        $this->addElement($id);

        $id = new Zend_Form_Element_Hidden('status');
        $this->addElement($id);

        $id = new Zend_Form_Element_Hidden('comment');
        $this->addElement($id);
    }

    public function prepareDecorators()
    {
        $this->setElementDecorators(array(
            'Label',
            'ViewHelper'
        ));
        $this->setDecorators(array(
            new Sch_Form_Decorator_Twitter_FormErrors(),
            new Zend_Form_Decorator_ViewScript(array(
                'viewScript' => 'forms/request-details.phtml',
                'viewModule' => 'planner'
            )),
            'Form'
        ));
        return parent::prepareDecorators();
    }

}

