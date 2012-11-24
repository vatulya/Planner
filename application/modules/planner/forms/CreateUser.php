<?php

class Planner_Form_CreateUser extends Sch_Form
{

    public function init()
    {
        $email = new Zend_Form_Element_Text('email');
        $email->setRequired(true);
        $email->addValidator(new Zend_Validate_EmailAddress());
        $this->addElement($email);

        $password = new Zend_Form_Element_Password('password');
        $password->addValidator(new Zend_Validate_StringLength(array('min' => 6)));
        $password->setRequired(true);
        $this->addElement($password);

        $fullName = new Zend_Form_Element_Text('full_name');
        $this->addElement($fullName);

        $address = new Zend_Form_Element_Text('address');
        $this->addElement($address);

        $phone = new Zend_Form_Element_Text('phone');
        $this->addElement($phone);

        $emergencyPhone = new Zend_Form_Element_Text('emergency_phone');
        $this->addElement($emergencyPhone);

        $emergencyFullName = new Zend_Form_Element_Text('emergency_full_name');
        $this->addElement($emergencyFullName);

        $birthday = new Zend_Form_Element_Text('birthday');
        $this->addElement($birthday);

        $owner = new Zend_Form_Element_Text('owner');
        $this->addElement($owner);
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
                'viewScript' => 'forms/create-user.phtml',
                'viewModule' => 'planner'
            )),
            'Form'
        ));
        return parent::prepareDecorators();
    }

}

