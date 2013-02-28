<?php

class Application_Model_Db_Group_Settings extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'group_settings';

    /**
     * This property for check allowed settings for Set in method saveGroupSetting
     * @var array
     */
    protected $_allowedSaveSettings = array(
        'max_free_people',
        'alert_over_limit_free_people',
    );

    /**
     * Method set default settings for some group by Id.
     * Also used when created new group.
     *
     * @param $groupId
     * @return int
     */
    public function setDefaultGroupSettings($groupId)
    {
        $default = $this->getDefaultGroupSettings();
        if ( ! $default) {
            $default = $this->createDefaultGroupSettings();
        }
        $this->_db->delete(self::TABLE_NAME, array('group_id = ?' => $groupId));
        $default['group_id'] = $groupId;
        $result = $this->_db->insert(self::TABLE_NAME, $default);
        return $result;
    }

    /**
     * This method return array of default settings
     *
     * @return array
     */
    public function getDefaultGroupSettings()
    {
        $result = $this->getGroupSettings(0); // 0 - this is default group and general group
        return $result;
    }

    /**
     * This method for create default settings in database.
     * Will be called once when project start and database is empty.
     *
     * @return int
     */
    public function createDefaultGroupSettings()
    {
        // TODO: maybe need move this to other location ? config or something else?
        $default = array(
            'group_id'        => 0,
            'pause_start'     => '13:00:00',
            'pause_end'       => '14:00:00',
            'max_free_people' => 2,
        );
        $result = $this->_db->insert(self::TABLE_NAME, $default);
        return $result;
    }

    /**
     * Delete settings row from database by group ID.
     * Called when group was deleted.
     *
     * @param $groupId
     * @return bool
     */
    public function deleteGroupSettings($groupId)
    {
        $this->_db->delete(self::TABLE_NAME, array('group_id = ?' => $groupId));
        return true;
    }

    /**
     * Method return array of all settings for some group by ID.
     *
     * @param $groupId
     * @return array
     */
    public function getGroupSettings($groupId)
    {
        $select = $this->_db->select()
            ->from(array('gs' => self::TABLE_NAME))
            ->where('gs.group_id = ?', $groupId);
        $result = $this->_db->fetchRow($select);
        if (empty($result)) {
            // no settings? need check
            $selectCheck = $this->_db->select()
                ->from(array('g' => Application_Model_Db_Groups::TABLE_NAME))
                ->where('g.id = ?', $groupId);
            $check = $this->_db->fetchRow($selectCheck);
            if ($check) {
                // Yes. Group exists, but without any settings. This is wrong. Need create default settings
                $this->setDefaultGroupSettings($groupId);
                $result = $this->_db->fetchRow($select);
            }
        }
        return $result;
    }

    /**
     * Method save only Pause setting
     *
     * @param int $groupId
     * @param string $start
     * @param string $end
     * @return bool|int
     */
    public function saveGroupPause($groupId, $start, $end)
    {
        $data = array(
            'pause_start' => $start,
            'pause_end'   => $end,
        );
        $select = $this->_db->select()
            ->from(array('gs' => self::TABLE_NAME))
            ->where('gs.group_id = ?', $groupId);
        $check = $this->_db->fetchRow($select);
        if ($check) {
            if ($check['pause_start'] != $data['pause_start'] || $check['pause_end'] != $data['pause_end']) {
                $result = $this->_db->update(self::TABLE_NAME, $data, array('group_id = ?' => $groupId));
            } else {
                // Nothing to update.
                $result = true;
            }
        } else {
            // Insert must be in Model (not DB model)
//            $data['group_id'] = $groupId;
//            $result = $this->_db->insert(self::TABLE_NAME, $data);
        }
        return $result;
    }

    /**
     * Method remove Pause from group
     *
     * @param $groupId
     * @return bool
     */
    public function deleteGroupPause($groupId)
    {
        $data = array(
            'pause_start' => 'NULL',
            'pause_end'   => 'NULL',
        );
        $result = $this->_db->update(self::TABLE_NAME, $data, array('group_id = ?' => $groupId));
        return true;
    }

    /**
     * Method for save only one allowed setting.
     * Allowed settings you can see in property $_allowedSaveSettings
     *
     * @param int $groupId
     * @param string $setting
     * @param string $value
     * @return bool
     */
    public function saveGroupSetting($groupId, $setting, $value)
    {
        $result = false;
        if (in_array($setting, $this->_allowedSaveSettings)) {
            try {
                $data = array(
                    $setting => $value,
                );
                $check = $this->getGroupSettings($groupId);
                if ($check[$setting] == $data[$setting]) {
                    $result = true;
                } else {
                    $result = $this->_db->update(self::TABLE_NAME, $data, array('group_id = ?' => $groupId));
                }
            } catch (Exception $e) {
                throw new Exception('Error! Database error!');
            }
        } else {
            throw new Exception('Error! Not allowed setting.');
        }
        return $result;
    }

}
