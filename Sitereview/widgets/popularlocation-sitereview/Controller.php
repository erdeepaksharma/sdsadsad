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
class Sitereview_Widget_PopularlocationSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    //GET LISTING TYPE ID    
    $params = array();
    $params['limit'] = $this->_getParam('itemCount', 10);
    $listingtype_id = $this->view->listingtype_id = $params['listingtype_id'] = $this->_getParam('listingtype_id');
    if (empty($listingtype_id)) {
      $listingtype_id = $this->view->listingtype_id = $params['listingtype_id'] = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id');
    }

    if (empty($listingtype_id)) {
      return $this->setNoRender();
    }

    //DONT RENDER IF LOCATION IS DIS-ABLED BY ADMIN
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    if (empty($listingtypeArray->location)) {
      return $this->setNoRender();
    }

    $this->view->category_id = 0;
    $this->view->subcategory_id = 0;
    $this->view->subsubcategory_id = 0;
    if (!empty($listingtype_id)) {
      $this->view->category_id = $params['category_id'] = $this->_getParam('hidden_category_id');
      $this->view->subcategory_id = $params['subcategory_id'] = $this->_getParam('hidden_subcategory_id');
      $this->view->subsubcategory_id = $params['subsubcategory_id'] = $this->_getParam('hidden_subsubcategory_id');
    }

    $params['listingtype_id'] = $listingtype_id;

    //GET SITEREVIEW SITEREVIEW FOR MOST RATED
    $this->view->sitereviewLocation = Engine_Api::_()->getDbTable('listings', 'sitereview')->getPopularLocation($params);

    //DONT RENDER IF SITEREVIEW COUNT IS ZERO
    if (!(count($this->view->sitereviewLocation) > 0)) {
      return $this->setNoRender();
    }

    $this->view->searchLocation = null;
    if (isset($_GET['location']) && !empty($_GET['location'])) {
      $this->view->searchLocation = $_GET['location'];
    }
  }

}