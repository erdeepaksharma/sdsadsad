<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: CustomBlockCreate.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Form_Admin_CustomBlockCreate extends Engine_Form {

  public function init() {
    // Conditions: When click on 'edit' from the admin-help & learnmore-manage page for showing prefields for selected ID.
    $customblock_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('customblock_id', null);
    if (empty($customblock_id)) {
      $this->setTitle('Create New Custom Block')->setDescription("Create a new custom block here, which will be shown in Welcome Tab.");
    } else {
      $this->setTitle('Edit Custom Block')->setDescription("Edit the custom block here, which will be shown in Welcome Tab.");
    }

    $this->addElement('Text', 'title', array(
        'label' => 'Title',
        'description' => 'Title of the custom block.',
        'required' => true
    ));


    $this->addElement('Radio', 'limitation', array(
        'label' => 'Display Limitation',
        'description' => 'Limitation for the custom block view.',
        'multiOptions' => array(
            0 => 'None, Always show this block to users in the Welcome Tab.',
            2 => 'Number of days since signup. (Below you will be able to enter the value.)',
            1 => 'Number of friends. (Below you will be able to enter the value.)'
        ),
        'onclick' => "getlimitation(this.value)",
        'value' => 0,
    ));

    $this->addElement('Text', 'limitation_value', array(
        'label' => 'Number of friends',
        'maxlength' => '4',
        // 'onclick' => "validateTitleOnClick()",
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        ),
    ));


    $this->addElement('Dummy', 'text_flag', array(
        'label' => 'Language Support',
    ));

    $this->addElement('Textarea', 'text_description', array(
        'label' => 'Content',
        'description' => '',
    ));

    $this->addElement('TinyMce', 'description', array(
        'label' => 'Content',
        'description' => '',
        'attribs' => array('rows' => 24, 'cols' => 80, 'style' => 'width:200px; max-width:200px; height:120px;'),
        'allowEmpty' => false,
        'filters' => array(
            new Engine_Filter_Html(),
            new Engine_Filter_Censor()),
        'editorOptions' => Engine_Api::_()->seaocore()->tinymceEditorOptions(),
    ));


    //PREPARE LEVELS
    $levels = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll();
    $levelKey = array();
    foreach ($levels as $level) {
      $levels_prepared[$level->getIdentity()] = $level->getTitle();
      $levelKey[] = $level->getIdentity();
    }
    reset($levels_prepared);

    $this->addElement('Multiselect', 'levels', array(
        'label' => 'Member Levels',
        'description' => 'Specify which member levels will be shown this custom block. To show this block to all member levels, leave them all selected. Use CTRL-click to select or deselect multiple levels.',
        'multiOptions' => $levels_prepared,
        'value' => $levelKey,
    ));

    //PREPARE NETWORKS
    $networks = Engine_Api::_()->getDbtable('networks', 'network')->fetchAll();
    $networkKey = array();
    if (count($networks) > 0) {
      foreach ($networks as $network) {
        $networks_prepared[$network->getIdentity()] = $network->getTitle();
        $networkKey[] = $network->getIdentity();
      }
      reset($networks_prepared);

      $this->addElement('Multiselect', 'networks', array(
          'label' => 'Networks',
          'description' => 'Specify which networks will be shown this custom block. To show this block to all networks, leave them all selected. Use CTRL-click to select or deselect multiple networks.',
          'multiOptions' => $networks_prepared,
          'value' => $networkKey
      ));
    }

    $this->addElement('Hidden', 'temp_limitation', array(
        'value' => '',
        'order' => 800
    ));

    $this->addElement('Hidden', 'temp_limitation_value', array(
        'value' => '',
        'order' => 801
    ));

    $this->addElement('Hidden', 'flag', array(
        'value' => '',
        'order' => 802
    ));


    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));
  }

}

?>