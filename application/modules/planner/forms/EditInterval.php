<?php

class Planner_Form_EditInterval extends Sch_Form
{

    public function init()
    {
        $id = new Zend_Form_Element_Hidden('id');
        $statusDescription = new Zend_Form_Element_Text('description');
        $color = new Zend_Form_Element_Hidden('color_hex');

        $this->addElements(array($id, $statusDescription, $color));
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
                'viewScript' => 'forms/edit-interval.phtml',
                'viewModule' => 'planner'
            )),
            'Form'
        ));
        return parent::prepareDecorators();
    }

}