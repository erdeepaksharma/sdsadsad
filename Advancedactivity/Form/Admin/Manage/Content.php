<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Content.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Form_Admin_Manage_Content extends Engine_Form {

  public function init() {

    $this
            ->setTitle('Add New Content Type')
            ->setDescription('Use the form below to add a content type for enabling users to filter activity feeds over it.');
    $notInclude = array('activity', 'advancedactivity', 'sitealbum', 'sitelike', 'sitepageoffer', 'sitepagebadge', 'featuredcontent', 'sitepagediscussion', 'sitepagelikebox', 'mobi', 'advancedslideshow', 'birthday', 'birthdayemail', 'communityad', 'dbbackup', 'facebookse', 'facebooksefeed', 'facebooksepage', 'feedback', 'groupdocument', 'grouppoll', 'mapprofiletypelevel', 'mcard', 'poke', 'sitealbum', 'sitepageinvite', 'siteslideshow', 'seaocore', 'suggestion', 'userconnection', 'sitepageform', 'sitepageadmincontact', 'sitebusinessbadge', 'sitebusinessoffer', 'sitebusinessdiscussion', 'sitebusinesslikebox', 'sitebusinessinvite', 'sitebusinessform', 'sitebusinessadmincontact', 'album', 'blog', 'classified', 'document', 'event', 'forum', 'poll', 'video', 'list', 'group', 'music', 'recipe', 'sitepage', 'sitepagenote', 'sitepagevideo', 'sitepagepoll', 'sitepagemusic', 'sitepagealbum', 'sitepageevent', 'sitepagereview', 'sitepagedocument', 'sitepageurl', 'sitebusiness', 'sitepageintegration', 'sitebusinessalbum', 'sitebusinessevent', 'sitebusinessreview', 'sitebusinessdocument', 'sitebusinessurl', 'sitebusinessnote', 'sitebusinessvideo', 'sitebusinesspoll', 'sitebusinessmusic', 'communityadsponsored', 'sitereviewlistingtype', 'sitestore','sitevideoview','sitegroupoffer','sitegroupbadge','sitegroupdiscussion','sitegrouplikebox','sitegroupinvite','sitegroupform','sitegroupadmincontact','sitegroupnote','sitegroup','sitegroupnote','sitegroupvideo','sitegrouppoll', 'sitegroupmusic', 'sitegroupalbum', 'sitegroupevent', 'sitegroupreview', 'sitegroupdocument', 'sitegroupurl','sitegroupintegration','sitegroupmember','sitestoreoffer','sitestorebadge','sitestorediscussion','sitestorelikebox','sitestoreinvite','sitestoreform','sitestoreadmincontact','sitestorenote','sitestore','sitestorenote','sitestorevideo','sitestorepoll', 'sitestoremusic', 'sitestorealbum', 'sitestoreevent', 'sitestorereview', 'sitestoredocument', 'sitestoreurl','sitestoreintegration','sitestoremember','sitepagemember','sitemobile','siteusercoverphoto','sitemobileapp','sitemailtemplates','siteevent','sitehashtag', 'sitenews','sitecrowdfunding');

    $defaultModule = Engine_Api::_()->getDbtable('contents', 'advancedactivity')->getDefaultAddedModule();
    
    if(!empty ($defaultModule))   
  $notInclude = array_merge($notInclude, $defaultModule);
    $module_table = Engine_Api::_()->getDbTable('modules', 'core');
    $module_name = $module_table->info('name');
    $select = $module_table->select()
            ->from($module_name, array('name', 'title'))
            ->where($module_name . '.type =?', 'extra')
            ->where($module_name . '.name not in(?)', $notInclude)
            ->where($module_name . '.enabled =?', 1);

    $contentModuloe = $select->query()->fetchAll();
    $contentModuloeArray = array();
    foreach ($contentModuloe as $modules) {
      $contentModuloeArray[$modules['name']] = $modules['title']. " ";
    }

    if (!empty($contentModuloeArray)) {
      $this->addElement('Select', 'module_name', array(
          'label' => 'Content Module',
          'allowEmpty' => false,
          'onchange' => 'setModuleName(this.value)',
          'multiOptions' => $contentModuloeArray,
      ));
      $this->addElement('Hidden', 'filter_type', array(
        'order' => 9685,
          'value' => key($contentModuloeArray),
      ));

      $this->addElement('Text', 'resource_title', array(
          'label' => 'Content Title',
          'description' => 'Enter the content title for which you use this module. Ex: You may use the Documents module for ‘Tutorials’ on your community.',
          'required' => true
      ));

      $this->addElement('Checkbox', 'content_tab', array(
          'description' => 'Filtering Activity Feeds',
          'label' => 'Enable filtering of activity feeds by users for this content type.',
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
          'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' =>
              'content-lists')),
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
      //VALUE FOR LOGO PREVIEW.
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_("There are currently no new content modules that could be added for “Content Lists”.") . "</span></div>";
      $this->addElement('Dummy', 'module', array(
          'description' => $description,
      ));
      $this->module->addDecorator('Description', array('placement' =>
          Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    }
  }

}

?>
