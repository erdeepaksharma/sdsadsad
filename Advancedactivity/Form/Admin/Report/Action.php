<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: Action.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/

class Advancedactivity_Form_Admin_Report_Action extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Take Action')
      ->setDescription('What would you like to do for this report?')
      ->setAction($_SERVER['REQUEST_URI']);

    $this->addElement('Checkbox', 'action', array(
      'label' => 'Delete Activity Feed?',
      'value' => 0,
    ));
    $this->getElement('action')->removeDecorator('Description');

    $this->addElement('Checkbox', 'action_content', array(
      'label' => 'Delete Activity Feed and Respective Content?',
      'value' => 0,
    ));
    $this->getElement('action_content')->removeDecorator('Description');


    $this->addElement('Checkbox', 'dismiss', array(
      'label' => 'Dismiss Report?',
      'value' => 1,
    ));
    $this->getElement('dismiss')->removeDecorator('Description');

    $this->addElement('Hash', 'token');

    $this->addElement('Button', 'execute', array(
      'type' => 'submit',
      'label' => 'Submit',
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    $this->addElement('Cancel', 'cancel', array(
      'link' => true,
      'prependText' => ' or ',
      'label' => 'cancel',
      'href' => 'javascript:parent.Smoothbox.close();',
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    $this->addDisplayGroup(array('execute', 'cancel'), 'buttons');
  }
}