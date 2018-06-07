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

class Advancedactivity_Form_Admin_Feelingtype_Edit extends Advancedactivity_Form_Admin_Feelingtype_Create {
  public function init() {

    parent::init();
    // Init form
    $this
      ->setTitle('Edit Feeling Type')
      ->setDescription('Below, you can edit the feeling of feeling type information.')
      ->setAttrib('name', 'admin_edit_feelingtype')
      ->setAttrib('enctype', 'multipart/form-data')
      ->setAttrib('class', 'global_form')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
    ;

    $this->removeElement('file');
    $this->submit->setLabel('Save Changes');
  }

  /*
   * @overwrite
   */
  public function saveValues() {
    
  }

}
