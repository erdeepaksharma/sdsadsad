<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Notificationsettings.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Model_DbTable_Notificationsettings extends Engine_Db_Table {

  public function getUserIdsByActionId($action_id) {
    $userIds = array();
    $select = $this->select()->from($this->info('name'),array('user_ids'))
                ->where('action_id  = ?', $action_id);
    
    $results = $select->query()
               ->fetchColumn();
    
    if(!$results){
        return ;
    }
   $userIds = json_decode($results); 
   
    return $userIds;
  }
  
  public function getActionIdsByUserId($user_id) {
    $actionIds = array();
    $select = $this->select();
    $results = $select->query()
                     ->fetchAll();
    foreach ($results as $result){
        if(in_array($user_id,json_decode($result['user_ids']))){
            $actionIds[] = $result['action_id'];
        }
       }
      
    return $actionIds;
  }
  
   public function isSetNotificationOff($action_id,$user_id) {
    $userIds = array();
    $select = $this->select()->from($this->info('name'),array('user_ids'))
                             ->where('action_id  = ?', $action_id);
    
    $results = $select->query()
               ->fetchColumn();
     if(!$results){
        return false;
    }
    $userIds = json_decode($results); 
    if(!in_array($user_id, $userIds)){
         return false;
    }
   
    return true;
  }

}