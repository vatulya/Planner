<?php
defined('APPLICATION_PATH') || define('APPLICATION_PATH', dirname(realpath(__FILE__)) . '/../application/');
defined('ZEND_PATH') || define('LIBRARY_PATH', dirname(realpath(__FILE__)) . '/../library/');

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(LIBRARY_PATH),
    realpath(APPLICATION_PATH),
    get_include_path(),
)));

date_default_timezone_set('Europe/Amsterdam');

defined('APPLICATION_ENV') || define('APPLICATION_ENV', (isset($argv[1]) ? $argv[1] : 'production'));

/** Zend_Application */
require_once 'Zend/Application.php';

$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.yaml'
);

$application->bootstrap();


if (1) {
    $currentDate = new My_DateTime();
    $date = $currentDate->format('Y-m-d');
    $currentDate->modify('-1 day');
    $prevDate = $currentDate->format('Y-m-d');
    //Check start history fill once
    $modelPlanning = new Application_Model_Planning();
    if (!$modelPlanning->checkExistDay($date)) {
        exit();
    }
    calculateDayHours($date,$prevDate);
} else {
    for ($i = 0; $i < 200 ; $i++) {
        $currentDate = new My_DateTime();
        $currentDate->modify('- ' . $i .' day');
        $date = $currentDate->format('Y-m-d');
        $currentDate->modify('- ' . $i +1 . ' day');
        $prevDate = $currentDate->format('Y-m-d');
        calculateDayHours($date,$prevDate);
    }
    //  var_dump($groups[$key]['users']);
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
}






//var_dump($modelGroup->getAllGroups());
