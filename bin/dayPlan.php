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

$bootstrap = $application->getBootstrap();
if ($bootstrap->hasResource('Log')) {
    $log = $bootstrap->getResource('Log');
    Zend_Registry::set('Zend_Log', $log);
}

$modelPlanning = new Application_Model_Planning();
if (empty($argv[1])) {
    $currentDate = new My_DateTime();
    $date = $currentDate->format('Y-m-d');
    $currentDate->modify('-1 day');
    $prevDate = $currentDate->format('Y-m-d');
    //Check start history fill once
    if (!$modelPlanning->checkExistDay($date)) {
        echo 'This day : ' . $date . " - already in history\n";
        exit();
    }
    calculateDayHours($date,$prevDate);
} else {
    $numPrevDays = $argv[1];
    echo "Start script for last : " . $numPrevDays . " days \n";
    for ($i = 0; $i < $numPrevDays ; $i++) {
        $currentDate = new My_DateTime();
        $currentDate->modify('- ' . $i .' day');
        $date = $currentDate->format('Y-m-d');
        $currentDate->modify('- ' . $i +1 . ' day');
        $prevDate = $currentDate->format('Y-m-d');
        if (!$modelPlanning->checkExistDay($date)) {
            echo 'This day : ' . $date . " - already in history\n";
            continue;
        }
        calculateDayHours($date,$prevDate);
    }
}



function calculateDayHours($date,$prevDate) {
    $modelGroup    = new Application_Model_Group();
    $modelPlanning = new Application_Model_Planning();
    $modelHistory  = new Application_Model_History();
    $modelUser     = new Application_Model_User();
    $userPlan      = new Application_Model_Db_User_Planning();
    $groups = $modelGroup->getAllGroups();
    //$date = "2012-11-29";
    foreach ($groups as $key => $group) {
        $groupId = $group['id'];
        $groups[$key] = $group;
        //TODO need replace on users from history
        $groups[$key]['users'] = $modelUser->getAllUsersByGroup($groupId);
        if (!empty($groups[$key]['users'])) {
            foreach ($groups[$key]['users'] as $keyUser => $user) {
                $modelPlanning->createNewDayUserPlanByGroup($user['user_id'], $groupId, $date);
                $modelHistory->addDayDataToHistory($user['user_id'], $groupId, $prevDate);
            }
        }
    }
    $modelUser->addTotalFreeHoursForDayToAllUsers();
}
