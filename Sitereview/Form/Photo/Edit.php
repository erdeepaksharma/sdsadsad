<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Edit.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Photo_Edit extends Engine_Form {

  public function init() {
    
    $this->setTitle('Edit Photo');

    $this->addElement('Text', 'title', array(
        'label' => 'Title',
         'filters' => array(
                            'StripTags',
                         new Engine_Filter_Censor(),
                         ),
    ));

    $this->addElement('Textarea', 'description', array(
        'label' => 'Description',
         'filters' => array(
                            'StripTags',
                         new Engine_Filter_Censor(),
                         ),
    ));

    $this->addElement('Button', 'submit', array(
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper'),
        'label' => 'Save Changes',
    ));

    $this->addElement('Cancel', 'cancel', array(
        'prependText' => ' or ',
        'label' => 'cancel',
        'link' => true,
        'href' => '',
        'onclick' => 'parent.Smoothbox.close();',
        'decorators' => array(
            'ViewHelper'
        ),
    ));

    $this->addDisplayGroup(array(
        'submit',
        'cancel'
            ), 'buttons');
  }

}