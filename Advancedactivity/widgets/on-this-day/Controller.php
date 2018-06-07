<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Widget_OnThisDayController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {

    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $request = new Zend_Controller_Request_Http();
    $dayCookie = $request->getCookie('shared_this');
    if(!empty($viewer)){
        $viewer_id = $viewer->getIdentity();
    }
    if( empty($viewer_id) || !empty($dayCookie)) {
      return $this->setNoRender();
    }
    if( !Engine_Api::_()->authorization()->isAllowed('advancedactivity_feed', $viewer, 'aaf_memories_enable') ) {
      return $this->setNoRender();
    }
    try {
      $actionTable = Engine_Api::_()->getDbtable('actions', 'advancedactivity');
      $onThisday = $actionTable->getOnThisDayActivity($viewer);
      
      if( empty($onThisday) ) {
        return $this->setNoRender();
      }
      $this->view->onThisDay = $onThisday;
    } catch( Exception $e ) {
      return $this->setNoRender();
    }
  }

}

?>