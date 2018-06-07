<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: PostSettings.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Form_Admin_PostSettings extends Engine_Form {

    public function init() {

        $this
                ->setTitle('Post Settings')
                ->setDescription('Below settings will govern various properties related to status updates on your website.');
        $coreSettingsApi = Engine_Api::_()->getApi('settings', 'core');


        $settings = $coreSettingsApi;


        $this->addElement('Radio', 'advancedactivity_post_byajax', array(
            'label' => 'Status Update via AJAX',
            'description' => "Do you want to enable posting of status updates via AJAX? 
                              (Note: Select 'No' if you have either enabled Janrain Integration with 'Publish' feature or are using Social DNA Publisher plugin. Status updates from LinkedIn tab will always be via AJAX, even if you select No over here.)",
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'value' => $coreSettingsApi->getSetting('advancedactivity.post.byajax', 1),
        ));

        $composerLink = 'http://www.socialengineaddons.com/socialengine-directory-pages-plugin';
        //WELCOME ICON WORK
        $pageitem = "<a href= '" . $composerLink . "' target='_blank'>Directory Items / Pages</a>";
        $composerOptionDes = sprintf("Choose the options from below that you want to be enabled for the Status Update Box in site activity feeds. (Additionally, using the “@” character with name, users will be able to tag their Friends and site’s %s in their updates on Member Homepage and User Profiles. Tagged friends and Page Admins of tagged %s will get notification updates.)", $pageitem, $pageitem);



        // VALUE FOR AJAX LAYOUT
        $this->addElement('MultiCheckbox', 'advancedactivity_composer_options', array(
            'description' => $composerOptionDes,
            'label' => 'Status Box Options',
            'multiOptions' => array(
                "withtags" => "Add Friends (Users will be able to add friends in their updates. These friends will appear in the updates with a “with” text. Added friends will get a notification update. If enabled, this will be available on Member Homepage and User Profiles.)",
                "emotions" => "Emoticons (Users will see an \"Insert Emoticons\" icon in the updates posting box and will be able to insert attractive Emoticons / Smileys in their posts. Symbols for smileys entered in comments of activity feeds will also be displayed as their emoticons. If enabled, this will be available at all locations of the status box and comments on feeds.)",
                "userprivacy" => 'Post Sharing Privacy (Users will be able to choose the people and networks with whom they want to share their updates. The available sharing privacy options come pre-configured with "Everyone", "Friends & Networks" and "Friends Only", and Friend Lists created by users like "Family", "Work Colleagues", etc are also shown. You can also enable users to share their updates within certain Network(s) by choosing the desired option for \'Networks Post Sharing Privacy\' field below. Additionally, users can also create custom sharing lists using their Friend Lists and Networks. If enabled, this will be available on Member Homepage and User Profiles.)',
                "webcam" => "Add Photo Using Webcam (Users will be able to add photo using their webcam in their updates. If enabled, this will be available in all locations of the status box with the 'Select File' option.)",
                "postTarget" => "Post Targeting (Allow users to be able to target their post to specific audience based on gender and age.)",
                "schedulePost" => "Post Scheduling (Allow your users to be able to schedule their post.)"
            ),
            'value' => $coreSettingsApi->getSetting('advancedactivity.composer.options', array("withtags", "emotions", "userprivacy", "webcam", "postTarget","schedulePost")),
        ));
        $this->advancedactivity_composer_options
                ->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' =>
                    false));

        $composerList = Engine_Api::_()->advancedactivity()->getComposerMenuList(false);
        $composerValue = $coreSettingsApi->getSetting('advancedactivity.composer.menuoptions', Engine_Api::_()->advancedactivity()->getComposerMenuList());
        ksort($composerValue);
        if(!empty($composerList['bannerXXXadvancedactivity']) && in_array('bannerXXXadvancedactivity', $composerValue)){
            $bannerTitle = $composerList['bannerXXXadvancedactivity'];
            unset($composerValue['bannerXXXadvancedactivity']);
            unset($composerList['bannerXXXadvancedactivity']);
            $composerValue = array_merge(array('bannerXXXadvancedactivity'),$composerValue);
            $composerList = array_merge(array('bannerXXXadvancedactivity' => $bannerTitle),$composerList);
        }
        if(!empty($composerValue)){
            $composerList = array_merge(array_flip($composerValue), $composerList);
        }  
        $this->addElement('MultiCheckbox', 'advancedactivity_composer_menuoptions', array(
            'description' => 'Choose the attachment menus from below that you want to be enabled in the Status Update Box in site activity feeds. Drag the attachment menu up / down to change its sequence in Status Update Box.',
            'label' => 'Status Box Attachment Menus',
            'multiOptions' => $composerList,
            'value' => $composerValue
        ));


        $content = $coreSettingsApi->getSetting('activity.content', 'everyone');
        $tip = null;
        if ($content == 'friends') {
            $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
            $link = $view->url(array("module" => "activity", "controller" => "settings"), "admin_default", true);
            $tip = sprintf("<div class='tip'> <span>Note: In Activity Feed Settings, you have chosen ‘My Friends’ for the ‘Feed Content’ field. Please %s, either to choose ‘All Members’ or ‘My Friends & Networks’ for this field.</span></div>", "<a href='" . $link . "'>click here</a>");
        }
        // VALUE FOR ENABLE /DISABLE Proximity Search
        $this->addElement('Radio', 'advancedactivity_networklist_privacy', array(
            'label' => 'Networks Post Sharing Privacy',
            'description' => 'Do you want to enable users to share their updates with desired Networks? (If yes, choose an appropriate value below.)' . $tip,
            'multiOptions' => array(
                2 => 'Yes, enable users to share their updates with all networks.',
                1 => 'Yes, enable users to share their updates only with the networks joined by them.',
                0 => 'No'
            ),
            'value' => $settings->getSetting('advancedactivity.networklist.privacy', 0),
        ));
        $this->getElement('advancedactivity_networklist_privacy')->getDecorator('Description')->setOptions(array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
        // Searchable Media
        $this->addElement('Radio', 'advancedactivity_post_searchable', array(
            'label' => 'Shared Media Searchability',
            'description' => "Do you want the media that is shared from the Status Update Box to be made searchable?",
            'multiOptions' => array(
                1 => "Yes (Note: In this case, the shared media will also appear on the browse page of the shared content type. Thus, even though privacy chosen during sharing [like Friends, Family, etc] will work on the main content page for viewing content, the shared media content will be visible on the browse page because you have made it searchable.)"
                ,
                0 => 'No'
            ),
            'value' => $coreSettingsApi->getSetting('advancedactivity.post.searchable', 0),
        ));
        $multiOptions = array('friends' => 'Friends');
        $include = array('sitepage', 'sitebusiness', 'sitegroup', 'sitestore', 'list', 'group', 'event');
        $module_table = Engine_Api::_()->getDbTable('modules', 'core');
        $module_name = $module_table->info('name');
        $select = $module_table->select()
                ->from($module_name, array('name', 'title'))
                ->where($module_name . '.type =?', 'extra')
                ->where($module_name . '.name in(?)', $include)
                ->where($module_name . '.enabled =?', 1);

        $contentModule = $select->query()->fetchAll();
        $include[] = 'friends';
        foreach ($contentModule as $module) {
            $multiOptions[$module['name']] = $module['title'];
        }
        $this->addElement('MultiCheckbox', 'aaf_tagging_module', array(
            'label' => 'Tag Contents',
            'description' => "Which type of content should be tagged?",
            'multiOptions' => $multiOptions,
            'value' => $coreSettingsApi->getSetting('aaf.tagging.module', $include)
        ));
        $contentModuleArray = Engine_Api::_()->seaocore()->getContentModule();
        if( !empty($contentModuleArray) ) {
            $this->addElement('Multiselect', 'aaf_allowed_buysell_content', array(
              'label' => 'Enable Sell Something for Content',
              'description' => "Select the content type for which you want to allow ‘Sell Something’ feature.",
              'allowEmpty' => true,
              'multiOptions' => array_merge(array('user' => 'User[Member Home Page & User itself profile.]'),array_filter($contentModuleArray)),
              'value' => $coreSettingsApi->getSetting('aaf.allowed.buysell.content', array('user'))
            ));
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
