<?php

class Planner_Form_EditDay extends Sch_Form
{

    public function init()
    {
        $id         = new Zend_Form_Element_Hidden('id');
        $timeStart2 = new Zend_Form_Element_Text('time_start2');
        $timeEnd2   = new Zend_Form_Element_Text('time_end2');
        $status1    = new Zend_Form_Element_Hidden('status1');
        $status2    = new Zend_Form_Element_Hidden('status2');
        $useStatus2 = new Zend_Form_Element_Checkbox('use_status2');
        $timeStart  = new Zend_Form_Element_Text('time_start');
        $timeEnd    = new Zend_Form_Element_Text('time_end');

        $color = new Zend_Form_Element_Hidden('color');

        $this->addElements(array($id, $color, $status1, $status2, $useStatus2, $timeStart, $timeEnd, $timeStart2, $timeEnd2));
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
                'viewScript' => 'forms/edit-day.phtml',
                'viewModule' => 'planner'
            )),
            'Form'
        ));
        return parent::prepareDecorators();
    }

}

