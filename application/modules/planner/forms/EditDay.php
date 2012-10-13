<?php

class Planner_Form_EditDay extends Sch_Form
{

    public function init()
    {
        $id = new Zend_Form_Element_Hidden('id');

        $groupName = new Zend_Form_Element_Text('group_name');
        $groupName->setRequired(true);

        $color = new Zend_Form_Element_Hidden('color');

        $this->addElements(array($id, $groupName, $color));
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

