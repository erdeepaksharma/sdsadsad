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

class Advancedactivity_Form_Admin_Greeting_Edit extends Advancedactivity_Form_Admin_Greeting_Create {
  public function init() {

    // Init form
    $this
      ->setTitle('Edit Greeting / Announcement')
      ->setDescription('Here,you can edit greeting / announcement / announcement. You can make this greeting / announcement available to users by default. 
')
      ->setAttrib('class', 'global_form')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
    ;

    parent::init();
    
  }

   
}
