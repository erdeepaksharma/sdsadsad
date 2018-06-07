<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Core.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Plugin_Sitemobile {

  protected $_pagesTable;
  protected $_contentTable;
  
  public function onIntegrated() {

    $this->_pagesTable = Engine_Api::_()->getApi('modules', 'sitemobile')->_pagesTable;
    $this->_contentTable = Engine_Api::_()->getApi('modules', 'sitemobile')->_contentTable;
    //Page Plugin Main
    $this->addSocialFeedPage();
  }
  
  //Get page id of pages from "sitemobile_pages" table.
  public function getPageId($page_name) {
    $db = Engine_Db_Table::getDefaultAdapter();

    // profile page
    $page_id = $db->select()
            ->from($this->_pagesTable, 'page_id')
            ->where('name = ?', $page_name)
            ->limit(1)
            ->query()
            ->fetchColumn();
    return $page_id;
  }
  
   public function addSocialFeedPage() {
   
    $db = Engine_Db_Table::getDefaultAdapter();
    $page_id = $this->getPageId('advancedactivity_socialfeed_index');
    if (!$page_id) { 
      $widgetCount = 0;
      $db->insert($this->_pagesTable, array(
          'name' => 'advancedactivity_socialfeed_index',
          'displayname' => 'Member Social Feed Page',
          'title' => 'Member Social Feed Page',
          'description' => 'This is the member Social Feed.',
          'custom' => 0,
      ));
      $page_id = $db->lastInsertId($this->_pagesTable);

      // containers
      $db->insert($this->_contentTable, array(
          'page_id' => $page_id,
          'type' => 'container',
          'name' => 'main',
          'parent_content_id' => null,
          'order' => 1,
          'params' => '',
      ));
      $container_id = $db->lastInsertId($this->_contentTable);

      $db->insert($this->_contentTable, array(
          'page_id' => $page_id,
          'type' => 'container',
          'name' => 'middle',
          'parent_content_id' => $container_id,
          'order' => 2,
          'params' => '',
      ));
      $middle_id = $db->lastInsertId($this->_contentTable);
      
      
      //TABED-CONTAINER
      $db->insert($this->_contentTable, array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitemobile.container-tabs-columns',
          'parent_content_id' => $middle_id,
          'order' => $widgetCount++,
          'params' => '{"layoutContainer":"horizontal","title":"","name":"sitemobile.container-tabs-columns"}'
      ));
      $main_middle_tabed_id = $db->lastInsertId();
      

      //FACEBOOK SOCIAL FEED WIDGET
      
      $db->insert($this->_contentTable, array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'advancedactivity.advancedactivityfacebook-userfeed',
          'parent_content_id' => $main_middle_tabed_id,
          'order' => 3,
          'params' => '{"title":"Facebook"}',
          'module' => 'advancedactivity'
      ));
      
       //LINKEDIN SOCIAL FEED WIDGET
      
      $db->insert($this->_contentTable, array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'advancedactivity.advancedactivitylinkedin-userfeed',
          'parent_content_id' => $main_middle_tabed_id,
          'order' => 5,
          'params' => '{"title":"Linkedin"}',
          'module' => 'advancedactivity'
      ));
      
       //TWITTER SOCIAL FEED WIDGET
      
      $db->insert($this->_contentTable, array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'advancedactivity.advancedactivitytwitter-userfeed',
          'parent_content_id' => $main_middle_tabed_id,
          'order' => 4,
          'params' => '{"title":"Twitter"}',
          'module' => 'advancedactivity'
      ));
      
      //INSERT THE ENTRY IN SITEMOBILE MENUITEM TABLE FOR SIDEPANEL LINK OF SOCIALFEED
      $db->query("INSERT IGNORE INTO `engine4_sitemobile_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`, `enable_mobile`, `enable_tablet`) VALUES
('core_main_socialfeed', 'advancedactivity', 'Social Feeds',  'Advancedactivity_Plugin_Menus::canViewSMFeeds', '{\"route\":\"default\", \"action\":\"index\", \"controller\" : \"socialfeed\", \"module\" : \"advancedactivity\"}', 'core_main', '', 3, 1, 1)");
    }
  }
}
