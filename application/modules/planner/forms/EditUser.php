<?php

class Planner_Form_EditUser extends Sch_Form
{

    public function init()
    {
        $userId = new Zend_Form_Element_Hidden('userId');
        $userId->setRequired(true);
        $this->addElement($userId);

        $firstName = new Zend_Form_Element_Text('first_name');
        $firstName->setRequired();
        $this->addElement($firstName);

        $lastName = new Zend_Form_Element_Text('last_name');
        $lastName->setRequired();
        $this->addElement($lastName);
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
                'viewScript' => 'forms/edit_user.phtml',
                'viewModule' => 'planner'
            )),
            'Form'
        ));
        return parent::prepareDecorators();
    }

}

