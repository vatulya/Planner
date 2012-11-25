<?php

class Planner_Form_EditDay extends Sch_Form
{

    public function init()
    {
        $id          = new Zend_Form_Element_Hidden('id');
        $userId      = new Zend_Form_Element_Hidden('user_id');
        $groupId     = new Zend_Form_Element_Hidden('group_id');
        $date        = new Zend_Form_Element_Hidden('date');
        $status1     = new Zend_Form_Element_Hidden('status1');
        $status2     = new Zend_Form_Element_Hidden('status2');
        $timeStart   = new Zend_Form_Element_Hidden('time_start');
        $timeEnd     = new Zend_Form_Element_Hidden('time_end');
        $timeStart2  = new Zend_Form_Element_Text('time_start2');
        $timeEnd2    = new Zend_Form_Element_Text('time_end2');

        $this->addElements(array($id, $userId, $groupId, $status1, $date, $status2, $timeStart, $timeEnd, $timeStart2, $timeEnd2));
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

