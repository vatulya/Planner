<?php

class Planner_Form_EditPauseInterval extends Sch_Form
{

    public function init()
    {
        $this->addElements(array(
            new Zend_Form_Element_Hidden('id'),
            new Zend_Form_Element_Text('time_start_hour'),
            new Zend_Form_Element_Text('time_start_min'),
            new Zend_Form_Element_Text('time_end_hour'),
            new Zend_Form_Element_Text('time_end_min')
        ));
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
                'viewScript' => 'forms/edit-pause-interval.phtml',
                'viewModule' => 'planner'
            )),
            'Form'
        ));
        return parent::prepareDecorators();
    }

}