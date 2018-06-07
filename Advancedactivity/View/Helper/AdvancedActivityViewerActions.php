<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdvancedActivityViewerActions.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_View_Helper_AdvancedActivityViewerActions extends Zend_View_Helper_Abstract
{

  private $_enabledNestedComment;

  public function advancedActivityViewerActions(array $data = array())
  {
    $isEnabledNestedComment = $this->checkEnabledNestedComment();
    if( $isEnabledNestedComment ) {
      $data = array_merge($data, array('replyForm' => new Nestedcomment_Form_Reply()));
      return $this->view->partial(
          '_activityViewerActions.tpl', 'nestedcomment', $data
      );
    } else {
      return $this->view->partial(
          '_activityViewerActions.tpl', 'advancedactivity', $data
      );
    }
  }

  private function checkEnabledNestedComment()
  {
    if( $this->_enabledNestedComment == null ) {
      $this->_enabledNestedComment = Engine_Api::_()->seaocore()->checkEnabledNestedComment('advancedactivity');
    }
    return $this->_enabledNestedComment;
  }

}
