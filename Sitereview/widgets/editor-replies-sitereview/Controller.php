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
class Sitereview_Widget_EditorRepliesSitereviewController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {

    //DONT RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject('user')) {
      return $this->setNoRender();
    }

    $user = Engine_Api::_()->core()->getSubject();

    //GET SEARCHING PARAMETERS
    $this->view->page = $params['page'] = $this->_getParam('page', 1);
    $this->view->itemCount = $params['per_page'] = $this->_getParam('itemCount', 5);
    $this->view->truncation = $params['truncation'] = $this->_getParam('truncation', 60);
    $this->view->onlyListingtypeEditor = $params['onlyListingtypeEditor'] = $this->_getParam('onlyListingtypeEditor', 1);
    $params['listingTypeIds'] = Engine_Api::_()->getDbtable('editors', 'sitereview')->getListingTypeIds($user->user_id);

    if(empty($params['listingTypeIds']) && empty($params['onlyListingtypeEditor']))
      return $this->setNoRender();

    $this->view->replies = $this->getAllCommentsByUserPaginator($user, $params);
    $this->view->replyCount = $this->view->replies->getTotalItemCount();

    if (empty($this->view->replyCount))
      return $this->setNoRender();

    $this->view->is_ajax = $this->_getParam('is_ajax', 0);

    if (!empty($this->view->is_ajax)) {
      $this->getElement()->removeDecorator('Title');
      $this->getElement()->removeDecorator('Container');
    }
    
    if(!$this->view->is_ajax) {
        $this->view->params = array_merge($params, $this->_getAllParams());
        if ($this->_getParam('loaded_by_ajax', true)) {
          $this->view->loaded_by_ajax = true;
          if ($this->_getParam('is_ajax_load', false)) {
            $this->view->is_ajax_load = true;
            $this->view->loaded_by_ajax = false;
            if (!$this->_getParam('onloadAdd', false))
              $this->getElement()->removeDecorator('Title');
            $this->getElement()->removeDecorator('Container');
          } else {
            return;
          }
        }
        $this->view->showContent = true;    
    }
    else {
        $this->view->showContent = true;
    }    
    
  }

  public function getAllCommentsByUserSelect(User_Model_User $user, $params) {

		$commentsTable = Engine_Api::_()->getDbtable('comments', 'core');
		$commentsTableName = $commentsTable->info('name');
		$listingsTable = Engine_Api::_()->getDbtable('listings', 'sitereview');
		$listingsTableName = $listingsTable->info('name');
    $select = $commentsTable->select()
                            ->from($commentsTableName)
															->where("poster_type = ?", $user->getType())
															->where("poster_id = ?", $user->getIdentity())
															->where("resource_type = 'sitereview_listing' OR resource_type = 'sitereview_review'")
															->order("$commentsTableName.creation_date DESC");
    if(empty($params['onlyListingtypeEditor'])) {
			$select
															->setIntegrityCheck(false)
															->join($listingsTableName, $listingsTableName.'.listing_id = '.$commentsTableName.'.resource_id', null)
															->where($listingsTableName.'.listingtype_id in (?)', $params['listingTypeIds']);
    }
    
    return $select;
  }

  public function getAllCommentsByUserPaginator(User_Model_User $user, $params=array()) {

    $paginator = Zend_Paginator::factory($this->getAllCommentsByUserSelect($user, $params));
    if (!isset($params['per_page']) || empty($params['per_page']))
      $params['per_page'] = 4;
    $paginator->setItemCountPerPage($params['per_page']);
    if (!isset($params['page']) || empty($params['page']))
      $params['page'] = 1;
    $paginator->setCurrentPageNumber($params['page']);
    return $paginator;
  }

}