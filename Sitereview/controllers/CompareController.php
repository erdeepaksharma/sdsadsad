<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: CompareController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_CompareController extends Core_Controller_Action_Standard {

  public function compareAction() {
    
    $request = $this->getRequest();
    //GET VIEWER ID
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    $cookiesSuffix = $viewer_id ? "_" . $viewer_id : '';
    $cookiesContent = $request->getCookie('srCompareListType' . $cookiesSuffix, '');
    $listTypes = ($cookiesContent && $cookiesContent != 'undefined') ? Zend_Json_Decoder::decode($cookiesContent) : null;
    if (empty($listTypes)) {
      return;
    }
    $this->view->customFields = array();
    $this->view->ratingsParams = array();
    $category_id = $this->_getParam('id');
    $cookiesList = $request->getCookie('srCompareList' . "lt" . $category_id . $cookiesSuffix, '');
    $cookieDataList = ($cookiesList && $cookiesList != 'undefined') ? Zend_Json_Decoder::decode($cookiesList) : null;
    $compareSettingsTable = Engine_Api::_()->getDbtable('compareSettings', 'sitereview');
    $this->view->compareSettingList = $compareSettingList = $compareSettingsTable->getCompareList(array(
        'category_id' => $category_id,
        'fetchRow' => 1
            ));
    if (empty($cookieDataList) || !$compareSettingList->enabled) {
      return;
    }
    $this->view->category = $category = Engine_Api::_()->getItem('sitereview_category', $category_id);
    $listingtype_id = $category->listingtype_id;
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $this->view->listingType = $this->_listingType = $listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $this->view->listing_plural_lc = strtolower($this->_listingType->title_plural);
    $this->view->listing_singular_lc = strtolower($this->_listingType->title_singular);

    if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "view_listtype_$listingtype_id")->isValid()) {
      return;
    }

    $description_list_type = $this->view->translate(ucfirst($listingType->title_plural));
    $this->view->category_id = $category_id; // = $this->_getParam('category_id', null);
    $description_list_type = $this->view->htmlLink($listingType->getHref(), $description_list_type, array());
    $category_array = array("0" => $description_list_type);
    $category_array[$category->category_id] = $this->view->htmlLink($category->getHref(), $this->view->translate($category->category_name), array());
    $category_ids = array($category->category_id);
    $dependency = $cat_dependency = $category->cat_dependency;
    while ($dependency != 0) {
      $parent_category = Engine_Api::_()->getItem('sitereview_category', $dependency);
      $category_array[$parent_category->category_id] = $this->view->htmlLink($parent_category->getHref(), $this->view->translate($parent_category->category_name), array());

      $dependency = $parent_category->cat_dependency;
      $category_ids[] = $parent_category->category_id;
    };
    ksort($category_array);
    $this->view->heading = join(" &raquo; ", $category_array);
    //$this->view->heading = $description_list_type;
    $list_ids = array_keys($cookieDataList);
    $category_id_key = 'category_id';
    $mapping = count($category_ids);
    for ($i = $mapping; $i > 1; $i--) {
      $category_id_key = 'sub' . $category_id_key;
    }
    $this->view->lists = $lists = Engine_Api::_()->getDbtable('listings', 'sitereview')->getListings(array(
        'list_ids' => $list_ids,
        $category_id_key => $category_id
            ));
    $this->view->totalList = count($lists);
    if ($compareSettingList->editor_rating_fields || $compareSettingList->user_rating_fields)
      $this->view->ratingsParams = Engine_Api::_()->getDbtable('ratingparams', 'sitereview')->reviewParams($category_ids, 'sitereview_listing');
    $this->view->compareSettingListEditorRatingFields = $compareSettingListEditorRatingFields = !empty($compareSettingList->editor_rating_fields) ? Zend_Json_Decoder::decode($compareSettingList->editor_rating_fields) : array();
    $this->view->compareSettingListUserRatingFields = $compareSettingListUserRatingFields = !empty($compareSettingList->user_rating_fields) ? Zend_Json_Decoder::decode($compareSettingList->user_rating_fields) : array();
    $this->view->seacore_api = Engine_Api::_()->seaocore();

    $this->view->compareSettingListCustomFields = $compareSettingListCustomFields = !empty($compareSettingList->custom_fields) ? Zend_Json_Decoder::decode($compareSettingList->custom_fields) : array();
    if (count($compareSettingListCustomFields) > 0) {
      $maaping_category_ids = array();
      foreach ($lists as $sitereview) {
        if ($sitereview->category_id) {
          $maaping_category_ids[$sitereview->category_id] = $sitereview->category_id;
          if ($sitereview->subcategory_id) {
            $maaping_category_ids[$sitereview->subcategory_id] = $sitereview->subcategory_id;
            if ($sitereview->subsubcategory_id) {
              $maaping_category_ids[$sitereview->subsubcategory_id] = $sitereview->subsubcategory_id;
            }
          }
        }
      }

      $this->view->proifle_map_ids = $proifle_map_ids = Engine_Api::_()->getDbtable('categories', 'sitereview')->getAllProfileTypes($maaping_category_ids);
      $customFields = array();

      foreach ($proifle_map_ids as $proifle_map_id) {
        $selectOption = Engine_Api::_()->getDbtable('metas', 'sitereview')->getProfileFields($proifle_map_id);
        if ($selectOption) {
          foreach ($selectOption as $key => $value) {
            $customFields[$key] = $value;
          }
        }
      }

      $this->view->customFields = $customFields;
      if (empty($this->view->customFields))
        $this->view->customFields = array();
      $this->view->fieldsApi = Engine_Api::_()->fields();
    }

    //GET LISTING TITLE
    if ($listingtype_id) {
      $siteinfo = $this->view->layout()->siteinfo;
      $titles = $siteinfo['title'];
      $keywords = $siteinfo['keywords'];
      $listing_type_title = ucfirst($this->_listingType->title_plural);
      if (!empty($titles))
        $titles .= ' - ';
      $titles .= $listing_type_title;
      $siteinfo['title'] = $titles;

      if (!empty($keywords))
        $keywords .= ' - ';
      $keywords .= $listing_type_title;
      $siteinfo['keywords'] = $keywords;

      $this->view->layout()->siteinfo = $siteinfo;
    }

    // Render
    $this->_helper->content
            //->setNoRender()
            ->setEnabled()
    ;
  }

}
