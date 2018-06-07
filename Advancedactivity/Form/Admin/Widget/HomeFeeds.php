<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: homeFeeds.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Form_Admin_Widget_HomeFeeds extends Engine_Form
{
  public function init()
  {
    $this->loadDefaultDecorators();
    $this->setAttrib('class', 'global_form_popup global_form global_form_aaf_homefeeds_settings')
      ->setDisableTranslator(true);
    $this->addElement('Text', 'title', array(
      'label' => 'Title',
      'value' => 'What\'s New',
    ));

    $faqlink = "http://www.socialengineaddons.com/page/facebook-application-submission";

    $this->addElement('MultiCheckbox', 'advancedactivity_tabs', array(
      'description' => 'Select the tabs that you want to be available in this block. <br> [Note: The Welcome, Twitter tabs will only work for Member Home Page. Twitter tabs will show the logged-in user\'s  Twitter and Linkedin feeds. It is recommended to place this widget where SocialEngine\'s Core Activity Feed widget is placed.]',
      'multiOptions' => array(
        "welcome" => "Welcome",
        "aaffeed" => "Site Activity Feeds",
        "twitter" => "Twitter Feeds",
      ),
    ));
    $this->addElement('Select', 'showPosts', array(
      'label' => 'Do you want to display "post something", module filters and other options?',
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No'
      ),
      'value' => 1,
    ));
    $this->addElement('Select', 'showGreetings', array(
      'label' => 'Select which type of greeting / announcement you want to display?',
      'multiOptions' => array(
                            'all' => 'All',
                            'userbased' => 'User Based Auto Greeting',
                            'custom' => 'Custom greeting / announcement',
                            'none' => 'None'
                 ),
      'value' => 'all',
    ));
    $this->addElement('Select', 'statusBoxDesign', array(
      'label' => 'Choose the Status (Post) Box design.',
      'multiOptions' => array(
        'activator_icon' => 'Attachment Links with Icon',
        'activator_buttons' => 'Attachment Links with Button in Pop-up',
        'activator_buttons activator_inline_buttons' => 'Attachment Links with Inline Button with status in Pop-up',
        'activator_top' => 'Attachment Links on Top of Status Box',
      ),
      'value' => 'activator_buttons',
      'onchange' => 'showHideAttachmentOption()',
      'decorators' => array(array('ViewScript', array(
                        'viewScript' => 'application/modules/Advancedactivity/views/scripts/_widgetSettingScripts.tpl',
                        )))
    ));
    $this->addElement('Select', 'maxAllowActivator', array(
      'label' => 'Choose the number Of menu options after which you want to show more tab(option).',
      'multiOptions' => array(2 => 2,3 => 3,4 => 4,5 => 5),
      'value' => 2,
    ));
    if( Engine_Api::_()->hasModuleBootstrap('sitealbum') || Engine_Api::_()->hasModuleBootstrap('sitevideo') ) {
      $this->addElement('Select', 'showTabs', array(
        'label' => 'Show Tabs',
        'description' => "Do you want to show various tabs like Add Photo, Create Photo Album and Add Videos in this widget? [Note: This setting only work if you have placed this widget on the Member Home Page and Member Profile Page.]",
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        ),
        'value' => 0,
      ));
    }
    $this->advancedactivity_tabs->getDecorator('description')->setOptions(array('escape' => false, 'placement' => 'prepend'));
    $this->addElement('Select', 'loadByAjax', array(
      'label' => 'AJAX Based Feed Loading On This Pages',
      'description' => "Do you want the feeds on this pages of members and various content to be loaded via AJAX after page load (this will be good for the overall web page loading speed)?",
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No'
      ),
      'value' => 0,
    ));


    $isCommunityAdvModuleEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('communityad');
    $isCommunityAdvIntegrated = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.community.adv', 0);
    $isCampaignAdvIntegrated = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.campaign.adv', 0);
    if( ($isCommunityAdvIntegrated || $isCampaignAdvIntegrated) && $isCommunityAdvModuleEnabled ) {
      $this->addElement('Select', 'integrateCommunityAdv', array(
        'label' => 'Show Advertisment',
        'description' => 'Do you want to display advertisement in between advanced activity feeds.',
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        ),
        'value' => 0,
      ));
    }

    $this->addElement('Select', 'memberPhotoStyle', array(
      'label' => 'Choose the style of the Member Photo in the  feeds.',
      'multiOptions' => array(
        'none' => 'Hide Photo',
        'left' => 'Left Square',
        'center' => 'Center Square',
        'right' => 'Right Square',
        'left_round' => 'Left Round',
        'center_round' => 'Center Round',
        'right_round' => 'Right Round',
      ),
      'value' => 'left',
    ));

//    $this->addElement('Text', 'videowidth', array(
//      'label' => 'Enter the width (in pixels) of video attachment block:',
//      'value' => 0,
//    ));
    $this->addElement('Select', 'pinboardColumn', array(
      'label' => 'Select the number of columns for displaying feeds in the feed layout. [Note: If more then 1 column is selected then feeds will be displayed in pinboard view.]',
      'multiOptions' => array(
        '0' => '1 Column',
        '2' => '2 Columns',
        '3' => '3 Columns',
        '4' => '4 Columns',
        '5' => '5 Columns',
      ),
      'value' => 0,
    ));
//    $this->addElement('Text', 'widthphotoattachment', array(
//      'label' => 'Enter the width (in pixels) of photo attachment block:',
//      'value' => 440,
//    ));

    $this->addElement('Select', 'viewMaxPhoto', array(
      'label' => 'Maximum Photos displayed in Activity Feed',
      'description' => 'Enter the maximum number of photos that you want to display as an attachment in the'
      . ' activity feed when multiple photos are uploaded by a user. Photos exceeding this value can be viewed'
      . ' by clicking the "+" thumbnail at the end of a photo stream in the activity feed.',
      'value' => 8,
      'multiOptions' => array(0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10, 11 => 11, 12 => 12,
      )
    ));

    $this->addElement('Select', 'customPhotoBlock', array(
      'label' => 'Do you want to set the height and width of photos in feeds? If Yes, then below settings of width and height will apply on the photos. If No, then width and height of photos will be automatically set.',
      'description' => '&nbsp;',
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No',
      ),
      'onchange' => 'showHideAafFeedPhotoBlocks()',
      'value' => 0,
    ));
    $this->customPhotoBlock->getDecorator('description')->setOptions(array('escape' => false, 'placement' => 'prepend'));
    $photoUnitsOptions = array(
      'px' => 'px',
      '%' => '%',
    );
    //Photo Block 1
    $this->addElement('Dummy', "photo_block_1", array(
      'label' => "For 1 photo",
    ));
    $this->addElement('Text', "photo_block_1_width", array(
      'label' => "Max width",
      'value' => '500',
      'validators' => array(
        array('GreaterThan', true, array(0)),
        array('Float', true),
      )
    ));
    $this->addElement('Select', "photo_block_1_width_unit", array(
      'multiOptions' => $photoUnitsOptions,
      'value' => 'px',
    ));
    $this->addElement('Text', "photo_block_1_height", array(
      'label' => "Max height (in pixels)",
      'value' => '450',
      'validators' => array(
        array('GreaterThan', true, array(0)),
        array('Int', true),
      )
    ));
    $this->addDisplayGroup(array('photo_block_1', 'photo_block_1_width', 'photo_block_1_width_unit', 'photo_block_1_height'), 'photo_block_1_group', array('disableLoadDefaultDecorators' => true));
    $this->getDisplayGroup('photo_block_1_group')->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'photo_block_1_group', 'class' => 'aaf_feed_photo_blocks'))));

    //Photo Block 2
    $this->addElement('Dummy', "photo_block_2", array(
      'label' => "Width and Height for 2 photos",
    ));
    $this->addElement('Text', "photo_block_2_width", array(
      'label' => "Width",
      'value' => '320',
      'validators' => array(
        array('Float', true),
        array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Select', "photo_block_2_width_unit", array(
      'multiOptions' => $photoUnitsOptions,
      'value' => 'px',
    ));
    $this->addElement('Text', "photo_block_2_height", array(
      'label' => "Height (in pixels)",
      'value' => '300',
      'validators' => array(
        array('GreaterThan', true, array(0)),
        array('Int', true),
      )
    ));
    $this->addDisplayGroup(array('photo_block_2', 'photo_block_2_width', 'photo_block_2_width_unit', 'photo_block_2_height'), 'photo_block_2_group', array('disableLoadDefaultDecorators' => true));
    $this->getDisplayGroup('photo_block_2_group')->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'photo_block_2_group', 'class' => 'aaf_feed_photo_blocks'))));

    //Photo Block 3
    $this->addElement('Dummy', "photo_block_3", array(
      'label' => "Width and Height for 3 photos",
    ));
    $this->addElement('Text', "photo_block_3_width", array(
      'label' => "Width",
      'value' => '320',
      'validators' => array(
        array('Float', true),
        array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Select', "photo_block_3_width_unit", array(
      'multiOptions' => $photoUnitsOptions,
      'value' => 'px',
    ));
    $this->addElement('Text', "photo_block_3_height", array(
      'label' => "Height (in pixels)",
      'value' => '300',
      'validators' => array(
        array('GreaterThan', true, array(0)),
        array('Int', true),
      )
    ));
    $this->addElement('Text', "photo_block_3_small_width", array(
      'label' => "Width",
      'value' => '300',
      'validators' => array(
        array('Float', true),
        array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Select', "photo_block_3_small_width_unit", array(
      'multiOptions' => $photoUnitsOptions,
      'value' => 'px',
    ));
    $this->addElement('Text', "photo_block_3_small_height", array(
      'label' => "Height (in pixels)",
      'value' => '150',
      'validators' => array(
        array('GreaterThan', true, array(0)),
        array('Int', true),
      )
    ));
    $this->addDisplayGroup(array('photo_block_3', 'photo_block_3_width', 'photo_block_3_width_unit', 'photo_block_3_height', 'photo_block_3_small_width', 'photo_block_3_small_width_unit', 'photo_block_3_small_height'), 'photo_block_3_group', array('disableLoadDefaultDecorators' => true));
    $this->getDisplayGroup('photo_block_3_group')->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'photo_block_3_group', 'class' => 'aaf_feed_photo_blocks aaf_feed_photo_blocks_2'))));

    //Photo Block 4
    $this->addElement('Dummy', "photo_block_4", array(
      'label' => "Width and Height for 4 photos",
    ));
    $this->addElement('Text', "photo_block_4_width", array(
      'label' => "Width",
      'value' => '420',
      'validators' => array(
        array('Float', true),
        array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Select', "photo_block_4_width_unit", array(
      'multiOptions' => $photoUnitsOptions,
      'value' => 'px',
    ));
    $this->addElement('Text', "photo_block_4_height", array(
      'label' => "Height (in pixels)",
      'value' => '420',
      'validators' => array(
        array('GreaterThan', true, array(0)),
        array('Int', true),
      )
    ));
    $this->addElement('Text', "photo_block_4_small_width", array(
      'label' => "Width",
      'value' => '170',
      'validators' => array(
        array('Float', true),
        array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Select', "photo_block_4_small_width_unit", array(
      'multiOptions' => $photoUnitsOptions,
      'value' => 'px',
    ));
    $this->addElement('Text', "photo_block_4_small_height", array(
      'label' => "Height (in pixels)",
      'value' => '140',
      'validators' => array(
        array('GreaterThan', true, array(0)),
        array('Int', true),
      )
    ));
    $this->addDisplayGroup(array('photo_block_4', 'photo_block_4_width', 'photo_block_4_width_unit', 'photo_block_4_height', 'photo_block_4_small_width', 'photo_block_4_small_width_unit', 'photo_block_4_small_height'), 'photo_block_4_group', array('disableLoadDefaultDecorators' => true));
    $this->getDisplayGroup('photo_block_4_group')->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'photo_block_4_group', 'class' => 'aaf_feed_photo_blocks aaf_feed_photo_blocks_2'))));

    //Photo Block 5
    $this->addElement('Dummy', "photo_block_5", array(
      'label' => "Width and Height for 5 photos",
    ));
    $this->addElement('Text', "photo_block_5_width", array(
      'label' => "Width",
      'value' => '320',
      'validators' => array(
        array('Float', true),
        array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Select', "photo_block_5_width_unit", array(
      'multiOptions' => $photoUnitsOptions,
      'value' => 'px',
    ));
    $this->addElement('Text', "photo_block_5_height", array(
      'label' => "Height (in pixels)",
      'value' => '300',
      'validators' => array(
        array('GreaterThan', true, array(0)),
        array('Int', true),
      )
    ));
    $this->addElement('Text', "photo_block_5_small_width", array(
      'label' => "Width",
      'value' => '213',
      'validators' => array(
        array('Float', true),
        array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Select', "photo_block_5_small_width_unit", array(
      'multiOptions' => $photoUnitsOptions,
      'value' => 'px',
    ));
    $this->addElement('Text', "photo_block_5_small_height", array(
      'label' => "Height (in pixels)",
      'value' => '150',
      'validators' => array(
        array('GreaterThan', true, array(0)),
        array('Int', true),
      )
    ));
    $this->addDisplayGroup(array('photo_block_5', 'photo_block_5_width', 'photo_block_5_width_unit', 'photo_block_5_height', 'photo_block_5_small_width', 'photo_block_5_small_width_unit', 'photo_block_5_small_height'), 'photo_block_5_group', array('disableLoadDefaultDecorators' => true));
    $this->getDisplayGroup('photo_block_5_group')->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'photo_block_5_group', 'class' => 'aaf_feed_photo_blocks aaf_feed_photo_blocks_2'))));
    //Photo Block 6
    $this->addElement('Dummy', "photo_block_6", array(
      'label' => "Width and Height for 6 photos",
    ));
    $this->addElement('Text', "photo_block_6_width", array(
      'label' => "Width",
      'value' => '300',
      'validators' => array(
        array('Float', true),
        array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Select', "photo_block_6_width_unit", array(
      'multiOptions' => $photoUnitsOptions,
      'value' => 'px',
    ));
    $this->addElement('Text', "photo_block_6_height", array(
      'label' => "Height (in pixels)",
      'value' => '300',
      'validators' => array(
        array('GreaterThan', true, array(0)),
        array('Int', true),
      )
    ));
    $this->addElement('Text', "photo_block_6_small_width", array(
      'label' => "Width",
      'value' => '150',
      'validators' => array(
        array('Float', true),
        array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Select', "photo_block_6_small_width_unit", array(
      'multiOptions' => $photoUnitsOptions,
      'value' => 'px',
    ));
    $this->addElement('Text', "photo_block_6_small_height", array(
      'label' => "Height (in pixels)",
      'value' => '150',
      'validators' => array(
        array('GreaterThan', true, array(0)),
        array('Int', true),
      )
    ));
    $this->addDisplayGroup(array('photo_block_6', 'photo_block_6_width', 'photo_block_6_width_unit', 'photo_block_6_height', 'photo_block_6_small_width', 'photo_block_6_small_width_unit', 'photo_block_6_small_height'), 'photo_block_6_group', array('disableLoadDefaultDecorators' => true));
    $this->getDisplayGroup('photo_block_6_group')->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'photo_block_6_group', 'class' => 'aaf_feed_photo_blocks aaf_feed_photo_blocks_2'))));
    //Photo Block 7
    $this->addElement('Dummy', "photo_block_7", array(
      'label' => "Width and Height for 7 photos",
    ));
    $this->addElement('Text', "photo_block_7_width", array(
      'label' => "Width",
      'value' => '170',
      'validators' => array(
        array('Float', true),
        array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Select', "photo_block_7_width_unit", array(
      'multiOptions' => $photoUnitsOptions,
      'value' => 'px',
    ));
    $this->addElement('Text', "photo_block_7_height", array(
      'label' => "Height (in pixels)",
      'value' => '150',
      'validators' => array(
        array('GreaterThan', true, array(0)),
        array('Int', true),
      )
    ));
    $this->addElement('Text', "photo_block_7_small_width", array(
      'label' => "Width",
      'value' => '157',
      'validators' => array(
        array('Float', true),
        array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Select', "photo_block_7_small_width_unit", array(
      'multiOptions' => $photoUnitsOptions,
      'value' => 'px',
    ));
    $this->addElement('Text', "photo_block_7_small_height", array(
      'label' => "Height (in pixels)",
      'value' => '157',
      'validators' => array(
        array('GreaterThan', true, array(0)),
        array('Int', true),
      )
    ));
    $this->addDisplayGroup(array('photo_block_7', 'photo_block_7_width', 'photo_block_7_width_unit', 'photo_block_7_height', 'photo_block_7_small_width', 'photo_block_7_small_width_unit', 'photo_block_7_small_height'), 'photo_block_7_group', array('disableLoadDefaultDecorators' => true));
    $this->getDisplayGroup('photo_block_7_group')->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'photo_block_7_group', 'class' => 'aaf_feed_photo_blocks aaf_feed_photo_blocks_2'))));

    //Photo Block 8
    $this->addElement('Dummy', "photo_block_8", array(
      'label' => "Width and Height for 8 photos",
    ));
    $this->addElement('Text', "photo_block_8_width", array(
      'label' => "Width",
      'value' => '150',
      'validators' => array(
        array('Float', true),
        array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Select', "photo_block_8_width_unit", array(
      'multiOptions' => $photoUnitsOptions,
      'value' => 'px',
    ));
    $this->addElement('Text', "photo_block_8_height", array(
      'label' => "Height (in pixels)",
      'value' => '150',
      'validators' => array(
        array('GreaterThan', true, array(0)),
        array('Int', true),
      )
    ));
    $this->addDisplayGroup(array('photo_block_8', 'photo_block_8_width', 'photo_block_8_width_unit', 'photo_block_8_height'), 'photo_block_8_group', array('disableLoadDefaultDecorators' => true));
    $this->getDisplayGroup('photo_block_8_group')->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'photo_block_8_group', 'class' => 'aaf_feed_photo_blocks'))));

    //Photo Block 9
    $this->addElement('Dummy', "photo_block_9", array(
      'label' => "Width and Height for  9 photos",
    ));
    $this->addElement('Text', "photo_block_9_width", array(
      'label' => "Width",
      'value' => '150',
      'validators' => array(
        array('Float', true),
        array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Select', "photo_block_9_width_unit", array(
      'multiOptions' => $photoUnitsOptions,
      'value' => 'px',
    ));
    $this->addElement('Text', "photo_block_9_height", array(
      'label' => "Height (in pixels)",
      'value' => '150',
      'validators' => array(
        array('GreaterThan', true, array(0)),
        array('Int', true),
      )
    ));
    $this->addDisplayGroup(array('photo_block_9', 'photo_block_9_width', 'photo_block_9_width_unit', 'photo_block_9_height'), 'photo_block_9_group', array('disableLoadDefaultDecorators' => true));
    $this->getDisplayGroup('photo_block_9_group')->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'photo_block_9_group', 'class' => 'aaf_feed_photo_blocks'))));

    //Photo Block 10
    $this->addElement('Dummy', "photo_block_10", array(
      'label' => "Width and Height for 10 photos",
    ));
    $this->addElement('Text', "photo_block_10_width", array(
      'label' => "Width",
      'value' => '120',
      'validators' => array(
        array('Float', true),
        array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Select', "photo_block_10_width_unit", array(
      'multiOptions' => $photoUnitsOptions,
      'value' => 'px',
    ));
    $this->addElement('Text', "photo_block_10_height", array(
      'label' => "Height (in pixels)",
      'value' => '120',
      'validators' => array(
        array('GreaterThan', true, array(0)),
        array('Int', true),
      )
    ));
    $this->addDisplayGroup(array('photo_block_10', 'photo_block_10_width', 'photo_block_10_width_unit', 'photo_block_10_height'), 'photo_block_10_group', array('disableLoadDefaultDecorators' => true));
    $this->getDisplayGroup('photo_block_10_group')->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'photo_block_10_group', 'class' => 'aaf_feed_photo_blocks'))));
  }

}
