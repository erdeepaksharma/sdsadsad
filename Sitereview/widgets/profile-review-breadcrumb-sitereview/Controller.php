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
class Sitereview_Widget_ProfileReviewBreadcrumbSitereviewController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {

    //DONT RENDER IF NOT AUTHORIZED
    if (!Engine_Api::_()->core()->hasSubject('sitereview_review')) {
      return $this->setNoRender();
    }

    //GET REVIEWS
    $this->view->reviews = Engine_Api::_()->core()->getSubject();

    //GET LISTING 
    $this->view->sitereview = $sitereview = $this->view->reviews->getParent();
    $this->view->listingtype_id = $listingtype_id = $sitereview->listingtype_id;
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
$this->view->listingType = $listingType = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);
    //GET TAB ID
    $this->view->tab_id = Engine_Api::_()->sitereview()->existWidget('sitereview_reviews', 0, $listingtype_id);

    //GET LISTING TYPE TITLE
    $this->view->title_plural = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeColumn($listingtype_id, 'title_plural');

    //GET CATEGORY TABLE
    $this->view->tableCategory = Engine_Api::_()->getDbTable('categories', 'sitereview');
    if (!empty($sitereview->category_id)) {
      $this->view->category_name = $this->view->tableCategory->getCategory($sitereview->category_id)->category_name;
      if (!empty($sitereview->subcategory_id)) {
        $this->view->subcategory_name = $this->view->tableCategory->getCategory($sitereview->subcategory_id)->category_name;
        if (!empty($sitereview->subsubcategory_id)) {
          $this->view->subsubcategory_name = $this->view->tableCategory->getCategory($sitereview->subsubcategory_id)->category_name;
        }
      }
    }
  }

}