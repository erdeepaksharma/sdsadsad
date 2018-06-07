<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Create.php 6590 2016-07-07 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */

class Advancedactivity_Form_Admin_Link_Create extends Engine_Form {
  public function init() {

    // Init form
    $this
      ->setTitle('Add New Link')
      ->setDescription('Here, add new link for your users. You can make this links available to users by default. 
')
      ->setAttrib('id', 'form-upload')
      ->setAttrib('name', 'admin_collection')
      ->setAttrib('enctype', 'multipart/form-data')
      ->setAttrib('class', 'global_form')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
    ;


    // Init name
    $this->addElement('Text', 'title', array(
      'label' => 'Caption',
      'allowEmpty' => false,
      'required' => true,
      'filters' => array(
        new Engine_Filter_HtmlSpecialChars(),
        'StripTags',
        new Engine_Filter_Censor(),
       
      )
    ));
     $this->addElement('Text', 'icon_path', array(
      'label' => 'Type',
      'allowEmpty' => false,
      'required' => true,
       
    ));
    
    $this->addElement('Radio', 'enabled', array(
            'label' => 'Enable',
            'description' => 'Do you want to enable link to share?',
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'value' => 1,
        ));
   
   
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Submit',
      'type' => 'submit',
      'order' => 999
    ));
  }

   
}
