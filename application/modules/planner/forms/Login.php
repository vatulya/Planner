<?php

class Management_Form_Login extends Sch_Form
{

    public function init()
    {
        $email = new Zend_Form_Element_Text('email');
        $email->addValidator(new Zend_Validate_EmailAddress());
        $email->setRequired(true);

        $password = new Zend_Form_Element_Password('password');
        $password->addValidator(new Zend_Validate_StringLength(array('min' => 6)));
        $password->setRequired(true);

        $this->addElements(array($email, $password));
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
                'viewScript' => 'forms/login.phtml',
                'viewModule' => 'planner'
            )),
            'Form'
        ));
        return parent::prepareDecorators();
    }

}

