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
class Advancedactivity_View_Helper_ShareIcons extends Zend_View_Helper_Abstract {
  public function shareIcons($action) {

    $item = $action->getShareableItem();
    if (empty($item)) {
      return;
    }
    $shareOptions = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.composer.share.menuoptions', array('facebook','twitter','linkedin','googleplus'));
    $shareOptions = !empty($shareOptions) ? $shareOptions : array();
    $enabledShareOptions = array_merge(array(0 => 0),$shareOptions);
    if (Engine_Api::_()->seaocore()->isSiteMobileModeEnabled()){
      $url = $this->view->url(array('module' => 'advancedactivity', 'controller' => 'index',
        'action' => 'share', 'type' => $item->getType(), 'id' => $item->getIdentity()), 'default', true);
    } else {
      $url = $this->view->url(array('module' => 'seaocore', 'controller' => 'activity',
        'action' => 'share', 'type' => $item->getType(), 'id' =>
        $item->getIdentity(), 'action_id' => $action->getIdentity(), 'format' => 'smoothbox',
        "not_parent_refresh" => 1), 'default', true);
    }
    $itemLink = (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $item->getHref();
    
    $urlencode = urlencode($itemLink);
    $shareIcons = array();
    $relPath = 'application/modules/Seaocore/externals/images/icons/share/';
    $webIcon = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.icon', '');
    $shareIcons[] = array(
      'caption' => Engine_Api::_()->getApi('settings', 'core')->getSetting('core_general_site_title', $this->view->translate('_SITE_TITLE')),
      'type' => 'web',
      'icon' => $webIcon ? $webIcon :  'application/modules/Advancedactivity/externals/images/web.png',
      'target' => $url,
      'href' => $url,
      'class' => 'smoothbox web-share'
    );
    $shareIcons['facebook'] = array(
      'caption' => 'Facebook',
      'type' => 'facebook',
      'icon' => $relPath . 'facebook.png',
      'target' => 'https://www.facebook.com/sharer/sharer.php?u='.$urlencode,
      'href' => 'https://www.facebook.com/sharer/sharer.php?u='.$urlencode,
      'blank' => true
    );
    $shareIcons['twitter'] = array(
      'caption' => 'Twitter',
      'type' => 'twitter',
      'icon' => $relPath . 'twitter.png',
      'target' => 'https://twitter.com/share?text='.$item->getTitle(),
      'href' => 'https://twitter.com/share?text='.$item->getTitle(),
      'blank' => true
    );
    $shareIcons['linkedin'] = array(
      'caption' => 'Linkedin',
      'type' => 'linkedin',
      'icon' => $relPath . 'linkedin.png',
      'target' => 'https://www.linkedin.com/shareArticle?mini=true&url='.$itemLink,
      'href' => 'https://www.linkedin.com/shareArticle?mini=true&url='.$itemLink,
      'blank' => true
    );

    $shareIcons['googleplus'] = array(
      'caption' => 'Google Plus',
      'type' => 'linkedin',
      'icon' => $relPath . 'googleplus.png',
      'target' => 'https://plus.google.com/share?url='.$urlencode.'&t='.$item->getTitle(),
      'href' => 'https://plus.google.com/share?url='.$urlencode.'&t='.$item->getTitle(),
      'blank' => true
    );
    $coreSettings = Engine_Api::_()->getApi('settings', 'core');
    if( Engine_Api::_()->hasModuleBootstrap('siteshare') && $coreSettings->getSetting('siteshare.share.bookmarks.enabled', 1) && !Engine_Api::_()->seaocore()->isSiteMobileModeEnabled() ) {
      $socialMoreUrl = $this->view->url(array('module' => 'siteshare', 'controller' => 'index',
        'action' => 'socail-share', 'subject' => $item->getGuid(), 'format' => 'smoothbox'), 'default', true);
      $shareIcons['social_more'] = array(
        'caption' => 'More',
        'type' => 'social_more',
        'icon' => $relPath . 'social_more.png',
        'target' => $socialMoreUrl,
        'href' => $socialMoreUrl,
        'class' => 'seao_smoothbox socialshare_more',
        'attribs' => 'data-SmoothboxSEAOClass="siteshare_socialshare_smoothbox" '
      );
    }
    $shareIcons = array_intersect_key($shareIcons, array_flip($enabledShareOptions));
    return $this->view->partial(
        '_iconsToolBarTip.tpl', 'seaocore', array(
        'icons' => $shareIcons,
        'class' => 'aaf_share_toolbar',
        'id' => 'aaf_share_toolbar_' . $action->action_id
        )
    );
  }
}
