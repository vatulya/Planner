<?php

class Calendar_IndexController extends Zend_Controller_Action
{

    const CALENDAR_CHANGE_TYPE_MONTH = 'month';
    const CALENDAR_CHANGE_TYPE_YEAR  = 'year';
    const CALENDAR_CHANGE_DIRECTION_LESS = 'less';
    const CALENDAR_CHANGE_DIRECTION_MORE = 'more';

    public $ajaxable = array(
        'index' => array('html'),
    );

    public $months = array();

    public $years = array();

    public function init()
    {
        $this->_helper->getHelper('AjaxContext')->initContext();
        $this->_helper->layout()->disableLayout();
        $this->_fillDateData();
    }

    public function indexAction()
    {
        $containerId = $this->_getParam('container_id');

        $editable = $this->_getParam('editable');
        $editable = $editable ? 1 : 0;

        $maxSelect = $this->_getParam('max_select', 0);
        $maxSelect = $maxSelect > 0 ? (int)$maxSelect : 0;

        /*** GET SHOWN DATE ***/
        $showDate = $this->_getParam('show_date');
        try {$showDate = new DateTime($showDate);} catch (Exception $e) {$showDate = new DateTime();}
        $showDate->setDate($showDate->format('Y'), $showDate->format('m'), '01'); // 2012-12-01

        /*** PREPARE OLD SELECTED DATES ***/
        $oldSelectedDates = $this->_getParam('old_selected_dates', '');
        $oldSelectedDates = $this->_normalizeDate($oldSelectedDates);
        $oldSelectedDates = $this->_sortDates($oldSelectedDates);
        $oldSelectedDates = $this->_toStringDates($oldSelectedDates);

        /*** PREPARE SELECTED DATES FOR SHOW-DATE ***/
        $selectedDates = $this->_getParam('selected_dates', '');
        $selectedDates = $this->_normalizeDate($selectedDates);
        $selectedDates = $this->_sortDates($selectedDates);
        $selectedDates = $this->_toStringDates($selectedDates);

        /*** PREPARE NEW SHOW DATE ***/
        $changeDateType      = $this->_getParam('change_type', '');
        $changeDateDirection = $this->_getParam('change_direction', '');
        $setShowDate         = $this->_getParam('set_show_date', '');
        $showDate = $this->_modifyShowDate($showDate, $changeDateType, $changeDateDirection, $setShowDate);

        $assign = array(
            'months'           => $this->months,
            'years'            => $this->years,
            'containerId'      => $containerId,
            'showDate'         => $showDate,
            'selectedDates'    => $selectedDates,
            'oldSelectedDates' => $oldSelectedDates,
            'editable'         => $editable,
            'maxSelect'        => $maxSelect,
        );

        $this->view->assign($assign);
    }

    protected function _modifyShowDate(DateTime $showDate, $changeDateType, $changeDateDirection, $setShowDate)
    {
        if ( ! empty($setShowDate)) {
            try {
                $setShowDate = new DateTime($setShowDate);
                if ($this->_checkDate($setShowDate)) {
                    $showDate = $setShowDate;
                }
            } catch (Exception $e) {}
        }
        if (
            ($changeDateType == self::CALENDAR_CHANGE_TYPE_MONTH || $changeDateType == self::CALENDAR_CHANGE_TYPE_YEAR)
            &&
            ($changeDateDirection == self::CALENDAR_CHANGE_DIRECTION_LESS || $changeDateDirection == self::CALENDAR_CHANGE_DIRECTION_MORE)
        ) {
            $modified = clone $showDate;
            $direction = $changeDateDirection == self::CALENDAR_CHANGE_DIRECTION_LESS ? '-' : '+';
            $type = $changeDateType == self::CALENDAR_CHANGE_TYPE_MONTH ? 'months' : 'years';
            $modified->modify($direction . '1 ' . $type); // +1 months
            if ($this->_checkDate($modified)) {
                $showDate = $modified;
            }
        }
        return $showDate;
    }

    protected function _checkDate(DateTime $date)
    {
        $minDate = key($this->years) . key($this->months); // 201201
        end($this->years); end($this->months);
        $maxDate = key($this->years) . key($this->months); // 201412
        reset($this->years); reset($this->months);
        $checkModified = (int)$date->format('Ym');
        if ($checkModified >= (int)$minDate || $checkModified <= (int)$maxDate) {
            return true;
        }
        return false;
    }

    protected function _normalizeDate($dates)
    {
        if ( ! is_array($dates)) {
            $dates = explode(',', $dates);
        }
        $dates = array_filter($dates);
        $normalizedData = array();
        foreach ($dates as $key => $value) {
            $fail = false;
            try {
                $key = '';
                $value = new DateTime($value);
                $key = $value->format('Y-m-d');
                if (array_key_exists($key, $dates)) {
                    $fail = true;
                }

            } catch (Exception $e) {$fail = true;}
            if ($fail) {
                $key = '';
                $value = '';
            }
            $normalizedData[$key] = $value;
        }
        $normalizedData = array_filter($normalizedData);
        return $normalizedData;
    }

    protected function _sortDates(array $dates)
    {
        $sort = function(DateTime $a, DateTime $b) {
            $aU = $a->format('U');
            $bU = $b->format('U');
            if ($aU < $bU) {
                return -1;
            }
            if ($aU > $bU) {
                return 1;
            }
            return 0;
        };
        usort($dates, $sort);
        return $dates;
    }

    protected function _toStringDates(array $dates, $format = 'Y-m-d')
    {
        foreach ($dates as $key => $value) {
            $dates[$key] = $value->format($format);
        }
        return $dates;
    }

    protected function _response($status = 0, $message = '', $data = array())
    {
        $response = array(
            'status'  => (int)$status,
            'message' => (string)$message,
            'data'    => (array)$data,
        );
        $this->view->response = $response;
    }

    protected function _fillDateData()
    {
        $months = array(
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maart',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Augustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'December',
        );
        $yearStart = 2012;
        $yearFinish = (int)date_create()->format('Y') + 2;
        $years = array();
        while ($yearStart <= $yearFinish) {
            $years[$yearStart] = $yearStart;
            $yearStart++;
        }
        $this->months = $months;
        $this->years = $years;
    }

}