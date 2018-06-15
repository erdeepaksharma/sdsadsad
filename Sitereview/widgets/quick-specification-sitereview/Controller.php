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
class Sitereview_Widget_QuickSpecificationSitereviewController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {

    //DONT RENDER IF NOT AUTHORIZED
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing') && !Engine_Api::_()->core()->hasSubject('sitereview_review')) {
      return $this->setNoRender();
    }

    $this->view->review = $review = '';
    if (Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject();
    } elseif (Engine_Api::_()->core()->hasSubject('sitereview_review')) {
      $this->view->review = $review = Engine_Api::_()->core()->getSubject();
      $this->view->sitereview = $sitereview = $review->getParent();
    }

    //LISITNG SHOULD BE MAPPED WITH PROFILE
    if (empty($this->view->sitereview->profile_type)) {
      return $this->setNoRender();
    }

    $itemCount = $this->_getParam('itemCount', 5);

    //GET QUICK INFO DETAILS
    $this->view->addHelperPath(APPLICATION_PATH . '/application/modules/Sitereview/View/Helper', 'Sitereview_View_Helper');
    $this->view->fieldStructure = Engine_Api::_()->fields()->getFieldsStructurePartial($sitereview);

    if (Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
			$this->view->show_fields = $this->view->fieldValueLoopQuickInfo($sitereview, $this->view->fieldStructure, $itemCount);
    } else {
      $this->view->show_fields = $this->view->fieldValueLoopQuickInfoSM($sitereview, $this->view->fieldStructure, $itemCount);
    }
    if (empty($this->view->show_fields)) {
      return $this->setNoRender();
    }

    //GET WIDGET SETTINGS
    $this->view->show_specificationlink = $this->_getParam('show_specificationlink', 1);
    
    //GET WIDGET SETTINGS
    $this->view->show_specificationtext = $this->_getParam('show_specificationtext', 'Full Specifications');
    if(empty($this->view->show_specificationtext)) {
      $this->view->show_specificationtext = 'Full Specifications';
    }

    //FETCH CONTENT DETAILS
    if (!empty($review)) {
      $this->view->tab_id = Engine_Api::_()->sitereview()->getTabId($sitereview->listingtype_id, 'sitereview.specification-sitereview');
    } else {
      $this->view->contentDetails = Engine_Api::_()->sitereview()->getWidgetInfo('sitereview.specification-sitereview', $this->view->identity);

      if (empty($this->view->contentDetails)) {
        $this->view->contentDetails->content_id = 0;
      }
    }
  }

}