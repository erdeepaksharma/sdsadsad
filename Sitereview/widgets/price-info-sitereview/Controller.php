<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Widget_PriceInfoSitereviewController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {

    //DONT RENDER IF NOT AUTHORIZED
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing') && !Engine_Api::_()->core()->hasSubject('sitereview_review')) {
      return $this->setNoRender();
    }

    $this->view->review = $review = '';
    if (Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject();
    } elseif (Engine_Api::_()->core()->hasSubject('sitereview_review')) {
      $this->view->review = $review = Engine_Api::_()->core()->getSubject();
      $this->view->sitereview = $sitereview = $review->getParent();
    }

    Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
    $listingType = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);
    if (!$sitereview->allowWhereToBuy() ) {
      return $this->setNoRender();
    }
    $this->view->layout_column = $this->_getParam('layout_column', 1);
    $this->view->listing_singular_lc = strtolower($listingType->title_singular);
    $this->view->listing_singular_UPPER = strtoupper($listingType->title_singular);
    $params = array();
    if ($this->view->layout_column) {
      $params['limit'] = $this->_getParam('limit', 4);
    }

    $priceInfoTable = Engine_Api::_()->getDbTable('priceinfo', 'sitereview');
    $this->view->priceInfos = $priceInfoTable->getPriceDetails($sitereview->listing_id, $params);

    if (Count($this->view->priceInfos) <= 0) {
      return $this->setNoRender();
    }
    
    $this->view->params = $params = $this->_getAllParams();
    if ($this->_getParam('loaded_by_ajax', false)) {
      $this->view->loaded_by_ajax = true;
      if ($this->_getParam('is_ajax_load', false)) {
        $this->view->is_ajax_load = true;
        $this->view->loaded_by_ajax = false;
        if (!$this->_getParam('onloadAdd', false))
          $this->getElement()->removeDecorator('Title');
        $this->getElement()->removeDecorator('Container');
        $this->view->showContent = true;
      }
    } else {
      $this->view->showContent = true;
    }    

    $this->view->show_price = ($listingType->where_to_buy == 1) ? 0 : 1;
    if ($this->view->show_price)
      $this->view->min_price = $priceInfoTable->getMinPrice($sitereview->listing_id);
    $this->view->tab_id = "";

    if ($review) {
      $this->view->tab_id = Engine_Api::_()->sitereview()->getTabId($sitereview->listingtype_id, 'sitereview.price-info-sitereview');
    }
    else {
      $this->view->contentDetails = Engine_Api::_()->sitereview()->getWidgetInfo('sitereview.price-info-sitereview', $this->view->identity);

      if (empty($this->view->contentDetails)) {
        $this->view->contentDetails->content_id = 0;
      }
    }
  }

}
