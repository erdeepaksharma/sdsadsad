<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 2014-02-16 5:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Widget_ClaimedListingsController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {
 
    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    $this->view->listingtype_id = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', 0);
    
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    
    $this->view->listing_plural_uc = ucfirst($listingtypeArray->title_plural);
    $this->view->listing_singular_lc = lcfirst($listingtypeArray->title_singular);
    $this->view->listing_plural_lc = lcfirst($listingtypeArray->title_plural);
   
        //MAKE PAGINATOR
    $this->view->paginator = $paginator = Engine_Api::_()->getDbtable('claims', 'sitereview')->getMyClaimListings($viewer_id, $listingtype_id);

    //GET PAGINATOR
    $paginator->setItemCountPerPage(10);
    $this->view->paginator = $paginator->setCurrentPageNumber(10);
  }

}