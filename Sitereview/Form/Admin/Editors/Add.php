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
class Sitereview_Form_Admin_Editors_Add extends Engine_Form {

  public function init() {

    $this->setMethod('post');
    $this->setTitle("Add More Listing Types")
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    $editor_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('editor_id', null);
    $editor = Engine_Api::_()->getItem('sitereview_editor', $editor_id);
    $ids = Engine_Api::_()->getDbTable('editors', 'sitereview')->getListingTypeIds($editor->user_id);

    $multiOptions = array();
    $listingTypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypes();
    foreach ($listingTypes as $listingType) {
      if (!in_array($listingType->listingtype_id, $ids))
        $multiOptions["$listingType->listingtype_id"] = $listingType->title_plural;
    }

    $this->addElement('MultiCheckbox', 'listingtypes', array(
        'label' => 'Listing Types',
        'description' => 'Choose the listing types from below for which editors will be allowed to write editor reviews.',
        'multiOptions' => $multiOptions,
    ));

    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
        'prependText' => ' or ',
        'onclick' => 'javascript:parent.Smoothbox.close()',
        'decorators' => array(
            'ViewHelper',
        ),
    ));

    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    $button_group = $this->getDisplayGroup('buttons');
  }

}