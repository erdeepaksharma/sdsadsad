<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminSettingsController.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */

class Advancedactivity_AdminSettingsController extends Core_Controller_Action_Admin {

  public function indexAction() {
      
    $onactive_disabled = array('advancedactivity_sitetabtitle', 'advancedactivity_post_canedit', 'advancedactivity_tabtype', 'advancedactivity_maxautoload',
        'advancedactivity_icon', 'logo_photo_preview', 'advancedactivity_info_tooltips',
        'advancedactivity_scroll_autoload', 'advancedactivity_composer_options', 'thirdparty_settings',
        'advancedactivity_update_frequency', 'advancedactivity_icon1', 'logo_photo_preview1', 'aaf_social_share_enable', 'submit', "advancedactivity_post_searchable", "advancedactivity_comment_show_bottom_post", "aaf_tagging_module", "linkedin_settings_temp", "seaocore_google_map_key", "linkedin_enable", "aaf_largephoto_enable", "advancedactivity_networklist_privacy", "facebook_enable", "twitter_enable", 'advancedactivity_feed_autoload', 'advancedactivity_composer_menuoptions', 'aaf_comment_like_box', 'advancedactivity_nestedcomment_setting', "instagram_enable", "notification_queueing","advancedactivity_pin_reset_days","aaf_notification_onoff_enable","advancedactivity_composer_share_menuoptions","advancedactivity_feed_cache","advancedactivity_feed_menu_align","advancedactivity_max_allowed_days");
    $this->view->googleapikey = Engine_Api::_()->getApi('settings', 'core')->getSetting('seaocore.google.map.key', '');
    $socialDNApublish = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('socialdnapublisher');
    if ('publish' != Engine_Api::_()->getApi('settings', 'core')->core_janrain_enable && empty($socialDNApublish)) {
      $onactive_disabled[] = "advancedactivity_post_byajax";
    }

    $afteractive_disabled = array('environment_mode', 'submit_lsetting');
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_settings');
    $this->view->form = $form = new Advancedactivity_Form_Admin_Global();
    $pluginName = 'advancedactivity';
    if (!empty($_POST[$pluginName . '_lsettings']))
      $_POST[$pluginName . '_lsettings'] = @trim($_POST[$pluginName . '_lsettings']);
    
    if(isset($_POST['advancedactivity_nestedcomment_setting'])) {
        unset($_POST['advancedactivity_nestedcomment_setting']);
    }
    if(isset($_POST['submit_lsetting'])){
        include APPLICATION_PATH . '/application/modules/Sitetagcheckin/settings/widgetSettings.php';
        include APPLICATION_PATH.'/application/modules/Sitereaction/settings/IconsActivation.php'; 
        include APPLICATION_PATH.'/application/modules/Advancedactivity/settings/ActivationSettings.php';
        $obj = new Sitereaction_IconsActivation();
        $obj->activate();
        $objectAdvancedactivity = new Advancedactivity_ActivationSettings();
        $objectAdvancedactivity->createFeelingTypes();
        $objectAdvancedactivity->createDefaultBannerAndGreeting();
        $objectAdvancedactivity->setWidgets();
        $objectAdvancedactivity->createNavigations();
        $objectAdvancedactivity->setMemberLevelPermissions();
    }
   
    include APPLICATION_PATH . '/application/modules/Advancedactivity/controllers/license/license1.php';
    
    }

    public function upgradeSettingsAction(){
        $redirect = $this->_getParam('redirect',null);
        if($redirect == 'install') {
            try{
            include APPLICATION_PATH . '/application/modules/Sitetagcheckin/settings/widgetSettings.php';
            include APPLICATION_PATH.'/application/modules/Sitereaction/settings/IconsActivation.php'; 
            include APPLICATION_PATH.'/application/modules/Advancedactivity/settings/ActivationSettings.php';
            $obj = new Sitereaction_IconsActivation();
            $obj->activate();
            $objectAdvancedactivity = new Advancedactivity_ActivationSettings();
            $objectAdvancedactivity->createFeelingTypes();
            $objectAdvancedactivity->createDefaultBannerAndGreeting();
            $objectAdvancedactivity->setWidgets();
            $objectAdvancedactivity->createNavigations();
            $objectAdvancedactivity->setMemberLevelPermissions();
            } catch (Exception $e){
                
            }
            $this->_helper->redirector->gotoUrl('install/manage');
        }
        $this->_helper->redirector->gotoRoute(array('module' => 'advancedactivity', 'controller' => 'settings'), 'admin_default', true);
    }
    public function postSettingsAction(){
         $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_settings');
         $this->view->form = $form = new Advancedactivity_Form_Admin_PostSettings();
         $settings = Engine_Api::_()->getApi('settings', 'core');
         $this->view->allowPin = $settings->getSetting('aaf.pinunpin.enable', 0);
         if (!$this->getRequest()->isPost()) {
            return;
         }
         if (!$form->isValid($this->getRequest()->getPost())) {
            return;
         }

         $values = $form->getValues();
         
         foreach ($values as $key => $value) {	
            $getSettings = $settings->getSetting($key);
            if(!empty($getSettings)){
                  $settings->removeSetting($key);
            }
            $settings->setSetting($key, $value);		
         }
         $this->view->form = $form = new Advancedactivity_Form_Admin_PostSettings();
         $form->renderForm();
         $form->addNotice('Your changes have been saved.');

    }
    public function thirdPartySettingsAction(){
         $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_settings');
         $this->view->form = $form = new Advancedactivity_Form_Admin_ThirdPartyServices();
         $settings = Engine_Api::_()->getApi('settings', 'core');

         if (!$this->getRequest()->isPost()) {
            return;
         }
         if (!$form->isValid($this->getRequest()->getPost())) {
            return;
         }

         $values = $this->getRequest()->getPost();
         unset($values['submit']);
         
        foreach ($values as $key => $value) {	
            $getSettings = $settings->getSetting($key);
            if(!empty($getSettings)){
                  $settings->removeSetting($key);
            }
            $settings->setSetting($key, $value);		
        }
         $form->addNotice('Your changes have been saved.');

    }
     
    
 public function feedSettingsAction(){
         $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_settings');
         $this->view->form = $form = new Advancedactivity_Form_Admin_FeedCustomization();
         $settings = Engine_Api::_()->getApi('settings', 'core');

      if (!$this->getRequest()->isPost()) {
            return;
         }
         if (!$form->isValid($this->getRequest()->getPost())) {
            return;
         }

         $values = $form->getValues();
         if($values["advancedactivity_feed_font_size"]< 24 || $values["advancedactivity_feed_font_size"] > 42) {
             $form->addError("Please enter the font size in between 24 to 42");
             return;
         }
        foreach ($values as $key => $value) {	
            $getSettings = $settings->getSetting($key);
            if(!empty($getSettings)){
                  $settings->removeSetting($key);
            }
            $settings->setSetting($key, $value);		
        }
        $form->addNotice('Your changes have been saved.');

    }
     public function homeFeedSettingsAction(){
         $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_settings');
         $this->view->form = $form = new Advancedactivity_Form_Admin_HomeFeedSettings();
         $settings = Engine_Api::_()->getApi('settings', 'core');
         if (!$this->getRequest()->isPost()) {
            return;
         }
         if (!$form->isValid($this->getRequest()->getPost())) {
            return;
         }

         $values = $form->getValues();
         unset($values['logo_photo_preview']);
         unset($values['logo_photo_preview1']);
         
        foreach ($values as $key => $value) {	
           $getSettings = $settings->getSetting($key);
           if(!empty($getSettings)){
                 $settings->removeSetting($key);
           }
           $settings->setSetting($key, $value);		
        }
    
         $form->addNotice('Your changes have been saved.');
    }

 public function wordSettingsAction(){
         $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_settings');
         $table = Engine_Api::_()->getDbtable('words', 'advancedactivity');	
         $select = $table->select()		
          ->order('word_id ASC');		
         $this->view->words = $table->fetchAll($select); 
    }

    public function createWordStyleAction(){
         $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_settings');
         $this->view->form = $form = new Advancedactivity_Form_Admin_WordStyle();
         if (!$this->getRequest()->isPost()) {
            return;
         }
         if (!$form->isValid($this->getRequest()->getPost())) {
            return;
         }
         
         $values = $form->getValues();
         $table = Engine_Api::_()->getDbtable('words', 'advancedactivity');
         $row = $table->createRow();
         $values['params'] = array();
         $values['params']['animation'] = $values['animation'];
         $values['params']['bg_enabled'] = $values['bg_enabled'];
         unset($values['animation']);
         $row->setFromArray($values);
         $row->save();
         $this->_helper->redirector->gotoRoute(array('module' => 'advancedactivity', 'controller' => 'settings', 'action' => 'word-settings'), 'admin_default', true);
          
    }
    
    
    
    public function editWordStyleAction(){
         $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_settings');
         $word_id = $this->_getParam('word_id','');
         if(empty($word_id)){
             return;
         }
         
         $this->view->item = $item = Engine_Api::_()->getItem("advancedactivity_word",$word_id);
         $this->view->form = $form = new Advancedactivity_Form_Admin_WordStyle(array(
            'item' => $item
         ));
         $data = $item->params ? array_merge($item->toArray(), $item->params) : $item->toArray();
         $form->populate($data);
         if (!$this->getRequest()->isPost()) {
            return;
         }
         if (!$form->isValid($this->getRequest()->getPost())) {
            return;
         }
         
         $values = $form->getValues();
         $values['params']['animation'] = $values['animation'];
         $values['params']['bg_enabled'] = $values['bg_enabled'];
         unset($values['animation']);
         $item->setFromArray($values);
         $item->save();
         $this->_helper->redirector->gotoRoute(array('module' => 'advancedactivity', 'controller' => 'settings', 'action' => 'word-settings'), 'admin_default', true);
          
    }

     public function deleteWordAction() {
     $this->_helper->layout->setLayout('admin-simple');
     $this->view->word_id = $word_id = $this->_getParam('word_id');

    if (!$this->getRequest()->isPost()) {
      return;
    }
    $values = $this->getRequest()->getPost();

    if ($values['confirm'] != $word_id) {
      return;
    }

    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();

    try {
       $row = Engine_Api::_()->getItem('advancedactivity_word', $word_id);	
       $row->delete();
       $db->commit();
    } catch (Exception $ex) {
      $db->rollBack();
      throw $ex;
    }
    $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => 10,
      'parentRefresh' => 10,
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Deleted'))
    ));
     }
    public function faqAction() {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_faq');
  }

  public function guidelinesAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_settings');
  }

  public function readmeAction() {
    
  }

  // This is the main action which are showing when click on "Welcome Settings" tab from the admin area.
  public function welcomeSettingsAction() {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_welcomesettings');

    $this->view->sub_navigation = $sub_navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_welcome_manage', array(), 'advancedactivity_admin_welcome_manage');

    $this->view->form = $form = new Advancedactivity_Form_Admin_WelcomeSettings();
    $this->view->isWelcomePageCorrect = Engine_Api::_()->advancedactivity()->isWelcomePageCorrect();

    $pageTable = Engine_Api::_()->getDbtable('pages', 'core');
    $pageTableName = $pageTable->info('name');

    $selectPage = $pageTable->select()
            ->from($pageTableName, array('page_id'))
            ->where('name =?', 'advancedactivity_index_welcometab')
            ->limit(1);
    $this->view->pageId = $selectPage->query()->fetch();


    if (!$this->getRequest()->isPost()) {
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    include APPLICATION_PATH . '/application/modules/Advancedactivity/controllers/license/license2.php';
  }

  // This is the main action which are showing when click on "Welcome Settings" tab from the admin area.
  public function manageCustomBlockAction() {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_welcomesettings');

    $this->view->sub_navigation = $sub_navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_welcome_manage', array(), 'advancedactivity_admin_customblock_manage');

    $page = $this->_getParam('page', 1);
    $sortingColumnName = $this->_getParam('idSorting', 0);
    $pagesettingsTable = Engine_Api::_()->getItemTable('advancedactivity_customblock');
    $pagesettingsSelect = $pagesettingsTable->select()->order('order ASC');
    $this->view->paginator = Zend_Paginator::factory($pagesettingsSelect);

    $this->view->paginator->setItemCountPerPage(100);
    $this->view->paginator->setCurrentPageNumber($page);

    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();
      if (!empty($values['customblock_id']) && !array_key_exists('delete', $values)) {
        $getOrder = $values['customblock_id'];
        $tableObject = Engine_Api::_()->getDbTable('customblocks', 'advancedactivity');
        $orderId = 1;
        foreach ($getOrder as $id) {
          $tableObject->update(array("order" => $orderId), array("customblock_id =?" => $id));
          $orderId++;
        }
      } else {
        foreach ($values as $key => $value) {
          if ($key == 'delete_' . $value) {
            $tableObject = Engine_Api::_()->getItem('advancedactivity_customblock', $value);
            if (!empty($tableObject->custom)) {
              $tableObject->delete();
            }
          }
        }
      }
      $this->_helper->redirector->gotoRoute(array('module' => 'advancedactivity', 'controller' => 'settings', 'action' => 'manage-custom-block'), 'admin_default', true);
    }
  }

  public function customBlockCreateAction() {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_welcomesettings');

    $this->view->customblock_id = $is_edit = $this->_getParam('customblock_id', 0);
    $textFlag = $this->_getParam('textFlag', 0);
    $limitation = $limitation_value = 0;
    $this->view->form = $form = new Advancedactivity_Form_Admin_CustomBlockCreate();

    // Only in the case of Edit.
    if (!empty($is_edit)) {
      $getContent = Engine_Api::_()->getItem('advancedactivity_customblock', $is_edit);
      if (!empty($getContent)) {
        $limitation = $getContent->limitation;
        $limitation_value = $getContent->limitation_value;
        $form->title->setValue($getContent->title);
        $form->limitation->setValue($getContent->limitation);
        $form->limitation_value->setValue($getContent->limitation_value);

        if (empty($textFlag)) {
          $form->description->setValue($getContent->description);
        } else {
          $form->text_description->setValue($getContent->description);
        }

        //SHOW PREFIELD NETWORKS
        $networks = Engine_Api::_()->getDbtable('networks', 'network')->fetchAll();
        if ($networks && isset($getContent->networks) && !empty($getContent->networks)) {
          if ($custom_networks = $form->getElement('networks')) {
            $custom_networks->setValue(Zend_Json_Decoder::decode($getContent->networks));
          }
        }

        //SHOW PREFIELD LEVELS
        if ($levels = $form->getElement('levels')) {
          $levels->setValue(Zend_Json_Decoder::decode($getContent->levels));
        }
      }
    }

    $form->temp_limitation->setValue($limitation);
    $form->temp_limitation_value->setValue($limitation_value);

    // Here we are change the "Description" of the form because If there are multiple language then "textFlag" create problem in language conversion.
    if (empty($textFlag)) {
      $form->removeElement('text_description');
      $textLinkFlag = $this->view->url(array('module' => 'advancedactivity', 'controller' => 'settings',
          'action' => 'custom-block-create', 'textFlag' => 1, 'page_id' => $page_id, 'customblock_id' => $is_edit), 'admin_default', true);
      $clickHere = "<a href='" . $textLinkFlag . "'> click here </a>";
      $textDescription = $this->view->translate("If your site supports multiple laguage then %s for the compatible Text input box.", $clickHere);
    } else {
      $form->removeElement('description');
      $textLinkFlag = $this->view->url(array('module' => 'advancedactivity', 'controller' => 'settings', 'action' => 'custom-block-create', 'textFlag' => 0, 'page_id' => $page_id, 'customblock_id' => $is_edit), 'admin_default', true);
      $textDescription = Zend_Registry::get('Zend_Translate')->_("If your site supports only one laguage then %s for the compatible Text input box.", $clickHere);
    }
    $form->text_flag->setDescription($textDescription);
    $form->text_flag->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));


    if (!$this->getRequest()->isPost()) {
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    $values = $form->getValues();

    if (!empty($values['limitation']) && empty($values['limitation_value'])) {
      $error = Zend_Registry::get('Zend_Translate')->_('Limitation value could not be empty.');
      $form->getDecorator('errors')->setOption('escape', false);
      $form->addError($error);
      return;
    }

    if (!empty($values['levels'])) {
      $values['levels'] = Zend_Json_Encoder::encode($values['levels']);
    }

    if (!empty($values['networks'])) {
      $values['networks'] = Zend_Json_Encoder::encode($values['networks']);
    }

    if (!empty($values['text_description'])) {
      $values['description'] = $values['text_description'];
    }
    unset($values['text_description']);
    unset($values['text_flag']);
    unset($values['temp_limitation']);
    unset($values['temp_limitation_value']);
    unset($values['flag']);

    include APPLICATION_PATH . '/application/modules/Advancedactivity/controllers/license/license2.php';
    $this->_helper->redirector->gotoRoute(array('module' => 'advancedactivity', 'controller' => 'settings', 'action' => 'manage-custom-block'), 'admin_default', true);
  }

  // Function: When approved or disapproved page (Help & Learn more page).
  public function enabledAction() {
    $this->view->enabled = $enabled = $this->_getParam('enabled');
    $this->view->id = $id = $this->_getParam('id');

    // Check post
    if ($this->getRequest()->isPost()) {
      $pagesettingsTable = Engine_Api::_()->getDbTable('customblocks', 'advancedactivity')->setUpdate(array('enabled' => $enabled), $id);

      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('Successfully done.')
      ));
    }
  }

  public function customBlockDeleteAction() {
    $customblock_id = $this->_getParam('customblock_id');
    // $this->view->customblock_id = $customblock_id;
    // Check post
    if ($this->getRequest()->isPost()) {
      $table = Engine_Api::_()->getItem('advancedactivity_customblock', $customblock_id);
      if (!empty($table)) {
        $table->delete();
      }
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('Deleted Successsfully.')
      ));
    }
  }

  public function notificationAction() {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_notificationsettings');
    $form = new Advancedactivity_Form_Admin_NotificationSettings();
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      include APPLICATION_PATH . '/application/modules/Advancedactivity/controllers/license/license2.php';
    }
    $this->view->form = $form;
  }

  /* this code is use for checking the feeds entry which come into in Stream table and not in action table also for deleteing this. */

  public function mismatchesFeedAction() {

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

    if (isset($_GET['delete']) && $_GET['delete'] && $action_ids) {
      $stream->delete(array('action_id IN(?)' => $action_ids));
      echo "Mismatch feed entry deleted:<br>";
      print_r($action_ids);
    } elseif ($action_ids) {

      echo "Note:If you want to delete the feed then also be pass in url \"?delete=1\" <br>";
      echo "Mismatch feed entry<br >";
      print_r($action_ids);
    } else {
      echo "No any feed found mismatch";
    }

    die;
  }

}
