<?php

class Planner_Form_Register extends Sch_Form
{

    public function init()
    {
        $email = new Zend_Form_Element_Text('email');
        $email->addValidator(new Zend_Validate_EmailAddress());
        $email->setRequired(true);
        $this->addElement($email);

        $password = new Zend_Form_Element_Password('password');
        $password->addValidator(new Zend_Validate_StringLength(array('min' => 6)));
        $password->setRequired(true);
        $this->addElement($password);

        $passwordRepeat = new Zend_Form_Element_Password('password_repeat');
        $passwordRepeat->addValidator(new Zend_Validate_StringLength(array('min' => 6)));
        $passwordRepeat->addValidator(new Zend_Validate_Identical(array('token' => 'password')));
        $this->addElement($passwordRepeat);

        $firstName = new Zend_Form_Element_Text('first_name');
        $firstName->setRequired();
        $this->addElement($firstName);

        $lastName = new Zend_Form_Element_Text('last_name');
        $lastName->setRequired();
        $this->addElement($lastName);

        $role = new Zend_Form_Element_Radio('role');
        $role->setRequired();
        $role->setMultiOptions(array(
            Application_Model_Auth::ROLE_USER  => 'User',
            Application_Model_Auth::ROLE_ADMIN => 'Admin',
        ));
        $this->addElement($role);
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
                'viewScript' => 'forms/register.phtml',
                'viewModule' => 'planner'
            )),
            'Form'
        ));
        return parent::prepareDecorators();
    }

}

