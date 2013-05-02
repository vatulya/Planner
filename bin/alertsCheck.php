<?php
defined('APPLICATION_PATH') || define('APPLICATION_PATH', dirname(realpath(__FILE__)) . '/../application/');
defined('ZEND_PATH') || define('LIBRARY_PATH', dirname(realpath(__FILE__)) . '/../library/');

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(LIBRARY_PATH),
    realpath(APPLICATION_PATH),
    get_include_path(),
)));

date_default_timezone_set('Europe/Amsterdam');

defined('APPLICATION_ENV') || define('APPLICATION_ENV',  'production');

/** Zend_Application */
require_once 'Zend/Application.php';

$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.yaml'
);

$application->bootstrap();



$modelPlanning = new Application_Model_Planning();
if (empty($argv[1])) {
    $currentDate = new My_DateTime();
    $date = $currentDate->format('Y-m-d');

    checkCurrentAlerts($date);
} else {
    $numPrevDays = $argv[1];
    echo "Start script for last : " . $numPrevDays . " days \n";
    for ($i = 0; $i < $numPrevDays ; $i++) {
        $currentDate = new My_DateTime();
        $currentDate->modify('- ' . $i .' day');
        $date = $currentDate->format('Y-m-d');
        $currentDate->modify('- ' . $i +1 . ' day');
        echo $date . "\n";
        checkCurrentAlerts($date);
    }
}

function checkCurrentAlerts($date) {
    $modelGroup    = new Application_Model_Group();
    $modelAlert    = new Application_Model_Alert();
    $modelHistory  = new Application_Model_History();
    $modelUser     = new Application_Model_User();
    $userPlan      = new Application_Model_Db_User_Planning();
    $groups = $modelGroup->getAllGroups();
    //$date = "2012-11-29";
    foreach ($groups as $key => $group) {
        $groupId = $group['id'];
        $groups[$key] = $group;
        $groups[$key]['users'] = $modelUser->getAllUsersByGroup($groupId);
        if (!empty($groups[$key]['users'])) {
            foreach ($groups[$key]['users'] as $keyUser => $user) {
                $modelAlert->checkAlertDayUserPlanByGroup($user['user_id'], $groupId, $date);
            }
        }
    }
}