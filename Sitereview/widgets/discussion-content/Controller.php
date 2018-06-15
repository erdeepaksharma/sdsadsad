<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
* @package    Sitereview
 * @copyright  Copyright 2010-2011 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 2011-05-05 9:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Widget_DiscussionContentController extends Seaocore_Content_Widget_Abstract {

  protected $_childCount;
  
  //ACTION FOR FETCHING THE DISCUSSIONS FOR THE PAGES
  public function indexAction() { 	
  	
    //DONT RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject('sitereview_topic')) {
      return $this->setNoRender();
    }


    //GET LISTING SUBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject('sitereview_topic')->getParent();

    //GET VIEWER
    $viewer = Engine_Api::_()->user()->getViewer();

    //GET LISTINGTYPE ID
    $this->view->listingtype_id = $listingtype_id = $sitereview->listingtype_id;

    //GET VIEWER
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer_id = $viewer->getIdentity();
    //SEND TAB ID TO THE TPL
    $this->view->tab_selected_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('content_id');
    //GET TOPIC  SUBJECT
    $this->view->topic = $topic = Engine_Api::_()->core()->getSubject();

    //GET SITEREVIEW OBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $topic->listing_id);

    //WHO CAN POST TOPIC
    $this->view->canPost = $canPost = $sitereview->authorization()->isAllowed($viewer, "topic_listtype_$listingtype_id");

    //INCREASE THE VIEW COUNT
    if (!$viewer || !$viewer_id || $viewer_id != $topic->user_id) {
      $topic->view_count = new Zend_Db_Expr('view_count + 1');
      $topic->save();
    }

    //CHECK WATHCHING
    $isWatching = null;
    if ($viewer->getIdentity()) {
      $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'sitereview');
      $isWatching = $topicWatchesTable->isWatching($sitereview->getIdentity(), $topic->getIdentity(), $viewer_id);
      if (false == $isWatching) {
        $isWatching = null;
      } else {
        $isWatching = (bool) $isWatching;
      }
    }
    $this->view->isWatching = $isWatching;

    //GET POST ID
    $this->view->post_id = $post_id = (int) $this->_getParam('post');

    $table = Engine_Api::_()->getDbtable('posts', 'sitereview');
    $select = $table->select()
            ->where('listing_id = ?', $sitereview->getIdentity())
            ->where('topic_id = ?', $topic->getIdentity())
            ->order('creation_date ASC');
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);

    //SKIP TO PAGE OF SPECIFIED POST
    if (0 != ($post_id = (int) $this->_getParam('post_id')) &&
            null != ($post = Engine_Api::_()->getItem('sitereview_post', $post_id))) {
      $icpp = $paginator->getItemCountPerPage();
      $page = ceil(($post->getPostIndex() + 1) / $icpp);
      $paginator->setCurrentPageNumber($page);
    }
    //USE SPECIFIED PAGE
    else if (0 != ($page = (int) $this->_getParam('page'))) {
      $paginator->setCurrentPageNumber($this->_getParam('page'));
    }

    if ($canPost && !$topic->closed) {
      $this->view->form = $form = new Sitereview_Form_Post_Create();
      $form->populate(array(
          'topic_id' => $topic->getIdentity(),
          'ref' => $topic->getHref(),
          'watch' => ( false == $isWatching ? '0' : '1' ),
      ));
    }

  }

}