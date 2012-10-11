<?php

class Planner_PlanningController extends My_Controller_Action
{
    const HISTORY_WEEK_NUM = 4;
    public $ajaxable = array(
        'get-week-details' => array('json'),
        'get-more-history' => array('json'),
    );

    protected $_modelGroup;
    protected $_modelUser;

    public function init()
    {
        parent::init();
        $this->_modelGroup    = new Application_Model_Group();
        $this->_modelUser     = new Application_Model_User();
    //    $this->_me           = $this->_helper->CurrentUser();
    //    $this->view->me      = $this->_helper->CurrentUser();
    //    $this->_setParam('userId', $this->_me['id']);

        $currentWeekDateMonday = new DateTime();
       // $currentWeekDateMonday->modify('last Monday');  2012-10-05
       // $this->view->currentWeekDateMonday  = $currentWeekDateMonday->format('W');
       // $this->view->nextWeekDateMonday     = $currentWeekDateMonday->format('W');

        $currentWeekDateMonday->modify('+1 week');
    //    $this->view->nextWeekYear = $nextWeekDate->format('Y');
    //    $this->view->nextWeek     = $nextWeekDate->format('W');
//        $this->view->nextWeekYear = date('Y', strtotime('Monday this week +1 week'));
//        $this->view->nextWeek     = date('W', strtotime('Monday this week +1 week'));
          $this->view->weekDays     =  $weekDays = Planner_Model_Date::getWeekDays();
    }

    public function indexAction()
    {
       // $users = array($this->_me);
        $date = new Planner_Model_Date();
        $weekYear = $date->getWeekYear();
        $year = $weekYear['year'];
        $week = $weekYear['week'];
        $historyDateWeekYear = $this->_getNumHistoryWeeks($year, $week);
        $date->modify('+1 week');
        $nextDateWeekYear = $date->getWeekYear($date->getTimestamp());


        $groups = $this->_modelGroup->getAllGroups();

        foreach ($groups as $key => $group) {
            $groupId = $group['id'];
            $groups[$key] = $group;
            $groups[$key]['users'] = $this->_modelUser->getAllUsersByGroup($groupId);
            if (!empty($groups[$key]['users'])) {
                foreach ($groups[$key]['users'] as $keyUser => $user) {
                    $user = $this->_getUserWeekPlan($user, $groupId, $year, $week);
                    $groups[$key]['users'][$keyUser] = $user;
                }
            }
          //  echo "<pre>";
          //  var_dump($groups[$key]['users']);
        }
        $this->view->week                = $week;
        $this->view->year                = $year;
        $this->view->historyDateWeekYear = $historyDateWeekYear;
        $this->view->nextDateWeekYear    = $nextDateWeekYear;
        $this->view->groups              = $groups;
    }

    protected function _getUserWeekPlan($user, $groupId, $year, $week)
    {

        $user['history'] = $this->_getHistory($user['user_id'], $groupId, $year, $week);
        //echo "<pre>";
        //var_dump($user['history']);

        $user['weekPlan'] = $this->_modelUser->getUserWeekPlanByGroup($user['user_id'], $groupId,  $year, $week);
        return $user;
    }

    public function getMoreHistoryAction()
    {
        $oldestYear = $this->_getParam('oldestYear');
        $oldestWeek = $this->_getParam('oldestWeek');

        $user['history'] = $this->_getHistory($userId, $oldestYear, $oldestWeek);

        $html = $view->render('_blocks/history.phtml');
        $data = array(
            'html' => $html,
        );
        $this->_response(1, '', $data);
    }

    protected function _getHistory($userId, $groupId, $fromYear, $fromWeek, $weeksCount = self::HISTORY_WEEK_NUM)
    {
        $history = array();
        $historyWeeks = $this->_getNumHistoryWeeks($fromYear, $fromWeek, $weeksCount);
        foreach ($historyWeeks as $week => $year) {
            $history[$week] = $this->_getWeekHistory($userId, $groupId, $year, $week);
        }
        //$history = array_reverse($history, true);
        return $history;
    }

    protected function _getWeekHistory($userId, $groupId, $year, $week)
    {
        return $this->_modelUser->getUserWorkTimeByGroup($userId, $groupId, $year, $week);
    }

    protected function _getNumHistoryWeeks($fromYear, $fromWeek, $weeksCount = self::HISTORY_WEEK_NUM)
    {
        $historyWeeks = array();
        $start = new DateTime($fromYear . 'W' . sprintf("%02d", $fromWeek));
        for ($i = $weeksCount; $i > 0; $i-- ) {
            $historyDate = clone $start;
            $historyDate->modify('-' . $i . ' week');
            $year = $historyDate->format('Y');
            $week = $historyDate->format('W');
            $historyWeeks[$week] = $year;
        }
        //$historyWeeks = array_reverse($historyWeeks, true);
        return  $historyWeeks;
    }
}