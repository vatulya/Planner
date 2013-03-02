<?php

class Planner_Form_EditStatus extends Sch_Form
{

    public function init()
    {
        $id = new Zend_Form_Element_Hidden('id');
        $statusDescription = new Zend_Form_Element_Text('description');
        $statusDescription->setRequired(true);
        $statusDescriptionLong = new Zend_Form_Element_Textarea('long_description');
        $holiday = new Zend_Form_Element_Checkbox('is_holiday');
        $color = new Zend_Form_Element_Hidden('color');

        $this->addElements(array($id, $statusDescription, $statusDescriptionLong, $color, $holiday));
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
                'viewScript' => 'forms/edit-status.phtml',
                'viewModule' => 'planner'
            )),
            'Form'
        ));
        return parent::prepareDecorators();
    }

}