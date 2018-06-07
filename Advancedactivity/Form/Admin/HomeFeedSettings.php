<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: HomeFeedSettings.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Form_Admin_HomeFeedSettings extends Engine_Form {

    public function init() {

        $this
          ->setTitle('Home Feed Settings')
          ->setDescription('Below settings will govern various properties related to status update box placed on Member Home page of your website.');
        $coreSettingsApi = Engine_Api::_()->getApi('settings', 'core');


        $settings = $coreSettingsApi;
        $this->addElement('Text', 'advancedactivity_sitetabtitle', array(
          'label' => 'Site Feeds Tab Title',
          'description' => "Enter the title for the tab that displays site activity feeds in the Advanced Activity Feeds.",
          'value' => $settings->getSetting('advancedactivity.sitetabtitle', "What's New!"),
        ));

        $this->addElement('Radio', 'advancedactivity_tabtype', array(
          'label' => 'Tab Types',
          'description' => 'Select the design type for the tabs in Advanced Activity Feeds. (These tabs enable users to switch between Welcome tab, feeds from your website and feeds from Facebook, Twitter and LinkedIn. Below, you will be able to choose the icon for your site\'s tab. In the Advanced Activity Feeds widget, for any placement, you can choose the tabs/sections to be available via the Edit Settings popup for the widget. The tabs are visible only if more than 1 sections are selected. On Content Profile/View pages, the Welcome, Facebook, Twitter and LinkedIn tabs will not be shown even if they are enabled and tabs will not appear there.)',
          'multiOptions' => array(
            1 => 'Icon Only',
            3 => 'Icon and Title',
            2 => 'Title Only'
          ),
          'onclick' => 'showiconOptions(this.value)',
          'value' => $settings->getSetting('advancedactivity.tabtype', 3),
        ));

        // Get available files (Icon for activity Feed).
        $logoOptions = array('application/modules/Advancedactivity/externals/images/web.png' => 'Default Icon');
        $imageExtensions = array('gif', 'jpg', 'jpeg', 'png');
        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;

        $it = new DirectoryIterator(APPLICATION_PATH . '/public/admin/');
        foreach ($it as $file) {
            if ($file->isDot() || !$file->isFile())
                continue;
            $basename = basename($file->getFilename());
            if (!($pos = strrpos($basename, '.')))
                continue;
            $ext = strtolower(ltrim(substr($basename, $pos), '.'));
            if (!in_array($ext, $imageExtensions))
                continue;
            $logoOptions['public/admin/' . $basename] = $basename;
        }

        $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_("You have not
                           uploaded an image for site logo. Please upload an image.") . "</span></div>";


        $URL = $view->baseUrl() . "/admin/files";
        $click = '<a href="' . $URL . '" target="_blank">over here</a>';
        $customBlocks = sprintf("Upload a small icon for your website %s. The ideal dimensions of this icon should be: 16 X 16 px. (This icon will be shown for your site's activity feeds tab in Advanced Activity Feeds. Once you upload a new icon at the link mentioned, then refresh this page to see its preview below after selection.)", $click);

        if (!empty($logoOptions)) {
            $this->addElement('Select', 'advancedactivity_icon', array(
              'label' => 'Choose Small Site Icon',
              'description' => $customBlocks,
              'multiOptions' => $logoOptions,
              'onchange' => "updateTextFields(this.value)",
              'value' => $settings->getSetting('advancedactivity.icon', ''),
            ));
            $this->getElement('advancedactivity_icon')->getDecorator('Description')->setOptions(array('placement' =>
              'PREPEND', 'escape' => false));
        }
        $logo_photo = $coreSettingsApi->getSetting('advancedactivity_icon', 'application/modules/Advancedactivity/externals/images/web.png');
        if (!empty($logo_photo)) {

            $photoName = $view->baseUrl() . '/' . $logo_photo;
            $description = "<img src='$photoName' width='20' height='20'/>";
        }
        //VALUE FOR LOGO PREVIEW.
        $this->addElement('Dummy', 'logo_photo_preview', array(
          'label' => 'Site Icon Preview',
          'description' => $description,
        ));
        $this->logo_photo_preview
          ->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

       // Get available files (Icon for activity Feed).
        $logoOptions1 = array('application/modules/Advancedactivity/externals/images/welcome-icon.png' =>
          'Default Icon');
        $imageExtensions = array('gif', 'jpg', 'jpeg', 'png');
        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;

        $it = new DirectoryIterator(APPLICATION_PATH . '/public/admin/');
        foreach ($it as $file) {
            if ($file->isDot() || !$file->isFile())
                continue;
            $basename = basename($file->getFilename());
            if (!($pos = strrpos($basename, '.')))
                continue;
            $ext = strtolower(ltrim(substr($basename, $pos), '.'));
            if (!in_array($ext, $imageExtensions))
                continue;
            $logoOptions1['public/admin/' . $basename] = $basename;
        }

        //WELCOME ICON WORK
        $click = '<a href="' . $URL . '" target="_blank">over here</a>';
        $customBlocks = sprintf('Upload a small icon for the Welcome Tab %s. The ideal dimensions of this icon should be: 16 X 16 px. (Once you upload a new icon at the link mentioned, then refresh this page to see its preview below after selection.)', $click);

        if (!empty($logoOptions1)) {
            $this->addElement('Select', 'advancedactivity_icon1', array(
              'label' => 'Choose Welcome Tab Icon',
              'description' => $customBlocks,
              'multiOptions' => $logoOptions1,
              'onchange' => "updateTextFields1(this.value)",
              'value' => $settings->getSetting('advancedactivity.icon1', ''),
            ));

            $this->getElement('advancedactivity_icon1')->getDecorator('Description')->setOptions(array('placement'
              => 'PREPEND', 'escape' => false));
        }
        $logo_photo1 = $coreSettingsApi->getSetting('advancedactivity_icon1', 'application/modules/Advancedactivity/externals/images/welcome-icon.png');
        if (!empty($logo_photo1)) {

            $photoName1 = $view->baseUrl() . '/' . $logo_photo1;
            $description1 = "<img src='$photoName1' width='20' height='20'/>";
        }
        //VALUE FOR LOGO PREVIEW.
        $this->addElement('Dummy', 'logo_photo_preview1', array(
          'label' => 'Welcome Tab Icon Preview',
          'description' => $description1,
        ));
        $this->logo_photo_preview1
          ->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' =>
            false));
        //WELCOME ICON WORK
        $this->addElement('Select', 'advancedactivity_update_frequency', array(
               'label' => 'Update Frequency for Twitter Feeds',
               'description' => 'This application connects to the respective third-party (using AJAX) after regular intervals to check if there are any new updates to the corresponding activity feed. How often do you want this process to occur? A shorter amount of time will consume more server resources. If your server is experiencing slowdown issues, try lowering the frequency the application checks for updates.',
               'value' => $coreSettingsApi->getSetting('advancedactivity.update.frequency', 120000),
               'multiOptions' => array(
                   60000 => '1 minute',
                   120000 => "2 minutes",
                   180000 => "3 minutes",
                   0 => 'Never'
               )
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
