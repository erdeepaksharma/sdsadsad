<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Adsettings.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Admin_Adsettings extends Engine_Form {

  public function init() {

    $enable_ads = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('communityad');
    if (!$enable_ads) {
      $this->addElement('Dummy', 'note', array(
          'description' => '<div class="tip"><span>' . sprintf(Zend_Registry::get('Zend_Translate')->_('This plugin provides deep integration for advertising using our "%1$sAdvertisements / Community Ads Plugin%2$s". Please install this plugin after downloading it from your Client Area on SocialEngineAddOns and enable this plugin to configure settings for the various ad positions and widgets available. You may purchase this plugin %1$sover here%2$s. <br />This plugin also has an integration with our "%3sAdvertisements / Community Ads - Sponsored Stories Extension%4s" which will enable your users to create a short version of an Activity Feed Story for friend’s action on their contents. To know more about this plugin, please visit it %3sover here%4s.'), '<a href="http://www.socialengineaddons.com/socialengine-advertisements-community-ads-plugin" target="_blank">', '</a>', '<a href="http://www.socialengineaddons.com/adsextensions/socialengine-advertisements-sponsored-stories" target="_blank">', '</a>') . '</span></div>',
          'decorators' => array(
              'ViewHelper', array(
                  'description', array('placement' => 'APPEND', 'escape' => false)
          ))
      ));
    }
    
    if ($enable_ads) {
      $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
      $url = $view->url(array('controller' => 'widgets', 'action' => 'manage', 'module' => 'communityad'), 'admin_default', true);
      $this
              ->setTitle('Ad Settings')
              ->setDescription('This plugin provides seamless integration with the "Advertisements / Community Ads Plugin". Attractive advertising can be done using the many available, well designed ad positions in this plugin. Below, you can configure the settings for the various ad positions and widgets.');

      $this->addElement('Dummy', 'note', array(
           'description' => '<div class="tip"><span>' .Zend_Registry::get('Zend_Translate')->_("Below, you can configure settings for non-widgetize pages of this plugin. To configure the settings for widgetize pages of this plugin, you need to place \"Display Advertisements\" widget on widgetize pages.") . '</span></div>',
          'decorators' => array(
              'ViewHelper', array(
                  'description', array('placement' => 'APPEND', 'escape' => false)
          ))
      ));

      $this->addElement('Radio', 'sitereview_communityads', array(
          'label' => 'Community Ads in this plugin',
          'description' => 'Do you want to show community ads in the various positions available in this plugin? (Below, you will be able to choose for every individual position. If you do not want to show ads in a particular position, then please enter the value "0" for it below.).',
          'multiOptions' => array(
              1 => 'Yes',
              0 => 'No'
          ),
          'onclick' => 'showads(this.value)',
          'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.communityads', 1),
      ));

      $this->addElement('Text', 'sitereview_adalbumcreate', array(
          'label' => "Listing Photo's Add Page",
          'maxlenght' => 3,
          'description' => "How many ads will be shown on a listing photo's add page?",
          'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.adalbumcreate', 2),
      ));

      $this->addElement('Text', 'sitereview_addiscussionview', array(
          'label' => 'Listing Discussion\'s View Page',
          'maxlenght' => 3,
          'description' => 'How many ads will be shown on a listing discussion\'s view page?',
          'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.addiscussionview', 2),
      ));

      $this->addElement('Text', 'sitereview_addiscussioncreate', array(
          'label' => 'Listing Discussion\'s Create Page',
          'maxlenght' => 3,
          'description' => 'How many ads will be shown on a listing discussion\'s create page?',
          'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.addiscussioncreate', 2),
      ));

      $this->addElement('Text', 'sitereview_addiscussionreply', array(
          'label' => 'Listing Discussion\'s Post Reply Page',
          'maxlenght' => 3,
          'description' => 'How many ads will be shown on a discussion\'s post reply page?',
          'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.addiscussionreply', 2),
      ));

      $this->addElement('Text', 'sitereview_adtopicview', array(
          'label' => 'Listing Topics\'s View Page',
          'maxlenght' => 3,
          'description' => 'How many ads will be shown on a listing topic\'s view page?',
          'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.adtopicview', 2),
      ));

      $this->addElement('Text', 'sitereview_advideocreate', array(
          'label' => 'Listing Video\'s Create Page',
          'maxlenght' => 3,
          'description' => 'How many ads will be shown on a listing video\'s create page?',
          'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.advideocreate', 2),
      ));

      $this->addElement('Text', 'sitereview_advideoedit', array(
          'label' => 'Listing Video\'s Edit Page',
          'maxlenght' => 3,
          'description' => 'How many ads will be shown on a listing video\'s edit page?',
          'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.advideoedit', 2),
      ));

      $this->addElement('Text', 'sitereview_advideodelete', array(
          'label' => 'Listing Video\'s Delete Page',
          'maxlenght' => 3,
          'description' => 'How many ads will be shown on a listing video\'s delete page?',
          'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.advideodelete', 1),
      ));

      $this->addElement('Text', 'sitereview_adtagview', array(
          'label' => 'Browse Tags Page',
          'maxlenght' => 3,
          'description' => 'How many ads will be shown on browse tags page?',
          'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.adtagview', 1),
      ));

      $this->addElement('Button', 'submit', array(
          'label' => 'Save Changes',
          'type' => 'submit',
          'ignore' => true
      ));
    }
  }

}