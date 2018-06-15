<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Overview.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Overview extends Engine_Form {

  public $_error = array();

  public function init() {

    $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    $listing_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listing_id', null);
    $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

    //GET VIEWER
    $viewer = Engine_Api::_()->user()->getViewer();

    //GET TINYMCE SETTINGS
    $albumEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album');
    $upload_url = "";
    if (Engine_Api::_()->authorization()->isAllowed('album', $viewer, 'create') && $albumEnabled) {
      $upload_url = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'upload-photo'), 'sitereview_general_listtype_' . $sitereview->listingtype_id, true);
    }

    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $listing_singular_uc = ucfirst($listingtypeArray->title_singular);
    $listing_singular_lc = strtolower($listingtypeArray->title_singular);
    $listing_singular_upper = strtoupper($listingtypeArray->title_singular);

    $this->setTitle("Edit $listing_singular_uc Overview")
            ->setDescription("Edit the overview for your $listing_singular_lc using the editor below, and then click 'Save Overview' to save changes.")
            ->setAttrib('name', 'sitereviews_overview');

    $this->addElement('TinyMce', 'overview', array(
        'label' => '',
        'allowEmpty' => false,
        'attribs' => array('rows' => 180, 'cols' => 350, 'style' => 'width:740px; max-width:740px;height:858px;'),

        'editorOptions' => Engine_Api::_()->seaocore()->tinymceEditorOptions($upload_url),
        'filters' => array(new Engine_Filter_Censor()),
    ));

    $this->addElement('Button', 'save', array(
        'label' => 'DASHBOARD_' . $listing_singular_upper . '_SAVE_OVERVIEW',
        'type' => 'submit',
    ));
  }

}