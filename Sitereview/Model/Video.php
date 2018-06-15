<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Video.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_Video extends Core_Model_Item_Abstract {

  protected $_parent_type = 'sitereview_listing';
  protected $_owner_type = 'user';
  protected $_parent_is_owner = false;

  /**
   * Return listing object
   *
   * @return listing object
   * */
  public function getParent($recurseType = null) {
    
    if($recurseType == null) $recurseType = 'sitereview_listing';
    
    return Engine_Api::_()->getItem($recurseType, $this->listing_id);
  }

  /**
   * Gets an absolute URL to the listing to view this item
   *
   * @return string
   */
  public function getHref($params = array()) {

    $parent = $this->getParent();
    $listingtype_id = $parent->listingtype_id;
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $params = array_merge(array(
        'route' => 'sitereview_video_view_listtype_' . $listingtype_id,
        'reset' => true,
        'listing_id' => $this->listing_id,
        'user_id' => $this->owner_id,
        'video_id' => $this->video_id,
        'slug' => $this->getSlug(),
            // 'tab' => $tab_id,
            ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
                    ->assemble($params, $route, $reset);
  }

  /**
   * Return a truncate video owner name
   *
   * @return truncate owner name
   * */
  public function truncateOwner($owner_name) {
    $tmpBody = strip_tags($owner_name);
    return ( Engine_String::strlen($tmpBody) > 10 ? Engine_String::substr($tmpBody, 0, 10) . '..' : $tmpBody );
  }

  /**
   * Make format for activity feed
   *
   * @return activity feed content
   */
  public function getRichContent($view = false, $params = array()) {

    $session = new Zend_Session_Namespace('mobile');
    $mobile = $session->mobile;

    //VIDEO TYPE IS YOUTUBE
    if ($this->type == 1) {
      $videoEmbedded = $this->compileYouTube($this->video_id, $this->code, $view, $mobile);
    }

    //VIDEO TYPE IS VIMEO
    if ($this->type == 2) {
      $videoEmbedded = $this->compileVimeo($this->video_id, $this->code, $view, $mobile);
    }

    //VIDEO TYPE IS MY COMPUTER
    if ($this->type == 3) {
      $storage_file = Engine_Api::_()->storage()->get($this->file_id, $this->getType());
      $video_location = $storage_file->getHref();
      if ($storage_file->extension === 'flv') {
        $videoEmbedded = $this->compileFlowPlayer($video_location, $view);
      } else {
        $videoEmbedded = $this->compileHTML5Media($video_location, $view);
      }
    }

    //THIS RICH IS REQUESTED FROM THE ACTIVITY FEED
    if ($view == false) {

      //DURATION
      $video_duration = "";
      if ($this->duration) {
        if ($this->duration > 360)
          $duration = gmdate("H:i:s", $this->duration);
        else
          $duration = gmdate("i:s", $this->duration);
        if ($duration[0] == '0')
          $duration = substr($duration, 1);
        $video_duration = "<span class='sr_video_length'>" . $duration . "</span>";
      }

      //THUMBNAIL
      $thumb = Zend_Registry::get('Zend_View')->itemPhoto($this, 'thumb.video.activity');

      if ($this->photo_id) {
        $thumb = Zend_Registry::get('Zend_View')->itemPhoto($this, 'thumb.video.activity');
      } else {
        $thumb = '<img alt="" src="'.Zend_Registry::get('Zend_View')->layout()->staticBaseUrl.'application/modules/Video/externals/images/video.png">';
      }

      $thumb = '<a id="video_thumb_' . $this->video_id . '" style="" href="javascript:void(0);" onclick="javascript:var myElement = $(this);myElement.style.display=\'none\';var next = myElement.getNext(); next.style.display=\'block\';">
              <div class="sr_video_thumb_wrapper">' . $video_duration . $thumb . '</div>
              </a>';

      //TITLE AND DESCRIPTION
      $title = "<a href='" . $this->getHref($params) . "'>$this->title</a>";
      $tmpBody = strip_tags($this->description);
      $description = "<div class='video_desc'>" . (Engine_String::strlen($tmpBody) > 255 ? Engine_String::substr($tmpBody, 0, 255) . '...' : $tmpBody) . "</div>";

      $videoEmbedded = $thumb . '<div id="video_object_' . $this->video_id . '" style="display:none;">' . $videoEmbedded . '</div><div class="video_info">' . $title . $description . '</div>';
    }
    return $videoEmbedded;
  }

  public function getEmbedCode(array $options = null) {
    $options = array_merge(array(
        'height' => '525',
        'width' => '525',
            ), (array) $options);
    $temp_server = constant('_ENGINE_SSL') ? 'https://' : 'http://';
    $view = Zend_Registry::get('Zend_View');
    $url = $temp_server . $_SERVER['HTTP_HOST']
            . Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
                'module' => 'sitereview',
                'controller' => 'video',
                'action' => 'external',
                'video_id' => $this->getIdentity(),
                    ), 'default', true) . '?format=frame';
    return '<iframe '
            . 'src="' . $view->escape($url) . '" '
            . 'width="' . sprintf("%d", $options['width']) . '" '
            . 'height="' . sprintf("%d", $options['width']) . '" '
            . 'style="overflow:hidden;"'
            . '>'
            . '</iframe>';
  }

  public function compileYouTube($video_id, $code, $view, $mobile = false) {
    //560 x 340
    //legacy youtube embed code
    $temp_server = constant('_ENGINE_SSL') ? 'https://' : 'http://';
    if (!$mobile) {
      $embedded = '
      <object width="' . ($view ? "560" : "425") . '" height="' . ($view ? "340" : "344") . '">
      <param name="movie" value="'.$temp_server.'www.youtube.com/v/' . $code . '&color1=0xb1b1b1&color2=0xcfcfcf&hl=en_US&feature=player_embedded&fs=1"/>
      <param name="allowFullScreen" value="true"/>
      <param name="allowScriptAccess" value="always"/>
      <embed src="'.$temp_server.'www.youtube.com/v/' . $code . '&color1=0xb1b1b1&color2=0xcfcfcf&hl=en_US&feature=player_embedded&fs=1' . ($view ? "" : "&autoplay=1") . '" type="application/x-shockwave-flash" allowfullscreen="true" allowScriptAccess="always" width="' . ($view ? "560" : "425") . '" height="' . ($view ? "340" : "344") . '" wmode="transparent"/>
      <param name="wmode" value="transparent" />
      </object>';
    } else {
      $autoplay = !$mobile && !$view;

      $embedded = '
        <iframe
        title="YouTube video player"
        id="videoFrame' . $video_id . '"
        class="youtube_iframe' . ($view ? "_big" : "_small") . '"' .
              /*
                width="'.($view?"560":"425").'"
                height="'.($view?"340":"344").'"
               */'
        src="'.$temp_server.'www.youtube.com/embed/' . $code . '?wmode=opaque' . ($autoplay ? "&autoplay=1" : "") . '"
        frameborder="0"
        allowfullscreen="">
        </iframe>';
    }


    return $embedded;
  }

  public function compileVimeo($video_id, $code, $view, $mobile = false) {
    //640 x 360
    $temp_server = constant('_ENGINE_SSL') ? 'https://' : 'http://';
    if (!$mobile) {
      $embedded = '
      <object width="' . ($view ? "560" : "425") . '" height="' . ($view ? "340" : "344") . '">
      <param name="allowfullscreen" value="true"/>
      <param name="allowscriptaccess" value="always"/>
      <param name="movie" value="'.$temp_server.'vimeo.com/moogaloop.swf?clip_id=' . $code . '&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1" />
      <embed src="'.$temp_server.'vimeo.com/moogaloop.swf?clip_id=' . $code . '&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1' . ($view ? "" : "&amp;autoplay=1") . '" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="' . ($view ? "640" : "400") . '" height="' . ($view ? "360" : "230") . '" wmode="transparent"/>
      <param name="wmode" value="transparent" />
      </object>';
    } else {
      $autoplay = !$mobile && !$view;

      $embedded = '
        <iframe
        title="Vimeo video player"
        id="videoFrame' . $video_id . '"
        class="vimeo_iframe' . ($view ? "_big" : "_small") . '"' .
              /*
                width="'.($view?"640":"400").'"
                height="'.($view?"360":"230").'"
               */'
        src="'.$temp_server.'player.vimeo.com/video/' . $code . '?title=0&amp;byline=0&amp;portrait=0&amp;wmode=opaque' . ($autoplay ? "&amp;autoplay=1" : "") . '"
        frameborder="0"
        allowfullscreen="">
        </iframe>';
    }

    return $embedded;
  }

  /**
   * Return keywords
   *
   * @param char separator 
   * @return keywords
   * */
  public function getKeywords($separator = ' ') {
    $keywords = array();
    foreach ($this->tags()->getTagMaps() as $tagmap) {
      $tag = $tagmap->getTag();
      $keywords[] = $tag->getTitle();
    }

    if (null == $separator) {
      return $keywords;
    }

    return join($separator, $keywords);
  }

  public function compileFlowPlayer($location, $view) {

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
$coreVersion = Engine_Api::_()->getDbtable('modules', 'core')->getModule('core')->version;
$checkVersion = Engine_Api::_()->sitereview()->checkVersion($coreVersion, '4.8.10');
       $flowplayerSwf = $checkVersion == 0 ? '/externals/flowplayer/flowplayer-3.1.5.swf' : '/externals/flowplayer/flowplayer-3.2.18.swf';
    $embedded = "
    <div id='videoFrame" . $this->video_id . "'></div>
    <script type='text/javascript'>
    en4.core.runonce.add(function(){\$('video_thumb_" . $this->video_id . "').removeEvents('click').addEvent('click', function(){flashembed('videoFrame$this->video_id',{src: '" . $view->layout()->staticBaseUrl . $flowplayerSwf."', width: " . ($view ? "480" : "420") . ", height: " . ($view ? "386" : "326") . ", wmode: 'opaque'},{config: {clip: {url: '$location',autoPlay: " . ($view ? "false" : "true") . ", duration: '$this->duration', autoBuffering: true},plugins: {controls: {background: '#000000',bufferColor: '#333333',progressColor: '#444444',buttonColor: '#444444',buttonOverColor: '#666666'}},canvas: {backgroundColor:'#000000'}}});})});
    </script>";
    return $embedded;
  }

  public function compileHTML5Media($location, $view)
  {
    $embedded = "
    <video id='video".$this->video_id."' controls preload='auto' width='".($view?"480":"420")."' height='".($view?"386":"326")."'>
      <source type='video/mp4;' src=".$location.">
    </video>";
    return $embedded;
  }
  
  public function getAuthorizationItem() {
    return $this->getParent();
  }

  /**
   * Gets a proxy object for the comment handler
   *
   * @return Engine_ProxyObject
   * */
  public function comments() {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('comments', 'core'));
  }

  /**
   * Gets a proxy object for the like handler
   *
   * @return Engine_ProxyObject
   * */
  public function likes() {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('likes', 'core'));
  }

  /**
   * Gets a proxy object for the tags handler
   *
   * @return Engine_ProxyObject
   * */
  public function tags() {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('tags', 'core'));
  }

  protected function _delete() {

    Engine_Api::_()->getDbtable('videoratings', 'sitereview')->delete(array('videorating_id = ?' => $this->video_id));

    // DELETE VIDEOS
    parent::_delete();
  }

}
