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
class Sitereview_Widget_ItemSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $listing_id = $this->_getParam('listing_id');

    if (empty($listing_id)) {
      return $this->setNoRender();
    }

    //GET ITEM OF THE DAY
    $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
    $sitereviewGetItem = Zend_Registry::isRegistered('sitereviewGetItem') ?  Zend_Registry::get('sitereviewGetItem') : null;

    if (empty($sitereview) || empty($sitereviewGetItem)) {
      return $this->setNoRender();
    }

    $starttime = $this->_getParam('starttime');
    $endtime = $this->_getParam('endtime');
    $currenttime = date('Y-m-d H:i:s');

    if (!empty($starttime) && $currenttime < $starttime) {
      return $this->setNoRender();
    }

    if (!empty($endtime) && $currenttime > $endtime) {
      return $this->setNoRender();
    }

    $this->view->ratingType = $this->_getParam('ratingType', 'rating_avg');

    if ($sitereview->closed == 1 || empty($sitereview->approved) || $sitereview->draft == 1 || empty($sitereview->search) || empty($sitereviewGetItem)) {
      $this->setNoRender();
    }
  }

}
