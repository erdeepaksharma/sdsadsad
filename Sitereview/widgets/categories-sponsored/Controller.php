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
class Sitereview_Widget_CategoriesSponsoredController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    //GET LISTING TYPE ID
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', -1);
    if (empty($listingtype_id)) {
      $this->view->listingtype_id = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    }

    $itemCount = $this->_getParam('itemCount', 0);
    $this->view->showIcon = $this->_getParam('showIcon', 1);

    //GET CATEGORY TABLE
    $this->view->tableCategory = $tableCategory = Engine_Api::_()->getDbtable('categories', 'sitereview');

    //GET SPONSORED CATEGORIES
    $this->view->categories = $categories = $tableCategory->getCategories(null, 0, $listingtype_id, 1, 0, $itemCount, 'cat_order', 0, array('category_id', 'category_name', 'file_id', 'listingtype_id', 'cat_dependency', 'subcat_dependency'));

    //GET STORAGE API
    $this->view->storage = Engine_Api::_()->storage();

    //GET SPONSORED CATEGORIES COUNT
    $this->view->totalCategories = Count($categories);

    if ($this->view->totalCategories <= 0) {
      return $this->setNoRender();
    }
  }

}