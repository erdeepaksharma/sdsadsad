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
class Sitereview_Widget_PhotosCarouselController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {

    //DONT RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      return $this->setNoRender();
    }

    //GET SUBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
    $sitereviewPhotoCarousel = Zend_Registry::isRegistered('sitereviewPhotoCarousel') ?  Zend_Registry::get('sitereviewPhotoCarousel') : null;
    $this->view->listingType = $listingType = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);
    $this->view->listingtype_id = $sitereview->listingtype_id;
    $this->view->album = $album = $sitereview->getSingletonAlbum();
    $this->view->photo_paginator = $photo_paginator = $album->getCollectiblesPaginator();
    $this->view->total_images = $photo_paginator->getTotalItemCount();
    $minMum = $this->_getParam('minMum', 0);
    
    if (empty($this->view->total_images) || $this->view->total_images < $minMum || empty($sitereviewPhotoCarousel)) {
      return $this->setNoRender();
    }
    
    $this->view->itemCount = $itemCount = $this->_getParam('itemCount', 3);
    $this->view->includeInWidget = $this->_getParam('includeInWidget', null);
    $photo_paginator->setItemCountPerPage(100);
    
    if ($this->view->includeInWidget) {
      $this->getElement()->removeDecorator('Title');
      $this->getElement()->removeDecorator('Container');
    }
  }

}