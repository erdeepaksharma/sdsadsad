<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: ActionTypes.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Model_DbTable_ActionTypes extends Activity_Model_DbTable_ActionTypes {

    protected $_name = 'activity_actiontypes';
    protected $_actionTypes;

    public function getEnabledGroupedActionTypes() {
        // Get enabled modules
        $enabledModuleNames = Engine_Api::_()->getDbtable('modules', 'core')->getEnabledModuleNames();
        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction') == 1) {
            $exclude = 'friends_follow';
        } else {
            $exclude = 'friends';
        }

        // Get types
        $actionTypes = $this->select()
                ->from($this->info('name'), array('type', 'module'))
                ->where('enabled = ?', 1)
                ->where('displayable > ?', 0)
                ->where('module IN(?)', $enabledModuleNames)
                ->where('type != ?', $exclude)
                ->query()
                ->fetchAll()
        ;

        // Group them
        $groupedActionTypes = array('all' => null);
        foreach ($actionTypes as $actionType) {
            // All
            //$groupedActionTypes['all'][] = $actionType['type'];
            // Photo
            if (false !== strpos($actionType['type'], 'photo')) {
                $groupedActionTypes['photo'][] = $actionType['type'];
            }
            // Music
            if (false !== strpos($actionType['type'], 'music') ||
                    false !== strpos($actionType['type'], 'song')) {
                $groupedActionTypes['music'][] = $actionType['type'];
            }
            // Video
            if (false !== strpos($actionType['type'], 'video')) {
                $groupedActionTypes['video'][] = $actionType['type'];
            }
            if (in_array($actionType['type'], array( 'post_self_sell', 'post_sell'))) {
                $groupedActionTypes['advertise'][] = $actionType['type'];
            }
            // Posts?
            if ($actionType['type'] !=='post_self_sell' && ( false !== strpos($actionType['type'], 'comment') ||
                    false !== strpos($actionType['type'], 'topic') ||
                    false !== strpos($actionType['type'], 'post') ||
                    false !== strpos($actionType['type'], 'status') ||
                    $actionType['type'] === 'sitetagcheckin_checkin' ||
                    $actionType['type'] === 'share')) {
                $groupedActionTypes['posts'][] = $actionType['type'];
            }

            // Like?
            if (false !== strpos($actionType['type'], 'like')) {
                $groupedActionTypes['like'][] = $actionType['type'];
            }
            if (false !== strpos($actionType['type'], '_listtype_') && false !== strpos($actionType['module'], 'sitereview')) {
                $ex = explode('_listtype_', $actionType['type']);
                $type = 'sitereview_listtype_' . $ex['1'];
                $groupedActionTypes[$type][] = $actionType['type'];
            }
            // By module?
            $groupedActionTypes[$actionType['module']][] = $actionType['type'];
        }

        return $groupedActionTypes;
    }

    public function getFilterGroupedActionTypes($actionTypeGroup) {
        $groupedActionTypes = $this->getEnabledGroupedActionTypes();
        $actionTypeFilters = isset($groupedActionTypes[$actionTypeGroup]) ? $groupedActionTypes[$actionTypeGroup] : array();
        if (!in_array($actionTypeGroup, array('sitepage', 'sitebusiness', 'sitegroup', 'sitestore'))) {
            return $actionTypeFilters;
        }
        $actionTypeGroupSubModules = Engine_Api::_()->advancedactivity()->getSubModules($actionTypeGroup);
        foreach ($actionTypeGroupSubModules as $actionTypeGroupSubModule) {
            if (isset($groupedActionTypes[$actionTypeGroupSubModule])) {
                $actionTypeFilters = array_merge($actionTypeFilters, $groupedActionTypes[$actionTypeGroupSubModule]);
            }
        }
        return $actionTypeFilters;
    }

}
