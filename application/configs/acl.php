<?php

$acl = new Zend_Acl();

// Roles

$guest      = new Zend_Acl_Role('GUEST');
$user       = new Zend_Acl_Role('USER', $guest);
$groupAdmin = new Zend_Acl_Role('GROUP_ADMIN', $user);
$admin      = new Zend_Acl_Role('ADMIN', $groupAdmin);
$superAdmin = new Zend_Acl_Role('SUPER_ADMIN', $admin);

$acl->addRole($guest);
$acl->addRole($user);
$acl->addRole($groupAdmin);
$acl->addRole($admin);
$acl->addRole($superAdmin);

$acl->addResource(new Zend_Acl_Resource('planner'));
$acl->addResource(new Zend_Acl_Resource('planner.auth'));
$acl->addResource(new Zend_Acl_Resource('planner.checking'));
$acl->addResource(new Zend_Acl_Resource('planner.planning'));
$acl->addResource(new Zend_Acl_Resource('planner.requests'));
$acl->addResource(new Zend_Acl_Resource('planner.open-requests'));
$acl->addResource(new Zend_Acl_Resource('planner.group-settings'));
$acl->addResource(new Zend_Acl_Resource('planner.user-settings'));
$acl->addResource(new Zend_Acl_Resource('planner.overview'));
$acl->addResource(new Zend_Acl_Resource('planner.alert'));

// Access rights

$acl->deny(null, null, null);

//$acl->allow($guest, 'planner');
$acl->allow($guest, 'planner.auth');

$acl->allow($user, 'planner.checking');
$acl->allow($user, 'planner.planning');
$acl->allow($user, 'planner.requests');

$acl->allow($groupAdmin, 'planner.open-requests');

$acl->allow($admin, 'planner.group-settings');
$acl->allow($admin, 'planner.user-settings');
$acl->allow($admin, 'planner.overview');
$acl->allow($admin, 'planner.alert');

$acl->allow($superAdmin, null);

return $acl;
