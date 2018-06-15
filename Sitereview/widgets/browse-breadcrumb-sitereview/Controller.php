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
class Sitereview_Widget_BrowseBreadcrumbSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $request = Zend_Controller_Front::getInstance()->getRequest();
    $this->view->listingtype_id = $listingtype_id = $request->getParam('listingtype_id', null);
    $sitereviewBrowseBreadcrumb = Zend_Registry::isRegistered('sitereviewBrowseBreadcrumb') ?  Zend_Registry::get('sitereviewBrowseBreadcrumb') : null;

    if (empty($listingtype_id) || empty($sitereviewBrowseBreadcrumb)) {
      return $this->setNoRender();
    }

    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);

    $this->view->listingtypeArray = $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $this->view->listing_plural_lc = strtolower($listingtypeArray->title_plural);
    $this->view->listing_plural_uc = ucfirst($listingtypeArray->title_plural);
    $this->view->formValues = $values = $request->getParams();

    //GET LISTING CATEGORY TABLE
    $this->view->categoryTable = $tableCategory = Engine_Api::_()->getDbTable('categories', 'sitereview');
    $this->view->category_id = $this->view->subcategory_id = $this->view->subsubcategory_id = 0;
    $this->view->category_name = $this->view->subcategory_name = $this->view->subsubcategory_name = '';
    
    $category_id = $request->getParam('category_id', null);    
    if (!empty($category_id)) {
      $this->view->category_id = $category_id;
      $this->view->category_name = $tableCategory->getCategory($category_id)->category_name;

      $subcategory_id = $request->getParam('subcategory_id', null);
      
      if (!empty($subcategory_id)) {
        $this->view->subcategory_id = $subcategory_id;
        $this->view->subcategory_name = $tableCategory->getCategory($subcategory_id)->category_name;

        $subsubcategory_id = $request->getParam('subsubcategory_id', null);

        if (!empty($subsubcategory_id)) {
          $this->view->subsubcategory_id = $subsubcategory_id;
          $this->view->subsubcategory_name = $tableCategory->getCategory($subsubcategory_id)->category_name;
        }
      }
    }

    if (((isset($values['tag']) && !empty($values['tag']) && isset($values['tag_id']) && !empty($values['tag_id'])))) {
      $current_url = $request->getRequestUri();
      $current_url = explode("?", $current_url);
      if (isset($current_url[1])) {
        $current_url1 = explode("&", $current_url[1]);
        foreach ($current_url1 as $key => $value) {
          if (strstr($value, "tag=") || strstr($value, "tag_id=")) {
            unset($current_url1[$key]);
          }
        }
        $this->view->current_url2 = implode("&", $current_url1);
      }
    }

    // set no render if not coming data .
    if ( empty($this->view->category_id) ||  !(isset($this->view->formValues['tag']) && !empty($this->view->formValues['tag']) && isset($this->view->formValues['tag_id']) && !empty($this->view->formValues['tag_id'])) ) {
      return $this->setNoRender();
    }
  }

}