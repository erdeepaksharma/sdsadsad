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
class Sitereview_Widget_ShowSameTagsController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    
    $video_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('video_id', $this->_getParam('video_id', null));
    $sitereview_video = Engine_Api::_()->getItem('sitereview_video', $video_id);

    if (empty($sitereview_video)) {
      return $this->setNoRender();
    }

    //GET SUBJECT
    $subject = Engine_Api::_()->getItem('sitereview_listing', $sitereview_video->listing_id);

    //GET TAB ID
    $this->view->tab_selected_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('tab');
    $this->view->listing_id = $sitereview_video->listing_id;

    //FETCH VIDEOS
    $params = array();
    $widgetType = 'showsametag';
    $params['resource_type'] = $sitereview_video->getType();
    $params['resource_id'] = $sitereview_video->getIdentity();
    $params['video_id'] = $sitereview_video->getIdentity();
    $params['limit'] = $this->_getParam('itemCount', 3);
    $params['view_action'] = 1;
    $this->view->paginator = $paginator = Engine_Api::_()->getDbtable('videos', 'sitereview')->widgetVideosData($params, '', $widgetType);
    $this->view->count_video = Count($paginator);
    $this->view->limit_sitereview_video = $this->_getParam('itemCount', 3);

    if (Count($paginator) <= 0) {
      return $this->setNoRender();
    }
  }

}