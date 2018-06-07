<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: ThirdPartyServices.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Form_Admin_ThirdPartyServices extends Engine_Form {

    public function init() {

        $this
                ->setTitle('Third Party Services Settings')
                ->setDescription('Below settings will govern various third party integration on your website.');
        $coreSettingsApi = Engine_Api::_()->getApi('settings', 'core');

        $this->addElement('Dummy', 'linkedin_settings_temp', array(
            'label' => '',
            'decorators' => array(array('ViewScript', array(
                        'viewScript' => '_formcontactimport.tpl',
                        'class' => 'form element'
                    )))
        ));

        $this->addElement('Radio', 'facebook_enable', array(
            'label' => 'Publish to Facebook',
            'description' => "Do you want to integrate the 'Publish to Facebook' feature for status updates?",
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'value' => $coreSettingsApi->getSetting('facebook.enable', Engine_Api::_()->getApi('settings', 'core')->core_facebook_enable == 'publish' ? 1 : 0),
        ));

        $this->addElement('Radio', 'twitter_enable', array(
            'label' => 'Publish to Twitter',
            'description' => "Do you want to integrate the 'Publish to Twitter' feature for status updates?",
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'value' => $coreSettingsApi->getSetting('twitter.enable', Engine_Api::_()->getApi('settings', 'core')->core_twitter_enable == 'publish' ? 1 : 0),
        ));

        $this->addElement('Radio', 'linkedin_enable', array(
            'label' => 'Publish to LinkedIn',
            'description' => "Do you want to integrate the 'Publish to LinkedIn' feature for status updates?",
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'value' => $coreSettingsApi->getSetting('linkedin.enable', 0),
        ));


        // Element: submit
        $this->addElement('Button', 'submit', array(
            'label' => 'Save Changes',
            'type' => 'submit',
            'ignore' => true
        ));
    }

}

?>
