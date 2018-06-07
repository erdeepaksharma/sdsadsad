<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: GetContent.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_View_Helper_GetActionBody extends Zend_View_Helper_Abstract
{

  protected $_action;
  protected $_body;
  protected $_wrongFeedAdded = array(
    'profile_photo_update', 'signup', 'friends'
  );

  public function getActionBody($action)
  {
    if( empty($action->body) ) {
      return;
    }
    $this->_action = $action;
    if( in_array($this->_action->type, $this->_wrongFeedAdded) || $action->body === $action->getTypeInfo()->body ) {
      return;
    }
    $this->_body = $action->body;
    $this->convertBody();
    return $this->_body;
  }

  private function getAction()
  {
    return $this->_action;
  }

  private function convertBody()
  {
    $action = $this->getAction();
    if( $action->attachment_count === 0 ) {
      $this->_body = Engine_Api::_()->getApi('activity', 'advancedactivity')->setFeedStyle($this->_body, $this->getAction()->params);
    }
    $body = $this->_body;
    $session = new Zend_Session_Namespace('siteViewModeSM');
    if( isset($session->siteViewModeSM) && in_array($session->siteViewModeSM, array("mobile", "tablet")) ) {
      $body = $this->view->viewMore($body, 255, 250000, 255, false);
      $body = nl2br($body);
    } else {
      $body = nl2br($this->view->viewMoreAdvancedActivity($body, 255, 250000, 255, false));
    }
    $composerOptions = $this->view->settings('advancedactivity.composer.options', array("emotions", "withtags"));
    $this->_body = $body;
    if( in_array("emotions", $composerOptions) ) {
      $this->_body = $this->view->smileyToEmoticons($body);
    }
    $this->makeHashLink();
    $allowType = array(
      'sitetagcheckin_checkin',
      'comment_sitereview_listing',
      'comment_sitereview_review',
      'nestedcomment_sitereview_listing',
      'nestedcomment_sitereview_review',
      'sitetagcheckin_add_to_map',
      'sitetagcheckin_content',
      'sitetagcheckin_lct_add_to_map'
    );
    if( false === strpos($action->type, 'post') && false !== strpos($action->type, 'status') && false !== strpos($action->type, 'photo') && !in_array($action->type, $allowType) ) {
      return;
    }
    $this->makeMentionTagsLink();
  }

  private function makeMentionTagsLink()
  {
    $body = $this->_body;
    $actionParams = (array) $this->getAction()->params;
    if( empty($actionParams['tags']) ) {
      return;
    }
    $tagData = array();
    foreach( (array) $actionParams['tags'] as $key => $tagStrValue ) {
      $tag = Engine_Api::_()->getItemByGuid($key);
      if( !$tag ) {
        continue;
      }
      $replaceStr = '<a class="sea_add_tooltip_link" '
        . 'href="' . $tag->getHref() . '" '
        . 'rel="' . $tag->getType() . ' ' . $tag->getIdentity() . '" >'
        . $tag->getTitle()
        . '</a>';
      $tagData["/" . preg_quote($tagStrValue) . "/"] = $replaceStr;
    }
    $this->_body = preg_replace(array_keys($tagData), $tagData, $body);
  }

  private function makeHashLink()
  {
    $body = $this->_body;
    if( !Engine_Api::_()->hasModuleBootstrap('sitehashtag') ) {
      return;
    }
    if( !Zend_Registry::isRegistered('sitehashtag_activity_content') ) {
      $isEnabled = Engine_Api::_()->getDbtable('contents', 'sitehashtag')->getEnable('activity');
      Zend_Registry::set('sitehashtag_activity_content', $isEnabled);
    }
    if( !Zend_Registry::get('sitehashtag_activity_content') ) {
      return;
    }
    $hashTagStr = $body;
    $hashtags = Engine_Api::_()->sitehashtag()->getHashTags($hashTagStr);
    $hashtags = $hashtags[0];
    if( empty($hashtags) ) {
      return;
    }
    $newHashTagStr = '';
    $convertStr = $body;
    foreach( $hashtags as $hashtag ) {
      $find = strpos($convertStr, $hashtag);
      $substr = $find ? substr($convertStr, 0, $find) : '';
      $newHashTagStr .= $substr . $this->getHashLinkForString($hashtag);
      $convertStr = substr($convertStr, $find + strlen($hashtag));
    }
    $newHashTagStr .= $convertStr;
    $this->_body = $newHashTagStr;
  }

  private function getHashLinkForString($hashtag)
  {
    $view = Zend_Registry::get('Zend_View');
    $url = $this->view->url(array('controller' => 'index', 'action' => 'index'), "sitehashtag_general") . "?search=" . urlencode($hashtag);
    return "<a href='$url'>" . $hashtag . "</a>";
  }

}
