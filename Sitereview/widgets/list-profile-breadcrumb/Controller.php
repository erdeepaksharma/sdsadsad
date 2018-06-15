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
class Sitereview_Widget_ListProfileBreadcrumbController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {

    //DONT RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      return $this->setNoRender();
    }

    //GET SUBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
    $this->view->listingType = $listingType = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);
    $this->view->listingtype_id = $sitereview->listingtype_id;
    $this->view->title_plural = ucfirst($listingType->title_plural);
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