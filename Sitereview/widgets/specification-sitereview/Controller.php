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
class Sitereview_Widget_SpecificationSitereviewController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {

    //DONT RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      return $this->setNoRender();
    }

    //GET SUBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');
    if (empty($sitereview->profile_type)) {
      return $this->setNoRender();
    }

    $this->view->addHelperPath(APPLICATION_PATH . '/application/modules/Fields/View/Helper', 'Fields_View_Helper');
    
    $this->view->fieldStructure = Engine_Api::_()->fields()->getFieldsStructurePartial($sitereview);
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    
    
     if (Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
			$this->view->otherDetails = $view->fieldValueLoop($sitereview, $this->view->fieldStructure);
    } else {
      $this->view->addHelperPath(APPLICATION_PATH . '/application/modules/Sitereview/View/Helper', 'Sitereview_View_Helper');
      $this->view->otherDetails = $view->fieldValueLoopSM($sitereview, $this->view->fieldStructure);
    }
    
    if (empty($this->view->otherDetails)) {
      return $this->setNoRender();
    }

    $params = $this->_getAllParams();
    $this->view->params = $params;
    if ($this->_getParam('loaded_by_ajax', false)) {
      $this->view->loaded_by_ajax = true;
      if ($this->_getParam('is_ajax_load', false)) {
        $this->view->is_ajax_load = true;
        $this->view->loaded_by_ajax = false;
        if (!$this->_getParam('onloadAdd', false))
          $this->getElement()->removeDecorator('Title');
        $this->getElement()->removeDecorator('Container');
        Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
      } else {
        return;
      }
    }
    $this->view->showContent = true;
  }

}