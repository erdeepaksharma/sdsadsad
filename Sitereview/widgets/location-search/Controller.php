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
class Sitereview_Widget_LocationSearchController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $valueArray = array('street' => $this->_getParam('street', 1), 'city' => $this->_getParam('city', 1), 'country' => $this->_getParam('country', 1), 'state' => $this->_getParam('state', 1));
    $list_street = serialize($valueArray);

    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', 0);
    $sitereviewLocationSearch = Zend_Registry::isRegistered('sitereviewLocationSearch') ?  Zend_Registry::get('sitereviewLocationSearch') : null;
    if (empty($listingtype_id)) {
      $this->view->listingtype_id = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    }
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);

    // Make form
    $this->view->form = $form = new Sitereview_Form_Locationsearch(array('value' => $list_street, 'type' => 'sitereview_listing', 'listingTypeId' => $listingtype_id));

    if (!empty($_POST)) {
      $this->view->category_id = $_POST['category_id'];
      $this->view->subcategory_id = $_POST['subcategory_id'];
      $this->view->subsubcategory_id = $_POST['subsubcategory_id'];
      $this->view->category_name = $_POST['categoryname'];
      $this->view->subcategory_name = $_POST['subcategoryname'];
      $this->view->subsubcategory_name = $_POST['subsubcategoryname'];
    }

    if (!empty($_POST)) {
      $this->view->advanced_search = $_POST['advanced_search'];
    }

    // Process form
    $params = Zend_Controller_Front::getInstance()->getRequest()->getParams();
    $form->isValid($params);
    $values = $form->getValues();
    unset($values['or']);
    $this->view->formValues = array_filter($values);
    $this->view->assign($values);
    if( empty($sitereviewLocationSearch) ) {
      return $this->setNoRender();
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