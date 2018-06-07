<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Global.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Form_Admin_Global extends Engine_Form {

    public function init() {

        $this
          ->setTitle('Global Settings')
          ->setDescription('These settings affect all members in your community.');
        $coreSettingsApi = Engine_Api::_()->getApi('settings', 'core');
        $this->addElement('Text', 'advancedactivity_lsettings', array(
          'label' => 'Enter License key',
          'description' => "Please enter your license key that was provided to you when you purchased this plugin. If you do not know your license key, please contact the Support Team of SocialEngineAddOns from the Support section of your Account Area.(Key Format: XXXXXX-XXXXXX-XXXXXX )",
          'required' => true,
          'value' => $coreSettingsApi->getSetting('advancedactivity.lsettings'),
        ));

        if (APPLICATION_ENV == 'production') {
            $this->addElement('Checkbox', 'environment_mode', array(
              'label' => 'Your community is currently in "Production Mode". We recommend that you momentarily switch your site to "Development Mode" so that the CSS of this plugin renders fine as soon as the plugin is installed. After completely installing this plugin and visiting few pages of your site, you may again change the System Mode back to "Production Mode" from the Admin Panel Home. (In Production Mode, caching prevents CSS of new plugins to be rendered immediately after installation.)',
              'description' => 'System Mode',
              'value' => 1,
            ));
        } else {
            $this->addElement('Hidden', 'environment_mode', array('order' => 990, 'value' => 0));
        }

        $settings = $coreSettingsApi;


        // Add submit button
        $this->addElement('Button', 'submit_lsetting', array(
          'label' => 'Activate Your Plugin Now',
          'type' => 'submit',
          'ignore' => true
        ));

        $this->addElement('Radio', 'advancedactivity_scroll_autoload', array(
          'label' => 'Auto-Loading Activity Feeds On-scroll',
          'description' => "Do you want to enable auto-loading of old activity feeds when users scroll down to the bottom of Advanced Activity Feeds? (This setting will apply to the site activity feeds as well as those from Twitter.)",
          'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
          ),
          'onchange' => 'switchContentField(this.value,"advancedactivity_maxautoload")',
          'value' => $coreSettingsApi->getSetting('advancedactivity.scroll.autoload', 1),
        ));

        $this->addElement('Select', 'advancedactivity_maxautoload', array(
          'label' => "Auto-Loading Count",
          'description' => 'Select the number of times that auto-loading of old activity feeds should occur on scrolling down. (Select 0 if you do not want such a restriction and want auto-loading to occur always. Because of auto-loading on-scroll, users are not able to click on links in footer; this setting has been created to avoid this.)',
          'multiOptions' => array(
            '0' => '0',
            '1' => "1",
            '2' => '2',
            "3" => "3",
            "4" => "4",
            "5" => "5",
            "6" => "6",
            "7" => "7",
            "8" => "8",
            "9" => "9",
            "10" => "10"
          ),
          'value' => $coreSettingsApi->getSetting('advancedactivity.maxautoload', 0),
          'disableTranslator' => 'true'
        ));
//        $this->addElement('Radio', 'aaf_largephoto_enable', array(
//          'label' => 'Bigger Photo Size in Feeds',
//          'description' => 'Do you want to enable bigger photo size and improve alignment of photos in activity feeds? (Note: This setting will not affect the photos uploaded / shared from "SocialEngine Photo Albums Plugin" and our "Advanced Photo Albums Plugin"; photos from these two will always appear bigger.)',
//          'multiOptions' => array(
//            1 => 'Yes',
//            0 => 'No'
//          ),
//          'value' => $coreSettingsApi->getSetting('aaf.largephoto.enable', 1),
//        ));

        $this->addElement('Radio', 'advancedactivity_post_canedit', array(
          'label' => 'Edit Privacy for Status Update Posts',
          'description' => "Do you want users to be able to edit privacy for their status update posts? (Note: If you select ‘Yes’ over here, then the option to change status update post privacy will be shown on member profile page activity feeds in the dropdown alongside the post.)",
          'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
          ),
          'value' => $coreSettingsApi->getSetting('advancedactivity.post.canedit', 1),
        ));
        $this->addElement('Text', 'advancedactivity_pin_reset_days', array(
          'label' => 'Pinned Post Duration',
          'description' => "Enter the time duration in days for feed to remain as pinned post.
[Note: Entered days will be set as limit for users. Users will be able to set the time frame for their pinned post under this limit only. Incase, user has not set any time frame then these days will be considered as default duration for the pinned post.]",
          'value' => $coreSettingsApi->getSetting('advancedactivity.pin.reset.days', 7),
        ));
        $this->addElement('Radio', 'aaf_notification_onoff_enable', array(
          'label' => 'Enable Feed Wise Notification',
          'description' => 'By enabling it, user will able to turn on / off notification for a particular feed.',
          'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
          ),
          'value' => $coreSettingsApi->getSetting('aaf.notification.onoff.enable', 1),
        ));

        //info tooltips
        $this->addElement('Radio', 'advancedactivity_info_tooltips', array(
          'label' => 'Info Tooltips',
          'description' => "Do you want to enable interactive Info Tooltips on mouse-over for sources and entities in the site activity feeds in Advanced Activity Feeds? (The interactive Info Tooltips contain information and quick action links for the entities. You can choose more settings for these from the Info Tooltip Settings.)",
          'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
          ),
          'value' => $coreSettingsApi->getSetting('advancedactivity.info.tooltips', 1),
        ));

        $this->addElement('Radio', 'aaf_social_share_enable', array(
          'label' => 'Social Share for Feed',
          'description' => 'Do you want to enable social share for feed? If enabled users will be able to share the feed on their social accounts [like Facebook, LinkedIn, etc] right away. For this they will simply need to hover the share button to choose one of the social accounts available in the tip.',
          'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
          ),
          'value' => $coreSettingsApi->getSetting('aaf.social.share.enable', 1),
        ));
        $this->addElement('MultiCheckbox', 'advancedactivity_composer_share_menuoptions', array(
            'description' => 'Choose the social share option which you want have in share options of activity feeds.',
            'label' => 'Social Share',
            'multiOptions' => array('facebook'=>'Facebook','twitter'=>'Twitter','linkedin'=>'LinkedIn','googleplus'=>'Google Plus'),
            'value' => $coreSettingsApi->getSetting('advancedactivity.composer.share.menuoptions', array('facebook','twitter','linkedin','googleplus'))
        ));
        if (!Engine_Api::_()->seaocore()->checkEnabledNestedComment('advancedactivity')) {
            $this->addElement('Radio', 'advancedactivity_comment_show_bottom_post', array(
              'label' => 'Quick Comment Box',
              'description' => 'Do you want to show a "Post a comment" box for activity feeds that have comments? (Enabling this can increase interactivity via comments on activity feeds on your site. Users will be able to quickly post a comment on activity feeds just by pressing the "Enter" key.)',
              'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
              ),
              'value' => $coreSettingsApi->getSetting('advancedactivity.comment.show.bottom.post', 1),
            ));

            $this->addElement('Radio', 'aaf_comment_like_box', array(
              'label' => 'Comments and Likes Box',
              'description' => 'Do you want to hide the "Comments" and "Likes" box by default for activity feeds display? (After enabling this, counts for comments and likes are shown with attractive icons beside them and Comments and Likes boxes for feeds are hidden by default and appear after clicking on the comments icon.)',
              'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
              ),
              'value' => $coreSettingsApi->getSetting('aaf.comment.like.box', 0),
            ));
        }
         $this->addElement('Radio', 'advancedactivity_feed_cache', array(
          'label' => 'Feed Cache',
          'description' => "Do you want to enable activity feed caching so that you can enhance your site loading speed?",
          'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
          ),
          'value' => $coreSettingsApi->getSetting('advancedactivity.feed.cache', 1),
        ));
        $this->addElement('Radio', 'aaf_translation_feed_enable', array(
          'label' => 'Allow Feed Translation',
          'description' => "Do you want to enable per feed translation option?",
          'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
          ),
          'value' => $coreSettingsApi->getSetting('aaf.translation.feed.enable', 1),
        ));
        $this->addElement('Radio', 'advancedactivity_feed_menu_align', array(
                'label' => 'Feed Bottom Menu Alignment',
                'description' => "Set the feed bottom menu(comment/like/share etc.) alignment",
                'multiOptions' => array(
                    'right' => 'Right Align',
                    'left' => 'Left Align'
                ),
                'value' => $coreSettingsApi->getSetting('advancedactivity.feed.menu.align', 'right'),
              ));
        if ((Engine_Api::_()->hasModuleBootstrap('siteiosapp') || Engine_Api::_()->hasModuleBootstrap('siteandroidapp') || Engine_Api::_()->hasModuleBootstrap('sitepushnotification'))) {
            // Element: notification_queue
            $this->addElement('Radio', 'notification_queueing', array(
                'label' => 'Notification Queue',
                'description' => 'Utilizing a notification queue, you can allow your website to throttle the notifications being sent out to prevent overloading the site server.',
                'required' => true,
                'multiOptions' => array(
                    1 => 'Yes, enable notification queue',
                    0 => 'No, always send notifications immediately',
                ),
                'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('notification.queueing', 1),
            ));
        }
        
         $this->addElement('Text', 'advancedactivity_max_allowed_days', array(
                'label' => 'Story Duration',
                'description' => 'Please inter story duration in a days.',
                
                'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity_max_allowed_days', 1),
            ));
         
        $row = Engine_Api::_()->seaocore()->checkEnabledNestedComment('advancedactivity', array('checkModuleExist' => true));
        if ($row && Engine_Api::_()->seaocore()->checkEnabledNestedComment('advancedactivity')) {
            $module_id = $row->module_id;
            $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
            $URL = $view->url(array('module' => 'nestedcomment', 'controller' => 'module', 'action' => 'edit', 'module_id' => $module_id), 'admin_default', true);
            $description = sprintf(Zend_Registry::get('Zend_Translate')->_('Please %1svisit here%2s to configure the ‘Advanced Comments / Replies’ setting for activity feeds.'), "<a href='" . $URL . "' target='_blank'>", "</a>");
            $this->addElement('Dummy', 'advancedactivity_nestedcomment_setting', array(
              'label' => 'Advanced Comment / Replies Settings',
              'description' => $description,
            ));
            $this->getElement('advancedactivity_nestedcomment_setting')->getDecorator('Description')->setOptions(array('placement', 'APPEND', 'escape' => false));
        }

        // Element: submit
        $this->addElement('Button', 'submit', array(
          'label' => 'Save Changes',
          'type' => 'submit',
          'ignore' => true
        ));
    }

}

?>
