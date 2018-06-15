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
class Sitereview_Widget_RatedListingsSitereviewController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {
   
    if ($this->_getParam('is_ajax_load', false)) {
      $this->view->is_ajax_load = true;
      if ($this->_getParam('contentpage', 1) > 1)
        $this->getElement()->removeDecorator('Title');
      $this->getElement()->removeDecorator('Container');
    } else {
      if(!$this->_getParam('detactLocation', 0)){
        $this->view->is_ajax_load = true;
      }else{
       $this->getElement()->removeDecorator('Title');
      }
    }    
    
    //GET VIEWER DETAILS
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer->getIdentity();

    //GET SETTINGS
    $this->view->allParams = $this->_getAllParams();
    $this->view->identity = $this->view->allParams['identity'] = $this->_getParam('identity', $this->view->identity); 
    $ShowViewArray = $this->_getParam('layouts_views', array("0" => "1", "1" => "2", "2" => "3"));
    $this->view->isajax = $this->_getParam('isajax', 0);
    $this->view->viewType = $this->_getParam('viewType', '');
    $this->view->statistics = $this->_getParam('statistics', array("viewCount", "likeCount", "commentCount", "reviewCount"));
    $defaultOrder = $this->view->defaultOrder = $this->_getParam('layouts_order', 2);
    if (empty($this->view->viewType)) {
      if ($defaultOrder == 1)
       $this->view->viewType = 'listview';
      else
         $this->view->viewType = 'gridview';
    }
    $this->view->ratingType = $this->_getParam('ratingType', 'rating_both');
    $this->view->title_truncation = $this->_getParam('truncation', 25);
    $this->view->title_truncationGrid = $this->_getParam('truncationGrid', 90);
    $this->view->postedby = $params['postedby'] = $this->_getParam('postedby', 1);
    $this->view->showExpiry = $this->_getParam('showExpiry', 0);
    $this->view->showContent = $params['showContent'] = $this->_getParam('showContent', array("price", "location")); 
    $this->view->list_view = 0;
    $this->view->grid_view = 0;
    $this->view->map_view = 0;
    $this->view->defaultView = -1;
    if (in_array("1", $ShowViewArray)) {
      $this->view->list_view = 1;
      if ($this->view->defaultView == -1 || $defaultOrder == 1)
        $this->view->defaultView = 0;
    }
    if (in_array("2", $ShowViewArray)) {
      $this->view->grid_view = 1;
      if ($this->view->defaultView == -1 || $defaultOrder == 2)
        $this->view->defaultView = 1;
    }
    if (in_array("3", $ShowViewArray)) {
      $this->view->map_view = 1;
      if ($this->view->defaultView == -1 || $defaultOrder == 3)
        $this->view->defaultView = 2;
    }
    $this->view->layouts_views=$ShowViewArray;
    if ($this->view->defaultView == -1) {
      return $this->setNoRender();
    }
    $customFieldValues = array();
    $values = array();

    $request = Zend_Controller_Front::getInstance()->getRequest();
    $sitereviewBroseListing = Zend_Registry::isRegistered('sitereviewBroseListing') ?  Zend_Registry::get('sitereviewBroseListing') : null;
    
    $this->view->params = $params = $request->getParams();
    if (!isset($params['category_id']))
      $params['category_id'] = 0;
    if (!isset($params['subcategory_id']))
      $params['subcategory_id'] = 0;
    if (!isset($params['subsubcategory_id']))
      $params['subsubcategory_id'] = 0;
    $this->view->category_id = $params['category_id'];
    $this->view->subcategory_id = $params['subcategory_id'];
    $this->view->subsubcategory_id = $params['subsubcategory_id'];
    
    //SHOW CATEGORY NAME
    $this->view->categoryName = '';
    if($this->view->category_id) {
      $this->view->categoryName = Engine_Api::_()->getItem('sitereview_category', $this->view->category_id)->category_name;
      $this->view->categoryObject = Engine_Api::_()->getItem('sitereview_category', $this->view->category_id);
      
      if($this->view->subcategory_id) {
        $this->view->categoryName = Engine_Api::_()->getItem('sitereview_category', $this->view->subcategory_id)->category_name;
        $this->view->categoryObject = Engine_Api::_()->getItem('sitereview_category', $this->view->subcategory_id);

        if($this->view->subsubcategory_id) {
          $this->view->categoryName = Engine_Api::_()->getItem('sitereview_category', $this->view->subsubcategory_id)->category_name;
          $this->view->categoryObject = Engine_Api::_()->getItem('sitereview_category', $this->view->subsubcategory_id);
        }      
      }      
    }
    
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', 0);
    if (empty($listingtype_id)) {
      $this->view->listingtype_id = $listingtype_id = $request->getParam('listingtype_id', null);
    }

    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);

    $this->view->listingtypeArray = $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $this->view->listing_plural_uc = ucfirst($listingtypeArray->title_plural);

    if (!empty($this->view->statistics) && empty($listingtypeArray->reviews) || $listingtypeArray->reviews == 1) {
      $key = array_search('reviewCount', $this->view->statistics);
      if (!empty($key)) {
        unset($this->view->statistics[$key]);
      }
    }

    if (isset($params['tag']) && !empty($params['tag'])) {
      $tag = $params['tag'];
      $tag_id = $params['tag_id'];
    }
    
    $this->view->current_page = $page = 1;
    if (isset($params['page']) && !empty($params['page'])) {
      $this->view->current_page = $page = $params['page'];
    }
    $this->view->allParams['page'] = $this->view->current_page;

    //GET VALUE BY POST TO GET DESIRED LISTINGS
    if (!empty($params)) {
      $values = array_merge($values, $params);
    }

    //FORM GENERATION
    $form = new Sitereview_Form_Search(array('type' => 'sitereview_listing', 'listingTypeId' => $listingtype_id));

    if (!empty($params)) {
      $form->populate($params);
    }

    $this->view->formValues = $form->getValues();

    $values = array_merge($values, $form->getValues());

    $values['page'] = $page;

    //GET LISITNG FPR PUBLIC PAGE SET VALUE
    $values['type'] = 'browse';

    if (@$values['show'] == 2) {

      //GET AN ARRAY OF FRIEND IDS
      $friends = $viewer->membership()->getMembers();

      $ids = array();
      foreach ($friends as $friend) {
        $ids[] = $friend->user_id;
      }

      $values['users'] = $ids;
    }

    $this->view->assign($values);

    //CORE API
    $this->view->settings = $settings = Engine_Api::_()->getApi('settings', 'core');

    //CUSTOM FIELD WORK
    $customFieldValues = array_intersect_key($values, $form->getFieldElements());
    if ($form->show->getValue() == 3 && !isset($_GET['show'])) {
      @$values['show'] = 3;
    }

    $values['orderby'] = $orderBy = $request->getParam('orderby', null);
    if (empty($orderBy)) {
      $values['orderby'] = $this->_getParam('orderby', 'creation_date');
    }
    $this->view->allParams['orderby'] = $values['orderby'];
    $this->view->limit = $values['limit'] = $itemCount = $this->_getParam('itemCount', 10);
    $this->view->bottomLine = $this->_getParam('bottomLine', 1);
    $this->view->bottomLineGrid = $this->_getParam('bottomLineGrid', 2);
    $values['viewType'] = $this->view->viewType ;
    $values['showClosed'] = $this->_getParam('showClosed', 1);
    $values['listingtype_id'] = $listingtype_id;
    $values['most_rated'] = 1;
    
    $this->view->detactLocation = $values['detactLocation'] = $this->_getParam('detactLocation', 0);
    if($listingtype_id && $this->view->detactLocation) {
      $this->view->detactLocation = Engine_Api::_()->sitereview()->enableLocation($listingtype_id);
    }        
    if($this->view->detactLocation) {
      $this->view->defaultLocationDistance = $values['defaultLocationDistance'] = $this->_getParam('defaultLocationDistance', 1000);    
      $values['latitude'] = $this->_getParam('latitude', 0);
      $values['longitude'] = $this->_getParam('longitude', 0);
    }       

    if (!empty($listingtype_id)) {
      if(isset($_GET['category_id'])) {
				$category_id = $_GET['category_id'];
      }
      else {
        $category_id = $this->_getParam('hidden_category_id');
      }
      if(isset($_GET['subcategory_id'])) {
				$subcategory_id = $_GET['subcategory_id'];
      }
      else {
        $subcategory_id = $this->_getParam('hidden_subcategory_id');
      }
      if(isset($_GET['subsubcategory_id'])) {
				$subsubcategory_id = $_GET['subsubcategory_id'];
      }
      else {
        $subsubcategory_id = $this->_getParam('hidden_subsubcategory_id');
      }
    }
    
    if ($category_id)
      $this->view->category_id =  $_GET['category_id'] = $category_id;
    if ($subcategory_id)
      $this->view->subcategory_d = $_GET['subcategory_id'] = $subcategory_id;
    if ($subsubcategory_id)
     $this->view->subsubcategory_id = $_GET['subsubcategory_id'] = $subsubcategory_id;
     
    if (!empty($_GET)) {
      $values = array_merge($values,$_GET);
    }
    
    // GET LISTINGS
    $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('listings', 'sitereview')->getSitereviewsPaginator($values, $customFieldValues);
    $paginator->setItemCountPerPage($itemCount);
    $this->view->paginator = $paginator->setCurrentPageNumber($values['page']);
    $this->view->totalResults = $paginator->getTotalItemCount();

    $this->view->enableLocation = $checkLocation = Engine_Api::_()->sitereview()->enableLocation($listingtype_id);
    $this->view->flageSponsored = 0;
    $this->view->totalCount = $paginator->getTotalItemCount();
    if (!empty($checkLocation) && $paginator->getTotalItemCount() > 0) {
      $ids = array();
      $sponsored = array();
      foreach ($paginator as $listing) {
        $id = $listing->getIdentity();
        $ids[] = $id;
        $listing_temp[$id] = $listing;
      }
      $values['listing_ids'] = $ids;
      $this->view->locations = $locations = Engine_Api::_()->getDbtable('locations', 'sitereview')->getLocation($values);

      foreach ($locations as $location) {
        if ($listing_temp[$location->listing_id]->sponsored) {
          $this->view->flageSponsored = 1;
          break;
        }
      }
      $this->view->sitereview = $listing_temp;
    } else {
      $this->view->enableLocation = 0;
    }

    $this->view->search = 0;
    if (!empty($this->_getAllParams) && Count($this->_getAllParams) > 1) {
      $this->view->search = 1;
    }
    
    if( empty($sitereviewBroseListing) ) {
      return $this->setNoRender();
    }

    //SEND FORM VALUES TO TPL
    $this->view->formValues = $values;

    //CAN CREATE PAGES OR NOT
    $this->view->can_create = Engine_Api::_()->authorization()->isAllowed('sitereview_listing', $viewer, "create_listtype_$listingtype_id");
    $this->view->ratingTable = Engine_Api::_()->getDbtable('ratings', 'sitereview');
    $this->view->columnWidth = $this->_getParam('columnWidth', '180');
    $this->view->columnHeight = $this->_getParam('columnHeight', '255');
    
    $this->view->paramsLocation = array_merge($_GET, $this->_getAllParams());
    $this->view->paramsLocation = array_merge($request->getParams(), $this->view->paramsLocation);
    $this->view->allParams['listingType'] = $this->view->listingType = $this->_getParam('listingType', $this->view->viewType);
    $this->view->viewmore = $this->_getParam('viewmore', false);
    if (isset($_GET['search']) || isset($_POST['search'])) {
      $this->view->detactLocation = 0;
    } else {
      $this->view->detactLocation = $this->_getParam('detactLocation', 0);
    } 
    //$this->view->listingtype_id = $listingtype_id;
  }

}