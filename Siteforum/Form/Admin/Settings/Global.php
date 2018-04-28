<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Global.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Form_Admin_Settings_Global extends Engine_Form {

    // IF YOU WANT TO SHOW CREATED ELEMENT ON PLUGIN ACTIVATION THEN INSERT THAT ELEMENT NAME IN THE BELOW ARRAY.
    public $_SHOWELEMENTSBEFOREACTIVATE = array(
        "environment_mode",
        "submit_lsetting"
    );
    
    public function init() {
        $this
                ->setTitle('Global Settings')
                ->setDescription('These settings affect all members in your community.');

        $coreSettings = Engine_Api::_()->getApi('settings', 'core');
        
        // ELEMENT FOR LICENSE KEY
        $this->addElement('Text', 'siteforum_lsettings', array(
            'label' => 'Enter License key',
            'description' => "Please enter your license key that was provided to you when you purchased this plugin. If you do not know your license key, please contact the Support Team of SocialEngineAddOns from the Support section of your Account Area.(Key Format: XXXXXX-XXXXXX-XXXXXX )",
            'value' => $coreSettings->getSetting('siteforum.lsettings'),
        ));
        
        if (APPLICATION_ENV == 'production') {
            $this->addElement('Checkbox', 'environment_mode', array(
                'label' => 'Your community is currently in "Production Mode". We recommend that you momentarily switch your site to "Development Mode" so that the CSS of this plugin renders fine as soon as the plugin is installed. After completely installing this plugin and visiting few stores of your site, you may again change the System Mode back to "Production Mode" from the Admin Panel Home. (In Production Mode, caching prevents CSS of new plugins to be rendered immediately after installation.)',
                'description' => 'System Mode',
                'value' => 1,
            ));
        } else {
            $this->addElement('Hidden', 'environment_mode', array('order' => 990, 'value' => 0));
        }

        $this->addElement('Button', 'submit_lsetting', array(
            'label' => 'Activate Your Plugin Now',
            'type' => 'submit',
            'ignore' => true
        ));

        $this->addElement('Radio', 'siteforum_bbcode', array(
            'label' => 'Enable BBCode',
            'multiOptions' => array(
                1 => 'Yes, members can use BBCode tags.',
                0 => 'No, do not let members use BBCode.'
            ),
            'value' => $coreSettings->getSetting('siteforum.bbcode', 1),
        ));

        $this->addElement('Radio', 'siteforum_html', array(
            'label' => 'Enable HTML',
            'multiOptions' => array(
                1 => 'Yes, members can use HTML in their posts.',
                0 => 'No, strip HTML from posts.'
            ),
            'value' => $coreSettings->getSetting('siteforum.html', 1),
        ));

        $this->addElement('Radio', 'siteforum_thanks', array(
            'label' => 'Allow Thanks',
            'description' => 'Do you want to allow users to thank topic owner, if they finds the post helpful / interesting?',
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'value' => $coreSettings->getSetting('siteforum.thanks', 1),
        ));

        $this->addElement('Radio', 'siteforum_rating', array(
            'label' => 'Allow Rating',
            'description' => 'Do you want to allow users to rate the topics?',
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'value' => $coreSettings->getSetting('siteforum.rating', 1),
        ));

        $this->addElement('Radio', 'siteforum_reputation', array(
            'label' => 'Reputation Increase / Decrease',
            'description' => 'Do you want to give users the choice to increase / decrease post creator\'s reputation?',
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'value' => $coreSettings->getSetting('siteforum.reputation', 1),
        ));

        // Add submit button
        $this->addElement('Button', 'submit', array(
            'label' => 'Save Changes',
            'type' => 'submit',
            'ignore' => true
        ));
    }

}
