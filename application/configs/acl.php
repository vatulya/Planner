<?php

$acl = new Zend_Acl();

// Roles

$guest      = new Zend_Acl_Role('GUEST');
$user       = new Zend_Acl_Role('USER');
$admin      = new Zend_Acl_Role('ADMIN');

$acl->addRole($guest);
$acl->addRole($user, 'GUEST');
$acl->addRole($admin);

$acl->addResource(new Zend_Acl_Resource('planner'));
$acl->addResource(new Zend_Acl_Resource('planner.auth'));
$acl->addResource(new Zend_Acl_Resource('planner.index'));

// Access rights

$acl->deny(null, null, null);

$acl->allow($guest, 'planner.auth');

$acl->allow($user, 'planner.index');

$acl->allow($admin, null);

return $acl;
