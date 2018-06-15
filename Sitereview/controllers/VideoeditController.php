<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: VideoeditController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_VideoeditController extends Seaocore_Controller_Action_Standard {

    protected $_listingType;

    //COMMON ACTION WHICH CALL BEFORE EVERY ACTION OF THIS CONTROLLER
    public function init() {

        //LOGGED IN USER CAN EDIT OR DELETE VIDEO
        if (!$this->_helper->requireUser()->isValid())
            return;

        //GET LISTING TYPE ID
        $listingtype_id = $this->_getParam('listingtype_id', null);
        Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
        $this->_listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);

        //AUTHORIZATION CHECK
        if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "view_listtype_$listingtype_id")->isValid())
            return;

        //SET SUBJECT
        $listing_id = $this->_getParam('listing_id', $this->_getParam('listing_id', null));
        if ($listing_id) {
            $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
            if ($sitereview) {
                Engine_Api::_()->core()->setSubject($sitereview);
            }
        }

        //SITEREVIEW SUBJECT SHOULD BE SET
        if (!$this->_helper->requireSubject()->isValid()) {
            return;
        }
    }

    //ACTION FOR EDIT THE VIDEO
    public function editAction() {

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        //GET SITEREVIEW SUBJECT
        $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject();

        $this->view->listingtype_id = $listingtype_id = $this->_listingType->listingtype_id;

        $this->view->slideShowEnanle = $this->slideShowEnable($listingtype_id);

        $this->view->listing_singular_uc = ucfirst($this->_listingType->title_singular);
        $this->view->listing_singular_lc = strtolower($this->_listingType->title_singular);

        //AUTHORIZATION CHECK
        if (!$this->_helper->requireAuth()->setAuthParams($sitereview, $viewer, "edit_listtype_$listingtype_id")->isValid()) {
            return;
        }

        $this->view->content_id = Engine_Api::_()->sitereview()->getTabId($listingtype_id, 'sitereview.video-sitereview');

        //SELECTED TAB
        $this->view->TabActive = "video";

        if (!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
            $this->_helper->content
                    ->setContentName("sitereview_videoedit_edit_listtype_$listingtype_id")
                    //->setNoRender()
                    ->setEnabled();
        }

        //GET VIDEOS
        $this->view->type_video = $type_video = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.video');

        if ($type_video && isset($sitereview->main_video['corevideo_id'])) {
            $this->view->main_video_id = $sitereview->main_video['corevideo_id'];
        } elseif (isset($sitereview->main_video['reviewvideo_id'])) {
            $this->view->main_video_id = $sitereview->main_video['reviewvideo_id'];
        }

        $this->view->videos = $videos = array();
        $this->view->integratedWithVideo = false;
        $sitevideoEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitevideo');
        if ($sitevideoEnabled && (Engine_Api::_()->getDbtable('modules', 'sitevideo')->getIntegratedModules(array('enabled' => 1, 'item_type' => "sitereview_listing_$sitereview->listingtype_id", 'item_module' => 'sitereview')))) {
            $params = array();
            $params['parent_type'] = $sitereview->getType() . '_' . $sitereview->listingtype_id;
            $params['parent_id'] = $sitereview->listing_id;
            $this->view->videos = $videos = Engine_Api::_()->getDbTable('videos', 'sitevideo')->getVideoPaginator($params);
            $this->view->integratedWithVideo = true;
        } else {
            if (Engine_Api::_()->sitereview()->enableVideoPlugin() && !empty($type_video)) {
                $this->view->videos = $videos = Engine_Api::_()->getItemTable('sitereview_clasfvideo', 'sitereview')->getListingVideos($sitereview->listing_id, 0, 1);
            } elseif (empty($type_video)) {
                $this->view->videos = $videos = Engine_Api::_()->getItemTable('sitereview_clasfvideo', 'sitereview')->getListingVideos($sitereview->listing_id, 0, 0);
            }
        }

        $allowed_upload_video = Engine_Api::_()->sitereview()->allowVideo($sitereview, $viewer, count($videos), $uploadVideo = 1);
        $this->view->upload_video = 1;
        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
            $this->view->upload_video = $allowed_upload_video;
        } else {
            if (empty($allowed_upload_video)) {
                return $this->_forwardCustom('requireauth', 'error', 'core');
            }
        }

        $this->view->count = count($videos);

        //MAKE FORM
        $this->view->form = $form = new Sitereview_Form_Video_Editvideo();

        foreach ($videos as $video) {

            $subform = new Sitereview_Form_Video_Edit(array('elementsBelongTo' => $video->getGuid()));

            if ($video->status != 1) {
                if ($video->status == 0 || $video->status == 2):
                    $msg = $this->view->translate("Your video is currently being processed - you will be notified when it is ready to be viewed.");
                elseif ($video->status == 3):
                    $msg = $this->view->translate("Video conversion failed. Please try again.");
                elseif ($video->status == 4):
                    $msg = $this->view->translate("Video conversion failed. Video format is not supported by FFMPEG. Please try again.");
                elseif ($video->status == 5):
                    $msg = $this->view->translate("Video conversion failed. Audio files are not supported. Please try again.");
                elseif ($video->status == 7):
                    $msg = $this->view->translate("Video conversion failed. You may be over the site upload limit.  Try  a smaller file, or delete some files to free up space.");
                endif;

                $subform->addElement('dummy', 'mssg' . $video->video_id, array(
                    'description' => $msg,
                    'decorators' => array(
                        'ViewHelper',
                        array('HtmlTag', array('tag' => 'div', 'class' => 'tip')),
                        array('Description', array('tag' => 'span', 'placement' => 'APPEND')),
                        array('Description', array('placement' => 'APPEND')),
                    ),
                ));
                $t = 'mssg' . $video->video_id;
                $subform->$t->getDecorator("Description")->setOption("placement", "append");
            }
            $subform->populate($video->toArray());
            $form->addSubForm($subform, $video->getGuid());
        }

        //CHECK METHOD
        if (!$this->getRequest()->isPost()) {
            return;
        }

        //FORM VALIDATION
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        //GET FORM VALUES
        $values = $form->getValues();

        if (isset($_POST['corevideo_cover']) && !empty($_POST['corevideo_cover'])) {
            if (isset($sitereview->main_video) && !empty($sitereview->main_video)) {
                $sitereview->main_video = array_merge((array) $sitereview->main_video, array('corevideo_id' => $_POST['corevideo_cover']));
            } else {
                $sitereview->main_video = array('corevideo_id' => $_POST['corevideo_cover']);
            }
        } elseif (isset($_POST['reviewvideo_cover']) && $_POST['reviewvideo_cover']) {
            if (isset($sitereview->main_video) && !empty($sitereview->main_video)) {
                $sitereview->main_video = array_merge((array) $sitereview->main_video, array('reviewvideo_id' => $_POST['reviewvideo_cover']));
            } else {
                $sitereview->main_video = array('reviewvideo_id' => $_POST['reviewvideo_cover']);
            }
        }

        $sitereview->save();

        //VIDEO SUBFORM PROCESS IN EDITING
        foreach ($videos as $video) {
            $subform = $form->getSubForm($video->getGuid());

            $values = $subform->getValues();
            $values = $values[$video->getGuid()];
            if (isset($values['delete']) && $values['delete'] == '1') {
                Engine_Api::_()->getDbtable('videos', 'sitereview')->delete(array('video_id = ?' => $video->video_id, 'listing_id = ?' => $sitereview->listing_id));
                Engine_Api::_()->getDbtable('actions', 'activity')->delete(array('type = ?' => 'video_sitereview_listtype_' . $listingtype_id, 'object_id = ?' => $sitereview->listing_id));
            } else {
                $video->setFromArray($values);
                $video->save();
            }
        }

        return $this->_helper->redirector->gotoRoute(array('action' => 'edit', 'listing_id' => $sitereview->listing_id), "sitereview_videospecific_listtype_$listingtype_id", true);
    }

    public function deleteAction() {

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //GET VIDEO ID
        $video_id = $this->_getParam('video_id');
        $viewer_id = $viewer->getIdentity();

        //GET SITEREVIEW SUBJECT
        $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject();

        $can_edit = $sitereview->authorization()->isAllowed($viewer, 'edit_listtype_' . $sitereview->listingtype_id);

        $sitereview_video = $video = Engine_Api::_()->getItem('video', $this->_getParam('video_id'));

        //VIDEO OWNER AND LISTING OWNER CAN DELETE VIDEO
        if ($viewer_id != $sitereview_video->owner_id && $can_edit != 1) {
            return $this->_forwardCustom('requireauth', 'error', 'core');
        }

        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('confirm') == true) {

            $db = Engine_Db_Table::getDefaultAdapter();
            $db->beginTransaction();
            try {
                Engine_Api::_()->getDbtable('clasfvideos', 'sitereview')->delete(array('listing_id = ?' => $sitereview->listing_id, 'video_id = ?' => $video_id));
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }

            $this->_forwardCustom('success', 'utility', 'core', array(
                'smoothboxClose' => true,
                'parentRefresh' => '500',
                'parentRefreshTime' => '500',
                'format' => 'smoothbox',
                'messages' => Zend_Registry::get('Zend_Translate')->_('You have successfully deleted this video.')
            ));
        }
    }

    public function slideShowEnable($listingtype_id) {
        //GET CONTENT TABLE
        $tableContent = Engine_Api::_()->getDbtable('content', 'core');
        $tableContentName = $tableContent->info('name');

        //GET PAGE TABLE
        $tablePage = Engine_Api::_()->getDbtable('pages', 'core');
        $tablePageName = $tablePage->info('name');
        //GET PAGE ID
        $page_id = $tablePage->select()
                ->from($tablePageName, array('page_id'))
                ->where('name = ?', "sitereview_index_view_listtype_$listingtype_id")
                ->query()
                ->fetchColumn();

        if (empty($page_id)) {
            return false;
        }

        $content_id = $tableContent->select()
                ->from($tableContent->info('name'), array('content_id'))
                ->where('page_id = ?', $page_id)
                ->where('name = ?', 'sitereview.slideshow-list-photo')
                ->query()
                ->fetchColumn();

        if ($content_id)
            return true;

        $params = $tableContent->select()
                ->from($tableContent->info('name'), array('params'))
                ->where('page_id = ?', $page_id)
                ->where('name = ?', 'sitereview.editor-reviews-sitereview')
                ->query()
                ->fetchColumn();
        if ($params) {
            $params = Zend_Json::decode($params);
            if (!isset($params['show_slideshow']) || $params['show_slideshow']) {
                return true;
            }
            return false;
        }
    }

}
