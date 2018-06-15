<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminClaimController.php 6590 2014-05-19 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_AdminClaimController extends Core_Controller_Action_Admin {

  //ACTION FOR GETTING THE LIST OF CLAIMABLE LISTING CREATORS
  public function indexAction() {

    //GET NAVIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_claim');

    //GET LISTING TYPE ID
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', 0);

    $this->view->listingTypes = $listingTypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypes();
    if (count($listingTypes) == 1)
      $this->view->listingtype_id = 1;

    //FILTER FORM
    $this->view->formFilter = $formFilter = new Sitereview_Form_Admin_Filter();

    //GET LIST MEMBER CLAIM TABLE
    $tableListMemberClaim = Engine_Api::_()->getDbtable('listmemberclaims', 'sitereview');

    //GET LIST MEMBER CLAIM TABLE NAME
    $tableListMemberClaimsName = $tableListMemberClaim->info('name');

    //GET USER TABLE NAME
    $tableUserName = Engine_Api::_()->getDbtable('users', 'user')->info('name');

    //SELECTING THE USERS WHOSE LISTING CAN BE CLAIMED
    $select = $tableListMemberClaim->select()
            ->setIntegrityCheck(false)
            ->from($tableListMemberClaimsName, 'listmemberclaim_id')
            ->join($tableUserName, $tableUserName . '.user_id = ' . $tableListMemberClaimsName . '.user_id', array('username', 'displayname', 'user_id'));
    if (!empty($listingtype_id))
      $select->where($tableListMemberClaimsName . '.listingtype_id = ?', $listingtype_id);
    $values = array();

    if ($formFilter->isValid($this->_getAllParams())) {
      $values = $formFilter->getValues();
    }

    foreach ($values as $key => $value) {
      if (null === $value) {
        unset($values[$key]);
      }
    }

    //VALUES
    $values = array_merge(array(
        'order' => "$tableListMemberClaimsName.user_id",
        'order_direction' => 'DESC',
            ), $values);

    $this->view->assign($values);

    //SELECT
    $select->order((!empty($values['order']) ? $values['order'] : "$tableListMemberClaimsName.user_id" ) . ' ' . (!empty($values['order_direction']) ? $values['order_direction'] : 'DESC' ));

    //MAKE PAGINATOR
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $this->view->paginator->setItemCountPerPage(50);
    $this->view->paginator = $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }

  //ACTION FOR GETTING THE LIST OF CLAIM MEMBER
  public function listclaimmemberAction() {

    //SET LAYOUT
    $this->_helper->layout->setLayout('admin-simple');

    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', 0);

    //FORM
    $form = $this->view->form = new Sitereview_Form_Admin_Listclaimmember();

    //SET ACTION
    $form->setAction($this->getFrontController()->getRouter()->assemble(array()));

    //CHECK FORM VALIDATION
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      //GET VALUES
      $values = $form->getValues();

      //GET USER ID
      $userid = $values['user_id'];

      //CHECK USER ID
      if ($userid == 0) {
        $this->view->status = false;
        $error = Zend_Registry::get('Zend_Translate')->_('This is not a valid user name. Please select a user name from the autosuggest.');
        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
        return;
      }

      //GET DB
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        //GET LIST MEMBER CLAIM TABLE
        $tableListMemberClaim = Engine_Api::_()->getDbTable('listmemberclaims', 'sitereview');

        //FETCH				
        $row = $tableListMemberClaim->fetchRow($tableListMemberClaim->getClaimListMember($userid, $listingtype_id));
        if ($row === null) {
          $row = $tableListMemberClaim->createRow();
          $row->listingtype_id = $listingtype_id;
          $row->user_id = $userid;
          $row->save();
        }
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 300,
          'parentRefresh' => 300,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('The claimed listing creator has been added successfully.'))
      ));
    }
  }

  //ACTION FOR GETTING THE MEMBER WHICH CAN BE CLAIMED THE LISTING
  function getmemberAction() {

    //FETCH USER LIST
    $listingtype_id = $this->_getParam('listingtype_id', 0);
    $userlists = Engine_Api::_()->getDbTable('listmemberclaims', 'sitereview')->getMembers($this->_getParam('text'), $this->_getParam('limit', 40), $listingtype_id);

    //MAKING DATA
    $data = array();
    $mode = $this->_getParam('struct');
    if ($mode == 'text') {
      foreach ($userlists as $userlist) {
        $content_photo = $this->view->itemPhoto($userlist, 'thumb.icon');
        $data[] = array('id' => $userlist->user_id, 'label' => $userlist->displayname, 'photo' => $content_photo);
      }
    } else {
      foreach ($userlists as $userlist) {
        $content_photo = $this->view->itemPhoto($userlist, 'thumb.icon');
        $data[] = array('id' => $userlist->user_id, 'label' => $userlist->displayname, 'photo' => $content_photo);
      }
    }
    return $this->_helper->json($data);
  }

  //ACTION FOR DELETEING THE CLAIMABLE LISTING CREATORS
  public function deleteClaimableMemberAction() {

    //LAYOUT
    $this->_helper->layout->setLayout('admin-simple');

    //CHECK FORM VALIDATION
    if ($this->getRequest()->isPost()) {
      //GET DB
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        //MEMBER DELETE
        Engine_Api::_()->getDbtable('listmemberclaims', 'sitereview')->delete(array('user_id =?' => $this->_getParam('user_id')));
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_(''))
      ));
    }
    //OUTPUT
    $this->renderScript('admin-claim/delete-claimable-member.tpl');
  }

  //ACTION FOR DISPLAYING THE LIST OF CLAIM LISTINGS
  public function processclaimAction() {

    //GET NAVIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_claim');

    //GET LISTING TYPE ID
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', 0);

    //FILTER FORM         
    $this->view->formFilter = $formFilter = new Sitereview_Form_Admin_Filter();

    //CLAIM TABLE
    $tableClaim = Engine_Api::_()->getDbtable('claims', 'sitereview');
    $tableClaimName = $tableClaim->info('name');

    //USER TABLE NAME
    $tableUserName = Engine_Api::_()->getDbtable('users', 'user')->info('name');

    //LISTING TABLE NAME
    $tableListingName = Engine_Api::_()->getDbtable('listings', 'sitereview')->info('name');

    //SELECT
    $select = $tableClaim->select()
            ->setIntegrityCheck(false)
            ->from($tableClaimName)
            ->join($tableUserName, $tableUserName . '.user_id = ' . $tableClaimName . '.user_id', array('displayname'))
            ->join($tableListingName, $tableListingName . '.listing_id = ' . $tableClaimName . '.listing_id', array('title', 'owner_id', 'listingtype_id'))
            ->group($tableClaimName . '.claim_id');
    if (!empty($listingtype_id))
      $select->where($tableListingName . '.listingtype_id = ?', $listingtype_id);

    //VALUES     
    $values = array();
    if ($formFilter->isValid($this->_getAllParams())) {
      $values = $formFilter->getValues();
    }

    foreach ($values as $key => $value) {
      if (null === $value) {
        unset($values[$key]);
      }
    }

    //VALUES
    $values = array_merge(array(
        'order' => 'listing_id',
        'order_direction' => 'DESC',
            ), $values);

    $this->view->assign($values);
    $select->order((!empty($values['order']) ? $values['order'] : "listing_id" ) . ' ' . (!empty($values['order_direction']) ? $values['order_direction'] : 'DESC' ));

    //MAKE PAGINATOR			
    $this->view->paginator = array();
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $this->view->paginator->setItemCountPerPage(50);
    $this->view->paginator = $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }

  //ACTION FOR WHAT SHOULD BE HAPPEN WITH THE LISTINGS WHICH ARE CLAIMED BY THE USERS
  public function takeActionAction() {

    //GET LISTING ID
    $this->view->listing_id = $listing_id = $this->_getParam('listing_id');

    //GET CLAIM ID
    $claimid = $this->_getParam('claim_id');

    //GET SITEREVIEW ITEM
    $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

    //OLD OWNER ID
    $oldownerid = $sitereview->owner_id;

    $listing_type = Engine_Api::_()->getDbtable('listingtypes', 'sitereview')->getListingTypeColumn($sitereview->listingtype_id, 'title_singular');

    //SEND SITEREVIEW TITLE TO THE TPL
    $listingtitle = $this->view->sitereview_title = $sitereview->title;

    //GET CLAIM ITEM
    $claiminfo = Engine_Api::_()->getItem('sitereview_claim', $claimid);

    //CHANGE OWNER ID
    $changeuserid = $claiminfo->user_id;

    //GET CLAIM ROW
    $this->view->claiminfo = $claiminfo;

    //COMMENTS
    $comments_mail = '';

    //CHECK FORM VALIDATION
    if ($this->getRequest()->isPost()) {
      //GET STATUS
      $status = $_POST['status'];

      //GET COMMENTS    
      $comments = $_POST['comments'];

      //CHECK COMMENTS VALIDATION
      if (!empty($comments)) {
        $comments_mail .= Zend_Registry::get('Zend_Translate')->_("Administrator's Comments: ") . $comments;
      }

      //GET MODIFIED DATE
      $modified_date = new Zend_Db_Expr('NOW()');

      //GET DB
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();

      //GET ADMIN EMAIL
      $email = Engine_Api::_()->getApi('settings', 'core')->core_mail_from;

      try {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
        $activityTableName = $activityTable->info('name');

        $select = $activityTable->select()
                ->from($activityTableName)
                ->where('subject_id = ?', $oldownerid)
                ->where('subject_type = ?', 'user')
                ->where('object_id = ?', $listing_id)
                ->where('object_type = ?', 'sitereview_listing')
                ->where('type = ?', 'sitereview_new_listtype_' . $sitereview->listingtype_id)
        ;
        $activityData = $activityTable->fetchRow($select);
        if (!empty($activityData)) {
          $activityData->subject_id = $changeuserid;
          $activityData->save();
          $activityTable->resetActivityBindings($activityData);
        }

        //GET SITEREVIEW TABLE
        $tableListings = Engine_Api::_()->getDbtable('listings', 'sitereview');
        $tableClaim = Engine_Api::_()->getDbtable('claims', 'sitereview');

        $httpVar = _ENGINE_SSL ? 'https://' : 'http://';
        $list_baseurl = $httpVar . $_SERVER['HTTP_HOST'] .
                Zend_Controller_Front::getInstance()->getRouter()->assemble(array('listing_id' => $listing_id, 'slug' => $sitereview->getSlug()), "sitereview_entry_view_listtype_$sitereview->listingtype_id", true);

        //MAKING LISTING TITLE LINK
        $list_title_link = '<a href="' . $list_baseurl . '"  >' . $sitereview->title . ' </a>';

        //CHECK STATUS
        if ($status != 2) {
          if ($status == 1) {

            //UPDATE LISTING TABLE        
            $tableListings->update(array('owner_id' => $claiminfo->user_id), array('listing_id = ?' => $listing_id));
            
            //UPDATE LISTING OTHERINFO TABLE        
            Engine_Api::_()->getDbtable('otherinfo', 'sitereview')->update(array('userclaim' => 0), array('listing_id = ?' => $listing_id));

            //UPDATE PHOTO TABLE
            $photoTable = Engine_Api::_()->getDbtable('photos', 'sitereview');
            $photoTableName = $photoTable->info('name');
            $selectPhotos = $photoTable->select()
                    ->from($photoTableName, array('user_id', 'photo_id', 'collection_id'))
                    ->where('user_id = ?', $oldownerid)
                    ->where('listing_id = ?', $listing_id);
            $photoDatas = $photoTable->fetchAll($selectPhotos);
            foreach ($photoDatas as $photoData) {
              $photoData->user_id = $changeuserid;
              $photoData->save();

              $select = $activityTable->select()
                      ->from($activityTableName, 'subject_id')
                      ->where('subject_id = ?', $oldownerid)
                      ->where('subject_type = ?', 'user')
                      ->where('object_id = ?', $photoData->photo_id)
                      ->where('object_type = ?', 'sitereview_listing')
                      ->where('type = ?', 'sitereview_photo_upload_listtype_' . $sitereview->listingtype_id)
              ;
              $activityDatas = $activityTable->fetchAll($select);
              foreach ($activityDatas as $activityData) {
                $activityData->subject_id = $changeuserid;
                $activityData->save();
                $activityTable->resetActivityBindings($activityData);
              }
            }

            Engine_Api::_()->getDbtable('photos', 'sitereview')->update(array('user_id' => $changeuserid), array('user_id = ?' => $oldownerid, 'listing_id = ?' => $listing_id));

            //UPDATE VIDEO TABLE
            $videoTable = Engine_Api::_()->getDbtable('videos', 'sitereview');
            $videoTableName = $videoTable->info('name');
            $selectVideos = $videoTable->select()
                    ->from($videoTableName, 'owner_id')
                    ->where('owner_id = ?', $oldownerid)
                    ->where('listing_id = ?', $listing_id);
            $videoDatas = $videoTable->fetchAll($selectVideos);
            foreach ($videoDatas as $videoData) {
              $videoData->owner_id = $changeuserid;
              $videoData->save();

              $select = $activityTable->select()
                      ->from($activityTableName, 'subject_id')
                      ->where('subject_id = ?', $oldownerid)
                      ->where('subject_type = ?', 'user')
                      ->where('object_id = ?', $videoData->video_id)
                      ->where('object_type = ?', 'sitereview_listing')
                      ->where('type = ?', 'sitereview_video_new_listtype_' . $sitereview->listingtype_id)
              ;
              $activityDatas = $activityTable->fetchAll($select);
              foreach ($activityDatas as $activityData) {
                $activityData->subject_id = $changeuserid;
                $activityData->save();
                $activityTable->resetActivityBindings($activityData);
              }
            }

            if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('video')) {

              $videoTable = Engine_Api::_()->getDbtable('videos', 'video');
              $videoTableName = $videoTable->info('name');

              $clasfVideoTable = Engine_Api::_()->getDbtable('clasfvideos', 'sitereview');
              $clasfVideoTableName = $clasfVideoTable->info('name');

              $videoDatas = $clasfVideoTable->select()
                      ->setIntegrityCheck()
                      ->from($clasfVideoTableName, array('video_id'))
                      ->joinLeft($videoTableName, "$clasfVideoTableName.video_id = $clasfVideoTableName.video_id", array(''))
                      ->where("$clasfVideoTableName.listing_id = ?", $listing_id)
                      ->where("$videoTableName.owner_id = ?", $oldownerid)
                      ->query()
                      ->fetchAll(Zend_Db::FETCH_COLUMN);

              if (!empty($videoDatas)) {

                $db->update('engine4_video_videos', array('owner_id' => $changeuserid), array('video_id IN (?)' => (array) $videoDatas));

                $select = $activityTable->select()
                        ->from($activityTableName, 'subject_id')
                        ->where('subject_id = ?', $oldownerid)
                        ->where('subject_type = ?', 'user')
                        ->where('object_id IN (?)', $videoDatas)
                        ->where("type = 'video_new' OR type = 'video_sitereview_listtype_$sitereview->listingtype_id'")
                ;
                $activityDatas = $activityTable->fetchAll($select);
                foreach ($activityDatas as $activityData) {
                  $activityData->subject_id = $changeuserid;
                  $activityData->save();
                  $activityTable->resetActivityBindings($activityData);
                }
              }
            }

            //UPDATE REVIEW TABLE
            $reviewTable = Engine_Api::_()->getDbtable('reviews', 'sitereview');
            $previousOwnerReviewed = $reviewTable->canPostReview(array('resource_id' => $listing_id, 'resource_type' => 'sitereview_listing', 'viewer_id' => $oldownerid));
            $newOwnerReviewed = $reviewTable->canPostReview(array('resource_id' => $listing_id, 'resource_type' => 'sitereview_listing', 'viewer_id' => $changeuserid));
            if (!empty($previousOwnerReviewed) && empty($newOwnerReviewed)) {
              $reviewTable->update(array('owner_id' => $changeuserid), array('review_id = ?' => $previousOwnerReviewed));
              $db->update('engine4_sitereview_reviewdescriptions', array('user_id' => $changeuserid), array('review_id = ?' => $previousOwnerReviewed));

              $select = $activityTable->select()
                      ->from($activityTableName, 'subject_id')
                      ->where('subject_id = ?', $oldownerid)
                      ->where('subject_type = ?', 'user')
                      ->where('object_type = ?', 'sitereview_listing')
                      ->where('object_id = ?', $previousOwnerReviewed)
                      ->where('type = ?', 'sitereview_review_add_listtype_' . $sitereview->listingtype_id)
              ;
              $activityDatas = $activityTable->fetchAll($select);
              foreach ($activityDatas as $activityData) {
                $activityData->subject_id = $changeuserid;
                $activityData->save();
                $activityTable->resetActivityBindings($activityData);
              }
            }

            //UPDATE DISCUSSION/TOPIC WORK
            $topicTable = Engine_Api::_()->getDbtable('topics', 'sitereview');
            $topicTableName = $topicTable->info('name');
            $selectTopic = $topicTable->select()
                    ->from($topicTableName, array('user_id', 'lastposter_id'))
                    ->where('user_id = ?', $oldownerid)
                    ->where('listing_id = ?', $listing_id);
            $topicDatas = $topicTable->fetchAll($selectTopic);
            foreach ($topicDatas as $topicData) {
              $topicData->user_id = $changeuserid;
              $topicData->lastposter_id = $changeuserid;
              $topicData->save();

              $select = $activityTable->select()
                      ->from($activityTableName, 'subject_id')
                      ->where('subject_id = ?', $oldownerid)
                      ->where('subject_type = ?', 'user')
                      ->where('object_id = ?', $topicData->topic_id)
                      ->where('type = ?', 'sitereview_topic_create_listtype_' . $sitereview->listingtype_id)
              ;
              $activityDatas = $activityTable->fetchAll($select);
              foreach ($activityDatas as $activityData) {
                $activityData->subject_id = $changeuserid;
                $activityData->save();
                $activityTable->resetActivityBindings($activityData);
              }
            }

            $postTable = Engine_Api::_()->getDbtable('posts', 'sitereview');
            $postTableName = $postTable->info('name');
            $selectPost = $postTable->select()
                    ->from($postTableName, 'user_id')
                    ->where('user_id = ?', $oldownerid)
                    ->where('listing_id = ?', $listing_id);
            $postDatas = $postTable->fetchAll($selectPost);
            foreach ($postDatas as $postData) {
              $postData->user_id = $changeuserid;
              $postData->save();

              $select = $activityTable->select()
                      ->from($activityTableName, 'subject_id')
                      ->where('subject_id = ?', $oldownerid)
                      ->where('subject_type = ?', 'user')
                      ->where('object_id = ?', $postData->post_id)
                      ->where('type = ?', 'sitereview_topic_reply_listtype_' . $sitereview->listingtype_id)
              ;
              $activityDatas = $activityTable->fetchAll($select);
              foreach ($activityDatas as $activityData) {
                $activityData->subject_id = $changeuserid;
                $activityData->save();
                $activityTable->resetActivityBindings($activityData);
              }
            }

            //UPDATE THE POST
            $attachementTable = Engine_Api::_()->getDbtable('attachments', 'activity');
            $attachementTableName = $attachementTable->info('name');

            $select = $activityTable->select()
                    ->from($activityTableName, array('action_id', 'subject_id'))
                    ->where('subject_id = ?', $oldownerid)
                    ->where('subject_type = ?', 'user')
                    ->where('object_id = ?', $listing_id)
                    ->where('object_type = ?', 'sitereview_listing')
                    ->where('type = ?', 'post')
            ;
            $activityDatas = $activityTable->fetchAll($select);
            foreach ($activityDatas as $activityData) {

              $select = $attachementTable->select()
                      ->from($attachementTableName, array('type', 'id'))
                      ->where('action_id = ?', $activityData->action_id);
              $attachmentData = $attachementTable->fetchRow($select);

              if ($attachmentData->type == 'video') {
                $db->update('engine4_video_videos', array('owner_id' => $changeuserid), array('video_id = ?' => $attachmentData->id));
              } elseif ($attachmentData->type == 'album_photo') {
                //UNABLE TO DO THIS CHANGE BECAUSE FOR WALL POST THERE IS ONLY ONE ALBUM PER USER SO WE CAN NOT SAY THAT THIS IS ONLY THE WALL POST POSTED BY SITEREVIEW PROFILE LISTING.
              } elseif ($attachmentData->type == 'music_playlist_song') {
                $db->update('engine4_music_playlists', array('owner_id' => $changeuserid), array('playlist_id = ?' => $attachmentData->id));
              } elseif ($attachmentData->type == 'core_link') {
                $db->update('engine4_core_links', array('owner_id' => $changeuserid), array('link_id = ?' => $attachmentData->id));
              }

              if ($attachmentData->type != 'album_photo') {
                $activityData->subject_id = $changeuserid;
                $activityData->save();
                $activityTable->resetActivityBindings($activityData);
              }
            }

            //SEND CHANGE OWNER EMAIL
            Engine_Api::_()->getApi('mail', 'core')->sendSystem(Engine_Api::_()->getItem('user', $sitereview->owner_id)->email, 'SITEREVIEW_' . $listing_type . '_CHANGEOWNER_EMAIL', array(
                'list_title' => $sitereview->title,
                'list_title_with_link' => $list_title_link,
                'object_link' => $list_baseurl,
                'site_contact_us_link' => $httpVar . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getBaseUrl() . '/help/contact',
                'email' => $email,
                'queue' => false
            ));

            //UPDATE CLAIM TABLE
            $tableClaim->update(array('status' => $status, 'comments' => $comments, 'modified_date' => $modified_date), array('claim_id = ?' => $claimid));

            //SEND EMAIL FOR CLAIM APPROVED
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($claiminfo->email, 'SITEREVIEW_' . $listing_type . '_CLAIM_APPROVED_EMAIL', array(
                'list_title' => $sitereview->title,
                'list_title_with_link' => $list_title_link,
                'object_link' => $list_baseurl,
                'comments' => $comments_mail,
                'my_claim_listings_link' => 'http://' . $_SERVER['HTTP_HOST'] .
                Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'my-listings'), 'sitereview_claim_listtype_' . $sitereview->listingtype_id, true),
                'email' => $email,
                'queue' => false
            ));
          } elseif ($status == 4) {
            //UPDATE CLAIM TABLE
            $tableClaim->update(array('status' => $status, 'comments' => $comments, 'modified_date' => $modified_date), array('claim_id = ?' => $claimid));
            //SEND EMAIL FOR CLAIM HOLD
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($claiminfo->email, 'SITEREVIEW_' . $listing_type . '_CLAIM_HOLDING_EMAIL', array(
                'list_title' => $sitereview->title,
                'list_title_with_link' => $list_title_link,
                'object_link' => $list_baseurl,
                'comments' => $comments_mail,
                'email' => $email,
                'queue' => false
            ));
          }
        } else {

          //UPDATE CLAIM TABLE
          $tableClaim->update(array('status' => 2, 'comments' => $comments, 'modified_date' => $modified_date), array('listing_id = ?' => $listing_id, 'user_id=?' => $claiminfo->user_id));

          //SEND EMAIL FOR CLAIM DECLINED
          Engine_Api::_()->getApi('mail', 'core')->sendSystem($claiminfo->email, 'SITEREVIEW_' . $listing_type . '_CLAIM_DECLINED_EMAIL', array(
              'list_title' => $sitereview->title,
              'list_title_with_link' => $list_title_link,
              'object_link' => $list_baseurl,
              'comments' => $comments_mail,
              'email' => $email,
              'queue' => false
          ));
        }
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      //REDIRECTING
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 300,
          'parentRefresh' => 300,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your action has been submitted and email successfully sent to the claimer.'))
      ));
    }
  }

  //ACTION FOR DELETING THE CLAIM REQUEST OF THE USERS
  public function requestDeleteAction() {

    //CHECK FORM VALIDATION
    if ($this->getRequest()->isPost()) {
      //GET DB
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        //GET CLAIM ITEM
        $sitereviewclaim = Engine_Api::_()->getItem('sitereview_claim', $this->_getParam('claim_id'));
        if ($sitereviewclaim) {
          $sitereviewclaim->delete();
        }
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      //REDIRECTING
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 20,
          'parentRefresh' => 20,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_(''))
      ));
    }
    //OUTPUT
    $this->renderScript('admin-claim/request-delete.tpl');
  }

  //ACTION FOR MULTI DELETING THE CLAIM REQUEST OF THE USERS
  public function multiDeleteRequestAction() {

    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();

      foreach ($values as $key => $value) {
        if ($key == 'delete_' . $value) {
          Engine_Api::_()->getItem('sitereview_claim', (int) $value)->delete();
        }
      }
    }

    //REDIRECTING
    $this->_helper->redirector->gotoRoute(array('action' => 'processclaim'));
  }

  //ACTION FOR MULTIDELETING THE MEMBER OF CLAIMABLE LISTING CREATORS
  public function multiDeleteClaimableMemberAction() {

    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();

      foreach ($values as $key => $value) {
        if ($key == 'delete_' . $value) {
          Engine_Api::_()->getItem('sitereview_listmemberclaims', (int) $value)->delete();
        }
      }
    }
    //REDIRECTING
    $this->_helper->redirector->gotoRoute(array('action' => 'index'));
  }

}