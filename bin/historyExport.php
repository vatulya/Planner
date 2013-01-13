<?php
/*
 * This script send history users data to email.
 *
 * Need set this script to cron at end week(as example every sunday 23-55).
 */

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

if (empty($week) || empty($year))  {
    $weekYear = My_DateTime::getWeekYear();
    $year = $weekYear['year'];
    $week = $weekYear['week'];
}

$overview = new Application_Model_Overview();
$filename = $overview->getHistoryDataFile($week, $year);
$fullPath = EXPORT_PATH_DIR . '/' . $filename;

$subscribedEmail = new Application_Model_Db_User_Mail();
$emails = $subscribedEmail->getListMail();

$transport = new Zend_Mail_Transport_Sendmail('planner@futurumshop.com');
Zend_Mail::setDefaultTransport($transport);
$mail = new Zend_Mail();
foreach ($emails as $email) {
    $mail->addTo($email['email']);
}
$mail->setBodyText('Planner weekly export.');
$mail->setFrom('planner@futurumshop.com');
$mail->setSubject('Planner weekly export');
$handle = fopen( $fullPath, "r" );
$content = fread($handle, filesize($fullPath));
$mail->createAttachment($content, Zend_Mime::TYPE_OCTETSTREAM, Zend_Mime::DISPOSITION_ATTACHMENT,Zend_Mime::ENCODING_BASE64, $filename);
$mail->send();
