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
class Sitereview_Form_Priceinfo_Edit extends Sitereview_Form_Priceinfo_Add {

  public $_error = array();

  public function init() {

    //GET LISTING TYPE ID
    $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $listing_singular_lc = strtolower($listingtypeArray->title_singular);
    $listing_singular_upper = strtoupper($listingtypeArray->title_singular);
    parent::init();

   $this->setTitle('EDIT_DASHBOARD_' . $listing_singular_upper . '_WHERE_TO_BUY_OPTION')
            ->setDescription("Edit Where to Buy option for this $listing_singular_lc using the form below.");

    $this->execute->setLabel('Save Changes');
  }

}