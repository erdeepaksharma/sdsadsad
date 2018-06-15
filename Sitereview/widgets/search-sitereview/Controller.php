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
class Sitereview_Widget_SearchSitereviewController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {

//    $sitereviewtable = Engine_Api::_()->getDbtable('listings', 'sitereview');
//    $sitereviewName = $sitereviewtable->info('name');
//
//    $categoryTable = Engine_Api::_()->getDbTable('categories', 'sitereview');

    $viewer = Engine_Api::_()->user()->getViewer()->getIdentity();

    $this->view->sitereview_post = true;

    $request = Zend_Controller_Front::getInstance()->getRequest();
    $params = $request->getParams();

    if (!isset($params['category_id']))
      $params['category_id'] = 0;
    if (!isset($params['subcategory_id']))
      $params['subcategory_id'] = 0;
    if (!isset($params['subsubcategory_id']))
      $params['subsubcategory_id'] = 0;
    $this->view->category_id = $category_id = $params['category_id'];
    $this->view->subcategory_id = $subcategory_id = $params['subcategory_id'];
    $this->view->subsubcategory_id = $subsubcategory_id = $params['subsubcategory_id'];

    //GET LISTING TYPE ID
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', null);
    if (empty($listingtype_id)) {
      $this->view->listingtype_id = $listingtype_id = $request->getParam('listingtype_id', null);
    }

    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);

    $this->view->categoryInSearchForm = Engine_Api::_()->getDbTable('searchformsetting', 'seaocore')->getFieldsOptions('sitereview_listtype_' . $listingtype_id, 'category_id');
    
    $this->view->locationDetection = $this->_getParam('locationDetection', 0);
    $widgetSettings = array(
        'locationDetection' => $this->view->locationDetection
    );
    
    //FORM CREATION
    $this->view->form = $form = new Sitereview_Form_Search(array('type' => 'sitereview_listing', 'listingTypeId' => $listingtype_id, 'widgetSettings' => $widgetSettings));
    //$this->view->form = $form = Zend_Registry::isRegistered('Sitereview_Form_Search') ? Zend_Registry::get('Sitereview_Form_Search') : new Sitereview_Form_Search(array('type' => 'sitereview_listing', 'listingTypeId' => $listingtype_id));
    $this->view->viewType = $this->_getParam('viewType', 'vertical');

    $orderBy = $request->getParam('orderby', null);
    if (empty($orderBy)) {
      $order = Engine_Api::_()->sitereview()->showSelectedBrowseBy($this->view->identity);
      $form->orderby->setValue("$order");
    }

    if (isset($params['tag']) && !empty($params['tag'])) {
      $tag = $params['tag'];
      $tag_id = isset($params['tag_id']) ? $params['tag_id'] : '';
      $page = !empty($params['page']) ? $params['page'] : 1;
      $params['tag'] = $tag;
      $params['tag_id'] = $tag_id;
      $params['page'] = $page;
    }

//    $orderBy = $request->getParam('orderby', null);

    if (!empty($orderBy)) {
      $params['orderby'] = $orderBy;
    }

    if (!empty($params))
      $form->populate($params);

    if (!$viewer) {
      $form->removeElement('show');
    }

    //SHOW PROFILE FIELDS ON DOME READY
    if (!empty($this->view->categoryInSearchForm) && !empty($this->view->categoryInSearchForm->display) && !empty($category_id)) {

      $categoryIds = array();
      $categoryIds[] = $category_id;
      $categoryIds[] = $subcategory_id;
      $categoryIds[] = $subsubcategory_id;

      //GET PROFILE MAPPING ID
      $this->view->profileType = Engine_Api::_()->getDbTable('categories', 'sitereview')->getProfileType($categoryIds, 0, 'profile_type');

    }  
      
    $categories = Engine_Api::_()->getDbTable('categories', 'sitereview')->getCategories(null, 0, $listingtype_id, 0, 1, 0, 'cat_order', 0, array('category_id', 'category_name', 'category_slug'));
    $categories_slug[0] = "";
    if (count($categories) != 0) {
      foreach ($categories as $category) {
        $categories_slug[$category->category_id] = $category->getCategorySlug();
      }
    }
    $this->view->categories_slug = $categories_slug;
  }

}