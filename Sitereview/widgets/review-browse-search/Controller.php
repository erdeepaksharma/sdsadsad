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
class Sitereview_Widget_ReviewBrowseSearchController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {

    $searchForm = $this->view->searchForm = new Sitereview_Form_Review_Search(array('type' => 'sitereview_review'));
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $this->view->requestParams = $requestParams = $request->getParams();

    if (isset($requestParams['page'])) {
      unset($requestParams['page']);
    }

    $searchForm
            ->setMethod('get')
            ->populate($requestParams)
    ;

    if (!isset($requestParams['listingtype_id']))
      $requestParams['listingtype_id'] = 0;

    $categories = Engine_Api::_()->getDbTable('categories', 'sitereview')->getCategories(null, 0, $requestParams['listingtype_id'], 0, 1, 0, 'cat_order', 0, array('category_id', 'category_name', 'category_slug'));
    $categories_slug[0] = "";
    if (count($categories) != 0) {
      foreach ($categories as $category) {
        $categories_slug[$category->category_id] = $category->getCategorySlug();
      }
    }
    $this->view->categories_slug = $categories_slug;

    $this->view->searchField = 'search';
    $this->view->widgetParams = $widgetParams = $this->_getAllParams();
  }

}