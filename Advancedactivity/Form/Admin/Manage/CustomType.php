<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: CustomType.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Form_Admin_Manage_CustomType extends Engine_Form {

  public function init() {

    $this
			->setTitle('Add New Content Type')
			->setDescription('Use the form below to add a content type for enabling users to create their custom lists for filtering activity feeds over them.');
			$notInclude = array('activity', 'advancedactivity', 'sitealbum', 'sitelike', 'sitepageoffer','sitepagebadge','featuredcontent','sitepagediscussion', 'sitepagelikebox', 'mobi','advancedslideshow','birthday', 'birthdayemail', 'communityad', 'dbbackup', 'facebookse', 'facebooksefeed','facebooksepage', 'feedback', 'groupdocument', 'grouppoll', 'mapprofiletypelevel','mcard','poke', 'sitealbum', 'sitepageinvite', 'siteslideshow', 'seaocore', 'suggestion','userconnection', 'sitepageform','sitepageadmincontact','sitebusinessbadge','sitebusinessoffer','sitebusinessdiscussion','sitebusinesslikebox','sitebusinessinvite','sitebusinessform','sitebusinessadmincontact','album', 'blog', 'classified', 'document', 'event', 'forum', 'poll', 'video','list','group', 'music', 'recipe', 'sitepage' , 'sitepagenote','sitepagevideo','sitepagepoll', 'sitepagemusic','sitepagealbum','sitepageevent', 'sitepagereview','sitepagedocument', 'sitepageurl', 'sitebusiness', 'sitepageintegration','sitebusinessalbum','sitebusinessevent', 'sitebusinessreview','sitebusinessdocument', 'sitebusinessurl', 'sitebusinessnote','sitebusinessvideo','sitebusinesspoll', 'sitebusinessmusic', 'communityadsponsored','sitevideoview','sitevideoview','sitetagcheckin','sitereviewlistingtype','sitereview','sitegroupoffer','sitegroupbadge','sitegroupdiscussion','sitegrouplikebox','sitegroupinvite','sitegroupform','sitegroupadmincontact','sitegroupnote','sitegroup','sitegroupnote','sitegroupvideo','sitegrouppoll', 'sitegroupmusic', 'sitegroupalbum', 'sitegroupevent', 'sitegroupreview', 'sitegroupdocument', 'sitegroupurl','sitegroupintegration','sitegroupmember','sitepagemember','siteusercoverphoto','sitemobile','sitemobileapp','sitemailtemplates','sitestoreoffer','sitestorebadge','sitestorediscussion','sitestorelikebox','sitestoreinvite','sitestoreform','sitestoreadmincontact','sitestorenote','sitestore','sitestorenote','sitestorevideo','sitestorepoll', 'sitestoremusic', 'sitestorealbum', 'sitestoreevent', 'sitestorereview', 'sitestoredocument', 'sitestoreurl','sitestoreintegration','sitestoremember','siteevent','sitehashtag', 'sitenews','sitecrowdfunding');
    

    $module_table = Engine_Api::_()->getDbTable('modules', 'core');
    $moduleName = $module_table->info('name');
    $select = $module_table->select()
            ->from($moduleName, array('name', 'title'))
            ->where($moduleName . '.type =?', 'extra')
            ->where($moduleName . '.name not in(?)', $notInclude)
            ->where($moduleName . '.enabled =?', 1);

    $contentModuloe = $select->query()->fetchAll();
    $contentModuloeArray = array();
    if (!empty($contentModuloe)) {
      $contentModuloeArray[] = '';
      foreach ($contentModuloe as $modules) {
        $contentModuloeArray[$modules['name']] = $modules['title']. " ";
      }
    }

    if (!empty($contentModuloeArray)) {
      $this->addElement('Select', 'module_name', array(
          'label' => 'Content Module',
          'allowEmpty' => false,
          'onchange' => 'setModuleName(this.value)',
          'multiOptions' => $contentModuloeArray,
      ));
    } else {
      //VALUE FOR LOGO PREVIEW.
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_("There are
currently no new content modules on your website that could be added for Custom Lists.") . "</span></div>";
      $this->addElement('Dummy', 'module', array(
          'description' => $description,
      ));
      $this->module->addDecorator('Description', array('placement' =>
          Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    }

    $module_name = Zend_Controller_Front::getInstance()->getRequest()->getParam('module_name', null);
    $contentItem = array();
    if (!empty($module_name)) {
      $this->module_name->setValue($module_name);
      if($module_name=='sitereviewlistingtype'){
        $resource_type = Zend_Controller_Front::getInstance()->getRequest()->getParam('resource_type', null);
        $contentItem[$resource_type]=$resource_type;
      }else{
      $contentItem = $this->getContentItem($module_name);
      }
      if (empty($contentItem))
        $this->addElement('Dummy', 'dummy_title', array(
            'description' => 'For this module not difine any item in manifest file.',
        ));
    }
    if (!empty($contentItem)) {
      $this->addElement('Select', 'resource_type', array(
          'label' => 'Content Type',
          'description' => "This is the value of 'items' key in the manifest file of this plugin. To view this value for a desired module, go to the directory of this module, and open the file 'settings/manifest.php'. In this file, search for 'items', and view its value. [Ex in case of blog module: Open file 'application/modules/Blog/settings/manifest.php', and go to around line 62. You will see the 'items' key array with value 'blog'. Thus, the Database Table Item for blog module is: 'blog']",
          //  'required' => true,
          'multiOptions' => $contentItem,
      ));

      $this->addElement('Text', 'resource_title', array(
          'label' => 'Content Title',
          'description' => 'Enter the content title for which you use this module. Ex: You may use the Documents module for ‘Tutorials’ on your community.',
          'required' => true
      ));

      $this->addElement('Checkbox', 'enabled', array(
          'description' => 'Enable for Custom Lists',
          'label' => 'Enable this content type to be part of users’ custom lists for filtering of activity feeds.',
          'value' => 1
      ));
      // Element: execute
      $this->addElement('Button', 'execute', array(
          'label' => 'Save Settings',
          'type' => 'submit',
          'ignore' => true,
          'decorators' => array('ViewHelper'),
      ));

      // Element: cancel
      $this->addElement('Cancel', 'cancel', array(
          'label' => 'cancel',
          'prependText' => ' or ',
          'ignore' => true,
          'link' => true,
          'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'custom-lists')),
          'decorators' => array('ViewHelper'),
      ));

      // DisplayGroup: buttons
      $this->addDisplayGroup(array('execute', 'cancel'), 'buttons', array(
          'decorators' => array(
              'FormElements',
              'DivDivDivWrapper',
          )
      ));
    } else {
      // Element: cancel
      $this->addElement('Cancel', 'cancel', array(
          'label' => 'cancel',
          'ignore' => true,
          'link' => true,
          'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'custom-lists')),
      ));
    }
  }

  public function getContentItem($moduleName) {
    $file_path = APPLICATION_PATH . "/application/modules/" . ucfirst($moduleName) . "/settings/manifest.php";
    $contentItem = array();
    if (@file_exists($file_path)) {
      $ret = include $file_path;
      if (isset($ret['items'])) {

        foreach ($ret['items'] as $item)
          $contentItem[$item] = $item . " ";
      }
    }
    return $contentItem;
  }

}

?>
