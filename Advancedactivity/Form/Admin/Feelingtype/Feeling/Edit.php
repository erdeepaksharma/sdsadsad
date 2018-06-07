<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Edit.php 6590 2016-07-07 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */

class Advancedactivity_Form_Admin_Feelingtype_Feeling_Edit extends Engine_Form {
  public function init() {

    // Init form
    $this
      ->setTitle('Edit Feeling Words')
      ->setDescription('Edit the search words of this sticker.')
      ->setAttrib('class', 'global_form_popup')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
    ;


    // Init name
    $this->addElement('Text', 'title', array(
      'label' => 'Search Words',
      'Description' => 'Separate words with commas.',
      'maxlength' => '40',
      'allowEmpty' => false,
      'required' => true,
      'filters' => array(
        new Engine_Filter_HtmlSpecialChars(),
        'StripTags',
        new Engine_Filter_Censor(),
        new Engine_Filter_StringLength(array('max' => '63')),
      )
    ));
    $this->title->getDecorator("Description")->setOption("placement", "append");
    $this->addElement('Button', 'submit', array(
      'label' => 'Save',
      'type' => 'submit',
      'order' => 999
    ));
  }
}
