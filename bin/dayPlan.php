<?php
defined('APPLICATION_PATH') || define('APPLICATION_PATH', dirname(realpath(__FILE__)) . '/../application/');
defined('ZEND_PATH') || define('LIBRARY_PATH', dirname(realpath(__FILE__)) . '/../library/');

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(LIBRARY_PATH),
    realpath(APPLICATION_PATH),
    get_include_path(),
)));

defined('APPLICATION_ENV') || define('APPLICATION_ENV', (isset($argv[1]) ? $argv[1] : 'production'));

/** Zend_Application */
require_once 'Zend/Application.php';

$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.yaml'
);

$application->bootstrap();

$modelGroup    = new Application_Model_Group();
$modelUser     = new Application_Model_User();
$userPlan      = new Application_Model_Db_User_Planning();

$groups = $modelGroup->getAllGroups();

$currentDate = new My_DateTime();
$date = $currentDate->format('Y-m-d');

foreach ($groups as $key => $group) {
    $groupId = $group['id'];
    $groups[$key] = $group;
    $groups[$key]['users'] = $modelUser->getAllUsersByGroup($groupId);
    if (!empty($groups[$key]['users'])) {
        foreach ($groups[$key]['users'] as $keyUser => $user) {
            $modelUser->createNewDayUserPlanByGroup($user['user_id'], $groupId, $date);
        }
    }

  //  var_dump($groups[$key]['users']);
}






//var_dump($modelGroup->getAllGroups());
