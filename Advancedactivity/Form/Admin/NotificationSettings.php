<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: NotificationSettings.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/

class Advancedactivity_Form_Admin_NotificationSettings extends Engine_Form {

  public function init() {
    $this
            ->setTitle('Notification Updates in Mini Navigation Menu')
            ->setDescription('You can now make the Notification Updates, that come in the header mini navigation menu, attractive like they are on Facebook. Below are the settings for this.');     $this->addElement('Radio', 'aaf_isenable_notification', array(
        'label' => 'Advanced Notification Updates',
        'description' => "Do you want to enable attractive, advanced notification updates in the mini navigation menu of your website. (Enabling this will replace the existing notification updates with the advanced one.)", 
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'onclick'=>'toggleNofiticationUpdate(this.value)',
        'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('aaf.isenable.notification', 1),
    ));

    $this->addElement('Select', 'aaf_notifications_inupdate', array(
        'label' => "Number of Notifications",
        'description' => 'Select the number of notifications that should be visible in the Updates dropdown. (The dropdown has a “View All Updates” link enabling users to read all updates.)',         'multiOptions' => array(
            '1' => "1",
            "2" => "2",
            "3" => "3",
            "4" => "4",
            "5" => "5",
            "6" => "6",
            "7" => "7",
            "8" => "8",
            "9" => "9",
            "10" => "10"
        ),
        'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('aaf.notifications.inupdate', 5),
        'disableTranslator' => 'true'
    ));
    // Add submit button
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
    ));
  }

}