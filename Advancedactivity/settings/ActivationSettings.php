<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: ActivationSettings.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 
class Advancedactivity_ActivationSettings {
    
    
    public function createFeelingTypes() {
          
        $table = Engine_Api::_()->getDbtable('feelingtypes', 'advancedactivity');
        // GET PAGE LIST.
        $select = $table->select();
        $hasFeelingtypes = $table->fetchRow($select);
        if ($hasFeelingtypes) {
            return;
        }
        foreach ($this->getFeelingTypes() as $values) {
            $feelingTypeTable = Engine_Api::_()->getDbtable('feelingtypes', 'advancedactivity');
            $db = Engine_Db_Table::getDefaultAdapter();
            $db->beginTransaction();
            try {
                $feelingType = $feelingTypeTable->createRow();
                $feelingType->setFromArray($values);
                $feelingType->save();
                $feelingType->createFeelings($values['feelings']);
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                throw  $e;
            }
        }
        
    }

    public function createNavigations(){
         $db = Zend_Db_Table_Abstract::getDefaultAdapter() ;
         $sql = "SHOW COLUMNS FROM `engine4_activity_actions` LIKE 'publish_date'";
         $columnName = $db->query($sql)->fetch();
         if(empty($columnName)) {
            $db->query('ALTER TABLE `engine4_activity_actions`  ADD `publish_date` DATETIME NULL DEFAULT NULL');
         }
         $db->query('INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) VALUES
                ("advancedactivity_admin_main_activity_faq", "advancedactivity", "Activity FAQ", "", \'{"route":"admin_default","module":"advancedactivity","controller":"settings" ,"action":"faq"}\', "advancedactivity_admin_main_faq", "", 1, 0, 1),
                ("advancedactivity_admin_main_hashtag_faq", "advancedactivity", "Hashtag FAQ", "", \'{"route":"admin_default","module":"sitehashtag","controller":"settings" ,"action":"faq"}\', "advancedactivity_admin_main_faq", "", 1, 0, 2),
                ("advancedactivity_admin_main_reaction_faq", "advancedactivity", "Reaction FAQ", "", \'{"route":"admin_default","module":"sitereaction","controller":"faq"}\', "advancedactivity_admin_main_faq", "", 1, 0, 3),
                ("advancedactivity_admin_main_tagcheckin_faq", "advancedactivity", "Taging Checkin FAQ", "", \'{"route":"admin_default","module":"sitetagcheckin","controller":"settings","action":"faq"}\', "advancedactivity_admin_main_faq", "", 1, 0, 4),
                ("advancedactivity_admin_main_feelingtype", "advancedactivity", "Manage FeelingTypes", "", \'{"route":"admin_default","module":"advancedactivity","controller":"feelingtype"}\', "advancedactivity_admin_main", "", 1, 0, 82),
                ("advancedactivity_admin_main_greeting", "advancedactivity", "Manage Greetings / Announcements", "", \'{"route":"admin_default","module":"advancedactivity","controller":"greeting"}\', "advancedactivity_admin_main", "", 1, 0, 83),
                ("advancedactivity_admin_main_tagcheckin", "advancedactivity", "Geo-Location Tagging, Check-Ins", "", \'{"route":"admin_default","module":"sitetagcheckin","controller":"settings"}\', "advancedactivity_admin_main", "", 1, 0, 84),
                ("advancedactivity_admin_main_reaction", "advancedactivity", "Stickers & Reactions", "", \'{"route":"admin_default","module":"sitereaction","controller":"settings"}\', "advancedactivity_admin_main", "", 1, 0, 85),
                ("advancedactivity_admin_main_hashtag", "advancedactivity", "Hashtag Settings", "", \'{"route":"admin_default","module":"sitehashtag","controller":"settings"}\', "advancedactivity_admin_main", "", 1, 0, 86),
                ("advancedactivity_admin_main_member_level", "advancedactivity", "Member Level Settings", "", \'{"route":"admin_default","module":"advancedactivity","controller":"member-level"}\', "advancedactivity_admin_main", "", 1, 0, 87),
                ("advancedactivity_admin_main_settings_global", "advancedactivity", "Global Settings", "", \'{"route":"admin_default","module":"advancedactivity","controller":"settings"}\', "advancedactivity_admin_main_settings", "", 1, 0, 1),
                ("advancedactivity_admin_main_settings_post", "advancedactivity", "Status Update Settings", "", \'{"route":"admin_default","module":"advancedactivity","controller":"settings","action":"post-settings"}\', "advancedactivity_admin_main_settings", "", 1, 0, 2),
                ("advancedactivity_admin_main_settings_home_feed", "advancedactivity", "Home Feed Settings", "", \'{"route":"admin_default","module":"advancedactivity","controller":"settings","action":"home-feed-settings"}\', "advancedactivity_admin_main_settings", "", 1, 0, 3),
                ("advancedactivity_admin_main_settings_banner", "advancedactivity", "Manage Feed Background", "", \'{"route":"admin_default","module":"advancedactivity","controller":"banner"}\', "advancedactivity_admin_main_settings", "", 1, 0, 4),
                ("advancedactivity_admin_main_settings_feed", "advancedactivity", "Feed Decoration Settings", "", \'{"route":"admin_default","module":"advancedactivity","controller":"settings","action":"feed-settings"}\', "advancedactivity_admin_main_settings", "", 1, 0, 4),
                ("advancedactivity_admin_main_settings_style", "advancedactivity", "Word Styling", "", \'{"route":"admin_default","module":"advancedactivity","controller":"settings","action":"word-settings"}\', "advancedactivity_admin_main_settings", "", 1, 0, 5),
                ("advancedactivity_admin_main_settings_third_party", "advancedactivity", "Third Party Settings", "", \'{"route":"admin_default","module":"advancedactivity","controller":"settings","action":"third-party-settings"}\', "advancedactivity_admin_main_settings", "", 1, 0, 6),
                ("user_home_product_manage", "advancedactivity", "Manage BuySell Products", "", \'{"route":"default","module":"advancedactivity","controller":"buy-sell","action":"manage"}\', "user_home", "", 1, 0, 4),
                ("advancedactivity_admin_main_advertising", "advancedactivity", "Advertisment", NULL, \'{"route":"admin_default","module":"advancedactivity","controller":"advertisment", "action":"index"}\', "advancedactivity_admin_main", NULL, "1", "0", "995");');
    }
    public function getFeelingTypes() {
        $feelingPath = APPLICATION_PATH . '/application/modules/Advancedactivity/externals/feelings';
        $feelingInfo = include APPLICATION_PATH . '/application/modules/Advancedactivity/externals/feelings/feelingInfo.php';
        $feelingTypes = array();

        if (is_dir($feelingPath)) {
            foreach (scandir($feelingPath) as $dirName) {
                $dirPath = $feelingPath . '/' . $dirName;
                if (in_array($dirName, array('.', '..')) || !is_dir($dirPath)) {
                    continue;
                }
                $title = trim(str_replace(array('-', '_'), ' ', $dirName));
                $feelingType = array('title' => $title,'default' => 1,'feelings' => $this->getDefaultFiles($dirPath));
                if (isset($feelingInfo[$dirName]) && is_array($feelingInfo[$dirName])) {
                    $feelingType = array_merge($feelingType, $feelingInfo[$dirName]);
                }
                $feelingTypes[] = $feelingType;
            }
        }
        return $feelingTypes;
    }

    public function getDefaultFiles($path) {
        $feelings = array();
        if (is_dir($path)) {
            foreach (scandir($path) as $fileName) {
                $extension = ltrim(strrchr(basename($fileName), '.'), '.');
                if (!in_array($extension, array('jpg', 'png', 'gif', 'jpeg'))) {
                    continue;
                }
                $Filedata = array(
                    'tmp_name' => $path . '/' . $fileName,
                    'name' => $fileName,
                );
                $feelings[] = array('Filedata' => $Filedata);
            }
        }
        return $feelings;
    }
    public function createDefaultBannerAndGreeting() {
        $bannerPath = APPLICATION_PATH . '/application/modules/Advancedactivity/externals/banner';
        $bannerTable = Engine_Api::_()->getDbtable('banners', 'advancedactivity');
        // GET BANNER LIST.
        $select = $bannerTable->select();
        $hasBanner = $bannerTable->fetchRow($select);
        if (empty($hasBanner)) {
            $bannerValues = array('startdate' => date('Y-m-d'),'enddate' =>date("Y-m-d",strtotime("+2 year", strtotime(date('Y-m-d')))),
            'enabled' => 1,'color' => '#ffffff','background_color' => '#1dcc92','highlighted' => 0);
            $i = 1;
            foreach ($this->getDefaultFiles($bannerPath) as $values) {
                $db = Engine_Db_Table::getDefaultAdapter();
                $db->beginTransaction();
                try {
                    $bannerRow = $bannerTable->createRow();
                    $bannerRow->setFromArray(array_merge(array('order' => ++$i),$bannerValues));
                    $bannerRow->file_id = $bannerTable->setPhoto($values['Filedata']);
                    $bannerRow->save();
                    $db->commit();
                } catch (Exception $e) {
                    $db->rollBack();
                    throw  $e;
                }
            }  
            $bannerRow = $bannerTable->createRow();
            $bannerRow->setFromArray(array_merge(array('gradient' =>'radial-gradient(#33ccff 0%, #ff99cc 100%)','order' => ++$i),$bannerValues));
            $bannerRow->save();
        }
       
        
        
        //DEFAULT GREETING WORK
        $greetingTable = Engine_Api::_()->getDbtable('greetings', 'advancedactivity');
        $greetingPath = APPLICATION_PATH . '/application/modules/Advancedactivity/externals/greeting';
        $greetingInfo = include APPLICATION_PATH . '/application/modules/Advancedactivity/externals/greeting/greetingInfo.php';
        // GET BANNER LIST.
        $select = $greetingTable->select();
        $hasGreeting = $greetingTable->fetchRow($select);
        if (empty($hasGreeting)) {
            foreach ($this->getDefaultFiles($greetingPath) as $values) {
                $db = Engine_Db_Table::getDefaultAdapter();
               
                $greetTagName =substr($values['Filedata']['name'],0,-4);
                if(empty($greetingInfo[$greetTagName])) {
                    continue;
                }
                try {
                    $db->beginTransaction();
                    $greetingRow = $greetingTable->createRow();
                    $file_id  =  $bannerTable->setPhoto($values['Filedata']);
                    $file = Engine_Api::_()->getItemTable('storage_file')->getFile($file_id);
                    $bannerImage = $file->getHref();
                    $greetingBody = '<div style="width: 12%; display: inline-block; vertical-align: middle;">
                            <img style="width: 100%; max-width: 100%;" src="'.$bannerImage.'"></div>
                        <div style="max-width: 80%; word-break: keep-all; margin-left: 1%; vertical-align: middle; display: inline-block;"><span style="font-size: 18pt; color: #33cccc;">'.$greetingInfo[$greetTagName]['title'].'</span> 
                        <span style="font-size: 18pt; color: #ff9900;">[USER_NAME] !</span></div>';
                    $greetingValues = array('title' => $greetingInfo[$greetTagName]['title'],'body' =>$greetingBody,'enabled' => 1,'repeat' => 1,'starttime' => $greetingInfo[$greetTagName]['starttime'],'endtime' => $greetingInfo[$greetTagName]['endtime']);
                    $greetingRow->setFromArray($greetingValues);
                    $greetingRow->save();
                    $db->commit();
                } catch (Exception $e) {
                    $db->rollBack();
                    throw  $e;
                }
            }
        }
       
    }
    function setWidgets() {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter() ;
        $select = new Zend_Db_Select( $db ) ;
        $select
            ->from( 'engine4_core_pages' )
            ->where( 'name = ?' , 'user_index_home' )
            ->limit( 1 ) ;
        $page_id = $select->query()->fetchObject()->page_id;
        if ( !empty( $page_id ) ) {
            $select = new Zend_Db_Select($db);
            $select->from( 'engine4_core_content' )->where( 'name = ?' , 'advancedactivity.on-this-day' )->where( 'page_id = ?' ,
            $page_id)->limit(1);
            if(empty($select->query()->fetchObject()->content_id)){
                $select = new Zend_Db_Select($db);
                $select->from( 'engine4_core_content' )->where( 'name = ?' , 'advancedactivity.home-feeds' )->where( 'page_id = ?' ,
                $page_id)->limit(1);
                $results = $select->query()->fetchAll();
                if (!empty($results)) {
                    $db->insert( 'engine4_core_content' , array (
                      'page_id' => $page_id,
                      'type' => 'widget',
                      'name' => 'advancedactivity.on-this-day',
                      'parent_content_id' => $results[0]['parent_content_id'],
                      'order' => $results[0]['order']-1,
                      'params' => '{"title":""}',
                    ) ) ;
                }
            } 
        }
    }
    function setMemberLevelPermissions() {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter() ;
        $select = new Zend_Db_Select($db) ;
        $levelOptions = $select->from( 'engine4_authorization_levels' , array('level_id'))
                               ->where('type NOT IN(?)', array('public'))
                               ->query()
                               ->fetchAll();
        $levelIds = array();
        foreach ($levelOptions as $ids) {
            $levelIds[] = $ids['level_id'];
        }
        //$levelIds = array_column($levelOptions,'level_id');
        $authKey = array('aaf_add_feeling_enable','aaf_advertise_enable','aaf_greeting_enable','aaf_memories_enable','aaf_pinunpin_enable','aaf_schedule_post_enable','aaf_targeted_post_enable','aaf_add_feeling_enable','aaf_feed_banner_enable');
        foreach($levelIds as $level) {
            foreach ($authKey as $authName){
                $db->query("INSERT IGNORE INTO `engine4_authorization_permissions` (`level_id`, `type`, `name`, `value`, `params`) VALUES (".$level.", 'advancedactivity_feed', '".$authName."', '1', NULL)");
            }
        }
    }
}