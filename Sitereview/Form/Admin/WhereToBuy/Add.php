<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Add.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Admin_WhereToBuy_Add extends Engine_Form {

  public function init() {

    $this->setTitle("Add New 'Where to Buy'")
            ->setDescription('Below, add a new e-commerce site to your \'Where to Buy\' options.');

    $this->addElement('Text', 'title', array(
        'label' => "Title",
        'description' => 'Enter the name of the e-commerce site.',
        'required' => true,
    ));
    $this->addElement('file', 'photo', array(
        'label' => "Icon",
        'description' => "Upload icon for the e-commerce site. (Note: The recommended dimension for the icon is: 30 x 100 pixels.)",
    ));

    $this->addElement('Button', 'submit', array(
        'label' => 'Submit',
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
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    $button_group = $this->getDisplayGroup('buttons');
  }

}