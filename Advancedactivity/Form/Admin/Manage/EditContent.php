<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: EditContent.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/

class Advancedactivity_Form_Admin_Manage_EditContent extends Engine_Form {

  public function init() {

    $this
            ->setTitle('Edit Content Type')
            ->setDescription('Use the form below to edit the content type for enabling users to filter activity feeds over it.');


    $this->addElement('Text', 'resource_title', array(
        'label' => 'Content Title',
        'description' => 'Enter the content title for which you use this module. Ex: You may use the Documents module for ‘Tutorials’ on your community.',
        'required' => true
    ));

    $this->addElement('Checkbox', 'content_tab', array(
        'description' => 'Filtering Activity Feeds',
        'label' => 'Enable filtering of activity feeds by users for this content type.',
        'value' => 1
    ));

    // Element: execute
    $this->addElement('Button', 'execute', array(
        'label' => 'Save Settings',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper'),
    ));

    // Element: cancel
    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'prependText' => ' or ',
        'ignore' => true,
        'link' => true,
        'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'custom-lists')),
        'decorators' => array('ViewHelper'),
    ));

    // DisplayGroup: buttons
    $this->addDisplayGroup(array('execute', 'cancel'), 'buttons', array(
        'decorators' => array(
            'FormElements',
            'DivDivDivWrapper',
        )
    ));
  }

}

?>