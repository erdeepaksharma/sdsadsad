<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Feed.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Plugin_Task_Feed extends Core_Plugin_Task_Abstract {

    public function execute() {

        $this->unpin();
        $this->schedulePost();
        $this->deleteMismatcheFeeds();

        return true;
    }

    public function unpin() {

        $numberOfDays = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.pin.reset.days', 7);
        $date = date('Y-m-d');
        $dateparams = strtotime($date);
        $timezone = Engine_Api::_()->getApi('settings', 'core')->core_locale_timezone;
        $oldTz = date_default_timezone_get();
        date_default_timezone_set($timezone);
        $dateparams = date("Y-m-d", $dateparams);
        $resetDate = date('Y-m-d', strtotime("-$numberOfDays day", strtotime($dateparams)));
        date_default_timezone_set($oldTz);

        $pinTable = Engine_Api::_()->getDbtable('pinsettings', 'advancedactivity');
        $pinTable->resetPin($resetDate);
    }

    public function schedulePost() {

        $actionTable = Engine_Api::_()->getDbtable('actions', 'advancedactivity');
        $max = $actionTable->select()
                ->from($actionTable->info('name'), array('action_id'))
                ->order('action_id DESC')
                ->limit(1)
                ->query()
                ->fetchColumn()
        ;

        $actions = $actionTable->getAllShecduledPostActions();
        $date = date('Y-m-d H:i:s');
        foreach ($actions as $action) {
            $max++;
            $action->action_id = $max;
            $action->date = $date;
            $action->modified_date = $date;
            $action->publish_date = NULL;
            $action->save();
            $actionTable->addActivityBindings($action);
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->query("ALTER TABLE ".$actionTable->info('name')." AUTO_INCREMENT = $max");
    }

    public function deleteMismatcheFeeds() {

        $action = Engine_Api::_()->getDbtable('actions', 'activity');
        $stream = Engine_Api::_()->getDbtable('stream', 'activity');
        $sName = $stream->info('name');
        $aName = $action->info('name');
        $action_ids = $stream->select()
                ->setIntegrityCheck(false)
                ->from($sName, "$sName.action_id")
                ->joinLeft($aName, "$sName.action_id = $aName.action_id   ", array())
                ->group($sName . '.action_id')
                ->where("$aName.action_id IS NULL")
                ->query()
                ->fetchAll(Zend_Db::FETCH_COLUMN);

        if ($action_ids) {
            $stream->delete(array('action_id IN(?)' => $action_ids));
        }
    }

}
