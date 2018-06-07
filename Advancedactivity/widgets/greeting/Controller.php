<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Widget_GreetingController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    $front = Zend_Controller_Front::getInstance();
    $key = $front->getRequest()->getModuleName()."_".$front->getRequest()->getActionName();
    
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->greetingType = $greetingType = $this->_getParam('greetingType','all');
    if(!empty($viewer)){
        $viewer_id = $viewer->getIdentity();
    }
    if(Zend_Registry::isRegistered('aaf_activity_greeting_'.$key) && Zend_Registry::get('aaf_activity_greeting_'.$key) == $greetingType.'_'.$viewer_id ){
       $greetingType = 'custom'; 
    }
    if( empty($viewer_id) ) {
      return $this->setNoRender();
    }
    if( !Engine_Api::_()->authorization()->isAllowed('advancedactivity_feed', $viewer, 'aaf_greeting_enable') ) {
      return $this->setNoRender();
    }
    $todaysBirthday = $this->todaysBirthday($viewer);
    $request = new Zend_Controller_Request_Http();
    $has_seen_own = $request->getCookie('has_seen_own');
    $has_seen = $request->getCookie('has_seen');
    $has_seen_find = $request->getCookie('has_seen_find');
    if(!empty($todaysBirthday) && in_array($greetingType,array('all','userbased')) && in_array($viewer_id,$todaysBirthday) && empty($has_seen_own)){
        $this->view->userItSelfBirthday = $todaysBirthday;
        return ;
    }
    if(!empty($todaysBirthday) && in_array($greetingType,array('all','userbased')) && empty($has_seen)){
        $this->view->todaysBirthday = $todaysBirthday;
        Zend_Registry::set('aaf_activity_greeting_'.$key,$greetingType.'_'.$viewer_id);
        return ;
    }elseif(in_array($greetingType,array('userbased')) && empty($has_seen_find)){
        $this->view->findFriends = true;
        return ;
    }
    $greetingCookie = $request->getCookie('closed_greetings');
    $randomGreetingCookie = $request->getCookie('random_greetings');
    if( !empty($greetingCookie) ) {
      $greeting_ids = array_unique(explode(',', $greetingCookie));
    }
    if( !empty($randomGreetingCookie) ) {
      $randomGreeting_ids = array_unique(explode(',', $randomGreetingCookie));
    }
    /*On This Day Synchronization Work */
//    $onThisday = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getOnThisDayActivity($viewer);
//    $dayCookie = $request->getCookie('shared_this');
//    if(!empty($onThisday) && empty($dayCookie)) {
//      return $this->setNoRender();
//    }
    /*On This Day Synchronization Work */
    try {
      
      $timezone = Engine_Api::_()->getApi('settings', 'core')->core_locale_timezone;
      if($viewer_id) {
        $timezone = $viewer->timezone;
      }
      
      $oldTz = date_default_timezone_get();
      date_default_timezone_set($timezone);
      $date = date('Y-m-d H:i:s a');
      date_default_timezone_set($oldTz);
      $item = Engine_Api::_()->getDbTable('greetings', 'advancedactivity');
      $select = $item->select()
        ->where("((TIME(starttime) <= TIME('".$date."') and TIME(endtime) >= TIME('".$date."')) and `repeat` = 1 ) or ((`starttime` <= ? and `endtime` >= ?) and `repeat` = 0)", $date)
        ->where('enabled = ? ', 1)
      ;
      if( !empty($greeting_ids) ) {
        $select->where("greeting_id NOT IN (?)", $greeting_ids);
      }
      if( !empty($randomGreeting_ids) ) {
        $select->where("greeting_id NOT IN (?)", $randomGreeting_ids);
      }
      $select->order('greeting_id DESC')
        ;
      $greeting = $item->fetchAll($select)->toArray();
      if( empty($greeting) ) {
        return $this->setNoRender();
      }
      
      $this->view->body = $greeting[0]['body'];
      $this->view->greeting_id = $greeting[0]['greeting_id'];
      if((count($greeting)<=1 && !empty($randomGreetingCookie) && count($randomGreeting_ids)>1) || (count($greeting)>1 && empty($randomGreetingCookie))) {
         setcookie("random_greetings", $this->view->greeting_id, time()+86400, '/');
      }elseif(count($greeting)>1){
         $randomGreetingCookie.=','.$this->view->greeting_id;
         setcookie("random_greetings", $randomGreetingCookie, time()+86400, '/');
      }else {
         setcookie("random_greetings", null, time()-86400, '/'); 
      }
      
    } catch( Exception $e ) {
      throw $e;
      
    }
  }
  public function todaysBirthday($viewer) {
      
      $metaTable = Engine_Api::_()->fields()->getTable('user', 'meta');
      $viewer_id = $viewer->getIdentity();
      $birthday_column = $metaTable->select()
                             ->where("type=?",'birthdate')
                             ->query()->fetchColumn();
      $membershipIds = Engine_Api::_()->getDbTable('membership', 'user')->getMembershipsOfIds($viewer);
      $membershipIdsIncViewer = !empty($membershipIds) ? array_merge(array($viewer_id),$membershipIds) : array($viewer_id);
      $birthdayUserIds = array();
      if(!empty($membershipIdsIncViewer) && !empty($birthday_column)) {
        $valueTable = Engine_Api::_()->fields()->getTable('user', 'values');
        $birthdays = $valueTable->select()->from($valueTable->info('name'), array('item_id'))
                                  ->where('field_id = ? ',$birthday_column)
                                  ->where('DAY(value) = DAY(now())  and MONTH(value) = MONTH(now()) and item_id IN (?)',$membershipIdsIncViewer)
                                  ->query()
                                  ->fetchAll()
                                  ;
        //$birthdayUserIds = array_column($birthdays,"item_id");
        foreach ($birthdays as $value){
            $birthdayUserIds[] = $value['item_id'];
        }
      }
      return $birthdayUserIds;
      
  }

}
?>