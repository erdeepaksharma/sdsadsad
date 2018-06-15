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
class Sitereview_Form_Admin_Editors_Map extends Engine_Form {

  public function init() {

    $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);

    $this->setMethod('post');
    $this->setTitle("Remove Editor?")
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    if (!empty($listingtype_id)) {
      $this->setDescription('Are you sure you want to remove this listing type? If you want to assign editor reviews written by this editor in this listing type, then select a new editor from the drop-down below otherwise leave the drop-down blank.');
    } else {
      $this->setDescription('Are you sure want to remove this Editor? If you want to assign editor reviews written by this editor to other editor, then select a new editor from the drop-down below otherwise leave the drop-down blank.');
    }

    $editor_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('editor_id', null);

    $currentEditor = Engine_Api::_()->getItem('sitereview_editor', $editor_id);
    $editorTable = Engine_Api::_()->getDbTable('editors', 'sitereview');

    $getDetails = $editorTable->getEditorDetails($currentEditor->user_id, $listingtype_id);
    foreach ($getDetails as $getDetail) {
      $multiOptions = array();
      $multiOptions[0] = '';
      $editors = $editorTable->getAllEditors($getDetail->listingtype_id, $currentEditor->user_id);
      foreach ($editors as $editor) {
        $user = Engine_Api::_()->getItem('user', $editor->user_id);
        $multiOptions[$editor->user_id] = $user->getTitle();
      }

      $this->addElement('Select', 'editors_listtype_' . $getDetail->listingtype_id, array(
          'label' => "$getDetail->title_plural",
          'multiOptions' => $multiOptions,
          'value' => '',
      ));
    }

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