<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Map.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Admin_Profilemaps_Map extends Engine_Form {

  protected $_countChieldMapping;

  public function getCountChieldMapping() {
    return $this->_countChieldMapping;
  }

  public function setCountChieldMapping($value) {
    $this->_countChieldMapping = $value;
    return $this;
  }

  public function init() {

    $this->setMethod('post')
            ->setTitle("Select Profile Type")
            ->setAttrib('class', 'global_form_box')
            ->setDescription("After selecting a profile type, if you click on 'Save', then the already created listings of this category will also be associated with this profile type.");

    $getCountChieldMapping = $this->getCountChieldMapping();
    if (!empty($getCountChieldMapping)) {

      $category_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('category_id', null);
      $category = Engine_Api::_()->getItem('sitereview_category', $category_id);
      if ($category->cat_dependency == 0 && $category->subcat_dependency == 0) {
        $description = Zend_Registry::get('Zend_Translate')->_("<div class='tip'><span>Note: It seems that sub-categories of this category have been already mapped. Thus, if you add this mapping, then earlier listings associated with the mapped sub-categories will loose their custom profile data.</span></div>");
      } else {
        $description = Zend_Registry::get('Zend_Translate')->_("<div class='tip'><span>Note: It seems that 3rd level categories of this sub-category have been already mapped. Thus, if you add this mapping, then earlier listings associated with the mapped 3rd level categories will loose their custom profile data.</span></div>");
      }

      $description = sprintf($description);

      $this->addElement('Dummy', 'warning_message', array(
          'description' => $description,
      ));
      $this->warning_message->addDecorator('Description', array('placement' => 'APPEND', 'warning_message' => 'label', 'class' => 'null', 'escape' => false, 'for' => 'warning_message'));
    }

    $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('sitereview_listing');
    if (count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type') {
      $profileTypeField = $topStructure[0]->getChild();
      $options = $profileTypeField->getOptions();
      if (count($options) > 0) {
        $options = $profileTypeField->getElementParams('sitereview_listing');
        unset($options['options']['order']);
        unset($options['options']['multiOptions']['0']);
        $this->addElement('Select', 'profile_type', array_merge($options['options'], array(
                    'label' => 'Profile Type',
                    'required' => true,
                    'allowEmpty' => false,
                )));
      } else if (count($options) == 1) {
        $this->addElement('Hidden', 'profile_type', array(
           'order' => 975,
            'value' => $options[0]->option_id
        ));
      }
    }

    $this->addElement('Button', 'yes_button', array(
        'label' => 'Save',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
        'prependText' => ' or ',
        'href' => '',
        'onClick' => 'javascript:parent.Smoothbox.close();',
        'decorators' => array(
            'ViewHelper'
        )
    ));
    $this->addDisplayGroup(array('yes_button', 'cancel'), 'buttons');
    $button_group = $this->getDisplayGroup('buttons');
  }

}
