<?php

class Planner_Form_ChangePassword extends Sch_Form
{

    public function init()
    {
        $userId = new Zend_Form_Element_Hidden('userId');
        $userId->setRequired(true);
        $this->addElement($userId);

        $passwordOld = new Zend_Form_Element_Password('password_old');
        $passwordOld->addValidator(new Zend_Validate_StringLength(array('min' => 6)));
        $passwordOld->setRequired(true);
        $this->addElement($passwordOld);

        $password = new Zend_Form_Element_Password('password');
        $password->addValidator(new Zend_Validate_StringLength(array('min' => 6)));
        $password->setRequired(true);
        $this->addElement($password);

        $passwordRepeat = new Zend_Form_Element_Password('password_repeat');
        $passwordRepeat->addValidator(new Zend_Validate_StringLength(array('min' => 6)));
        $passwordRepeat->addValidator(new Zend_Validate_Identical(array('token' => 'password')));
        $this->addElement($passwordRepeat);
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
                'viewScript' => 'forms/change_password.phtml',
                'viewModule' => 'planner'
            )),
            'Form'
        ));
        return parent::prepareDecorators();
    }

}

