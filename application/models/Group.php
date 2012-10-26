<?php

class Application_Model_Group extends Application_Model_Abstract
{

    protected $_modelDb;

    public function __construct()
    {
        $this->_modelDb = new Application_Model_Db_Groups();
    }

    public function getAllGroups()
    {
        $groups = $this->_modelDb->getAllGroups();
        return $groups;
    }

    public function getGroupById($groupId)
    {
        $group = $this->_modelDb->getGroupById($groupId);
        return $group;
    }

    public function getUserGroupsAdmin(array $user)
    {
        $admin = array();
        if (empty($user['role']) || empty($user['id'])) {
            return $admin;
        }
        if ($user['role'] == Application_Model_Auth::ROLE_GROUP_ADMIN) {
            $modelUserGroups = new Application_Model_Db_User_Groups();
            $admin = $modelUserGroups->getUserGroupsAdmin($user['id']);
        }
        return $admin;
    }

    public function saveGroup(array $group) {
        $result = false;
        if (Application_Model_Auth::getRole() >= Application_Model_Auth::ROLE_ADMIN) {
            if ( ! empty($group['id'])) {
                $oldGroup = $this->_modelDb->getGroupById($group['id']);
                if (empty($oldGroup)) {
                    return $result;
                }
                if ($oldGroup == $group) {
                    $result = true;
                    return $result;
                }
                $result = $this->_modelDb->updateGroup($group['id'], $group);
            } else {
                $result = $this->_modelDb->insertGroup($group);
            }
        }
        return $result;
    }

    public function deleteGroup($groupId)
    {
        if (Application_Model_Auth::getRole() >= Application_Model_Auth::ROLE_ADMIN) {
            $this->_modelDb->deleteGroup($groupId);
        }
        return true;
    }

    public function getGroupPlanning($groupId, $weekType = null)
    {
        $groupPlannings = new Application_Model_Db_Group_Plannings();
        $planning = $groupPlannings->getGroupPlanning($groupId, $weekType);
        return $planning;
    }

    public function saveGroupPlanning($groupId, array $planning)
    {
        $result = false;
        if (Application_Model_Auth::getRole() >= Application_Model_Auth::ROLE_ADMIN) {
            $group = $this->_modelDb->getGroupById($groupId);
            if ($group) {
                $planning = $this->_preparePlanning($planning);
                $groupPlannings = new Application_Model_Db_Group_Plannings();
                $result = $groupPlannings->saveGroupPlanning($groupId, $planning);
            }
        }
        return $result;
    }

    protected function _preparePlanning(array $planning)
    {
        $prepared = array();
        foreach ($planning as $key => $day) {
            if (empty($day['time_start']['hour']) || empty($day['time_end']['hour'])) {
                continue;
            }
            $timeStart = '';
            $timeEnd = '';
            try { $timeStart = new DateTime((int)$day['time_start']['hour'] . ':' . (int)$day['time_start']['min']); } catch (Exception $e) {}
            try { $timeEnd = new DateTime((int)$day['time_end']['hour'] . ':' . (int)$day['time_end']['min']); } catch (Exception $e) {}
            if ( ! $timeStart || ! $timeEnd || $timeStart >= $timeEnd) {
                continue; // wrong time
            }
            $day['day_number'] = intval($day['day_number']);
            if ($day['day_number'] < 1 || $day['day_number'] > 6 ) {
                continue; // wrong day number
            }
            if ($day['week_type'] != Application_Model_Db_Group_Plannings::WEEK_TYPE_ODD && $day['week_type'] != Application_Model_Db_Group_Plannings::WEEK_TYPE_EVEN) {
                continue; // wrong week type
            }
            $preparedDay = array(
                'week_type'  => $day['week_type'],
                'day_number' => $day['day_number'],
                'time_start' => $timeStart->format('H:i:s'),
                'time_end'   => $timeEnd->format('H:i:s'),
            );
            $prepared[$key] = $preparedDay;
        }
        return $prepared;
    }

    static public function getAllowedColors()
    {
        $colors = array(
            'FFFFFF','DCDCDC','D3D3D3','C0C0C0','808080','708090',
            'FFFFE0','F5F5DC','FFF8DC','FFFACD','FAFAD2','FFEFD5',
            'FAEBD7','FFE4C4','FFDEAD','FFC0CB','FFE4E1','E6E6FA',
            'D8BFD8','B0C4DE','ADD8E6','B0E0E6','AFEEEE','E0FFFF',
            'F0F8FF','F0FFFF','F0FFF0','90EE90','3CB371','DA70D6',
        );
        return $colors;
    }

}
