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
class Sitereview_Widget_ReviewButtonController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {

    $listing_guid = $this->_getParam('listing_guid', null);
    $identity = $this->_getParam('identity', 0);
    $this->view->listing_profile_page = $this->_getParam('listing_profile_page', 0);
    if (empty($listing_guid) && !Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      return $this->setNoRender();
    }
    
    if(empty($listing_guid) && Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');
      $listing_guid = $sitereview->getGuid();
      $this->view->listing_profile_page = 1;
      $identity = Engine_Api::_()->sitereview()->existWidget('sitereview_reviews', 0, $sitereview->listingtype_id);
    }
    else {
      $this->getElement()->removeDecorator('Title');
      $this->getElement()->removeDecorator('Container');
    }
   

    $this->view->sitereview = $sitereview = Engine_Api::_()->getItemByGuid($listing_guid);
    $this->view->listingtype_id = $listingtype_id = $sitereview->listingtype_id;
    $this->view->listing_id = $listing_id = $sitereview->listing_id;
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    
    if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
			if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "user_review"))
			return $this->setNoRender();
    }
    
    $this->view->listingtypeArray = $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    if (empty($listingtypeArray->reviews) || $listingtypeArray->reviews == 1) {
      return $this->setNoRender();
    }

    //GET VIEWER
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer_id = $viewer->getIdentity();
    //GET USER LEVEL ID
    if (!empty($viewer_id)) {
      $this->view->level_id = $level_id = $viewer->level_id;
    } else {
      $this->view->level_id = $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
    }

    $create_review = ($sitereview->owner_id == $viewer_id) ? $listingtypeArray->allow_owner_review : 1;
    if (empty($create_review)) {
      return $this->setNoRender();
    }

    //GET RATING TABLE
    $ratingTable = Engine_Api::_()->getDbTable('ratings', 'sitereview');
      
		//GET REVIEW TABLE
		$reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');
		if ($viewer_id) {
			$params = array();
			$params['resource_id'] = $sitereview->listing_id;
			$params['resource_type'] = $sitereview->getType();
			$params['viewer_id'] = $viewer_id;
			$params['type'] = 'user';
			$this->view->review_id = $hasPosted = $reviewTable->canPostReview($params);
		} else {
				$this->view->review_id = $hasPosted = 0;
		}
		
		$autorizationApi = Engine_Api::_()->authorization();
		if($autorizationApi->getPermission($level_id, 'sitereview_listing', "review_create_listtype_$listingtype_id") && empty($hasPosted)) {
		  $this->view->createAllow = 1;
		} elseif($autorizationApi->getPermission($level_id, 'sitereview_listing', "review_update_listtype_$listingtype_id") && !empty($hasPosted)) {
			$this->view->createAllow = 2;
		}
		else {
		  $this->view->createAllow = 0;
		}
		
    $this->view->update_permission = $autorizationApi->getPermission($level_id, 'sitereview_listing', "review_update_listtype_$listingtype_id");
    $selectRatingTable = $ratingTable->select()
                           ->from($ratingTable->info('name'),'rating_id')
														->where('resource_id = ?',$sitereview->listing_id)
														->where('resource_type = ?',$sitereview->getType())
														->where('user_id = ?',$viewer_id);
		$this->view->rating_exist = $selectRatingTable->query()->fetchColumn();
		
		$show_rating = 0;
		if(!empty($this->view->rating_exist) && empty($listingtypeArray->allow_review))
		  $show_rating = 1;
		  
    if(empty($this->view->createAllow) && empty($show_rating))
      return $this->setNoRender();

    if ($this->view->listing_profile_page) {
      $this->view->contentDetails = Engine_Api::_()->sitereview()->getWidgetInfo('sitereview.user-sitereview', $identity);
    }

    $this->view->tab = Engine_Api::_()->sitereview()->getTabId($sitereview->listingtype_id, 'sitereview.sitemobile-user-sitereview'); 
  }

}