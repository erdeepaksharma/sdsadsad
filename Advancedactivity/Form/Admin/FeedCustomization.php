<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: FeedCustomization.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Form_Admin_FeedCustomization extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Feed Decorations Settings')
      ->setDescription('Below you can configure the font size of the text displaying in the status updates. This will be useful in highlighting the short status updates.');
    $coreSettingsApi = Engine_Api::_()->getApi('settings', 'core');

    $this->addElement('Text', 'advancedactivity_feed_char_length', array(
      'label' => 'Character Length',
      'description' => "Enter the length of character for the status update which you want to highlight from other status updates. Status updates which comprises of less than or equal characters defined in the textbox will appear in the font size which you have saved below.
                        [Note: This setting will not work for ‘0’ characters.]",
      'value' => $coreSettingsApi->getSetting('advancedactivity.feed.char.length', 50),
    ));
    $this->addElement('Text', 'advancedactivity_feed_font_size', array(
      'label' => 'Font Size',
      'description' => "Enter the font size for status updates to make them stand out from other status updates.",
      'value' => $coreSettingsApi->getSetting('advancedactivity.feed.font.size', 30),
    ));
    $this->addElement('Text', 'advancedactivity_feed_font_color', array(
      'label' => 'Font Color',
      'description' => "Enter the font color for status updates to make them stand out from other status updates.",
      'decorators' => array(array('ViewScript', array(
                        'viewScript' => '_namebgcoloreven.tpl',
                        'class' => 'form element',
                        'name' => 'advancedactivity_feed_font_color',
                        'value' => $coreSettingsApi->getSetting('advancedactivity.feed.font.color', '#000'),
                        'label' =>'Font Color',
                        'description' => 'Enter the font color for status updates to make them stand out from other status updates.',
                        'order' =>99
                    ))),
      'value' => $coreSettingsApi->getSetting('advancedactivity.feed.font.color', '#000'),
    ));
    $this->addElement('Select', 'advancedactivity_feed_font_style', array(
      'label' => 'Font Style',
      'description' => "Select the font style for status updates to make them stand out from other status updates.",
      'multiOptions' => array('normal'=>'Normal','italic'=>'Italic','oblique' => 'Oblique'),
      'value' => $coreSettingsApi->getSetting('advancedactivity.feed.font.style', 'normal'),
    ));
    $this->addElement('Text', 'advancedactivity_banner_feed_length', array(
      'label' => 'Character Limit for Text on Background Image',
      'description' => "Enter the character length for the text displaying on the feed background image.",
      'value' => $coreSettingsApi->getSetting('advancedactivity.banner.feed.length', 100),
    ));
    $this->addElement('Text', 'advancedactivity_banner_count', array(
      'label' => 'Background Images Limit',
      'description' => "Enter the count of background images to be displayed in status box.",
      'value' => $coreSettingsApi->getSetting('advancedactivity.banner.count', 10),
    ));
    $this->addElement('Select', 'advancedactivity_feed_banner_order', array(
      'label' => 'Background Images Order',
      'description' => "Select the order for the background images to be displayed in status box. [Note: Highlighted background will always append in the beginning.]",
      'multiOptions' => array('random'=>'Random Order','sequence'=>'Saved Order'),
      'value' => $coreSettingsApi->getSetting('advancedactivity.feed.banner.order', 'random'),
    ));
    // Add submit button
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }

}
