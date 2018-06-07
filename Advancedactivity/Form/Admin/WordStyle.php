<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: WordStyle.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Form_Admin_WordStyle extends Engine_Form
{

  protected $_item;

  public function getItem()
  {
    return $this->_item;
  }

  public function setItem(Core_Model_Item_Abstract $item)
  {
    $this->_item = $item;
    return $this;
  }

  public function init()
  {
    $this
      ->setTitle('Add New Word')
      ->setDescription('You can add a new word and set color for that word and its background as per your choice.');
    $coreSettingsApi = Engine_Api::_()->getApi('settings', 'core');

    $this->addElement('Text', 'title', array(
      'label' => 'Word',
      'description' => "Enter the word which you want to style.",
      'required' => true,
      'class' => 'aaf_admin_word_preview'
    ));

    $this->addElement('Text', 'color', array(
      'label' => 'Select Word\'s Color',
      'description' => 'Select the color for the words.',
      'decorators' => array(array('ViewScript', array(
            'viewScript' => '_namebgcoloreven.tpl',
            'class' => 'form element',
            'name' => 'color',
            'value' => $this->_item ? $this->_item->color : '#00000',
            'label' => 'Select Word\'s Color',
            'description' => 'Select the color for the words.',
            'order' => 98
          ))),
      'value' => $coreSettingsApi->getSetting('advancedactivity_feed_highlighted_word_color', '#ccccce')
    ));
    $this->addElement('Text', 'background_color', array(
      'label' => 'Select Word\'s Background Color',
      'description' => 'Select the background color for the words.',
      'decorators' => array(array('ViewScript', array(
            'viewScript' => '_namebgcoloreven.tpl',
            'class' => 'form element',
            'name' => 'background_color',
            'value' => $this->_item ? $this->_item->background_color : '#FFFFFF',
            'label' => 'Select Word\'s Background Color',
            'description' => 'Select the background color for the words.',
            'order' => 99
          ))),
      'value' => $coreSettingsApi->getSetting('advancedactivity_feed_highlighted_word_background', '#cccccd')
    ));
    $this->addElement('Select', 'style', array(
      'label' => 'Font Style',
      'description' => "Select the font style for the words.",
      'onchange' => '$("title").setStyle("font-style",this.value)',
      'multiOptions' => array('normal' => 'Normal', 'italic' => 'Italic', 'oblique' => 'Oblique'),
      'value' => 'normal',
    ));
    $this->addElement('Radio', 'bg_enabled', array(
      'label' => 'Show Background',
      'description' => "Do you want to show the backgroud color for this word?",
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No'
      ),
      'value' => 0,
    ));
    $animations = array('' => 'None');
    $backgroundAnimations = array('Happy New Year', 'Happy Birthday', 'Merry Christmas', 'Congratulations', 'Happy Easter', 'Happy Thanksgiving');
    foreach( $backgroundAnimations as $value ) {
      $animations['background-' . str_replace(' ', '-', strtolower($value))] = $value . ' (Special)';
    }
    $animationNameList = array("Fly Out","Slow Fly Out","Colorful Fly Out","Fast Fly Out","Wheel","Appear","Bubbles Repel","Bubbles Circle","Colorful Bubbles","Disappear","Repel","Sunrays","Fade In-Out","Bubbles","Fireworks","Vertical Fireworks","Bubbles Fireworks"
                                ,"Colorful Fireworks");
    for( $i = 1; $i <= 18; $i++ ) {
      $animations[$i] = $animationNameList[$i];
    }
    $this->addElement('Select', 'animation', array(
      'label' => 'Animation',
      'description' => "Select the animation type for the above entered word.",
      'multiOptions' => $animations,
      'value' => '',
    ));
    // Add submit button
    $this->addElement('Button', 'submit', array(
      'label' => 'Submit',
      'type' => 'submit',
      'ignore' => true
    ));
  }

}
