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
class Advancedactivity_View_Helper_GetRichContent extends Zend_View_Helper_Abstract
{
  /**
   * Assembles action string
   *
   * @return string
   */
  public function getRichContent($item, $feedSettings = array())
  {
    if( !$item )
      return;
    switch( $item->getType() ) {
      case 'activity_action':
        $this->view->headLink()
    ->prependStylesheet($this->view->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/styles/style_advancedactivity.css');
        $actionTable = Engine_Api::_()->getDbtable('actions', 'advancedactivity');
        $action = $actionTable->getActionById($item->getIdentity());
        $content = $this->view->partial(
          '_actionAsAttachment.tpl', 'advancedactivity', array('action' => $action, 'feedSettings' => $feedSettings));
        break;
      case 'poll':
        $view = Zend_Registry::get('Zend_View');
        $view = clone $view;
        $view->clearVars();
        $view->addScriptPath('application/modules/Poll/views/scripts/');

        $content = '';
        $content .= '
					<div class="feed_poll_rich_content">
						<div class="feed_item_link_title">
							' . $view->htmlLink($item->getHref(), $item->getTitle(), array('class' => 'sea_add_tooltip_link', 'rel' => $item->getType() . ' ' . $item->getIdentity())) . '
						</div>
						<div class="feed_item_link_desc">
							' . $view->viewMore($item->getDescription()) . '
						</div>
				';

        // Render the thingy
        $view->poll = $item;
        $view->owner = $owner = $item->getOwner();
        $view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $view->pollOptions = $item->getOptions();
        $view->hasVoted = $item->viewerVoted();
        $view->showPieChart = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.showpiechart', false);
        $view->canVote = $item->authorization()->isAllowed(null, 'vote');
        $view->canChangeVote = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.canchangevote', false);
        $view->hideLinks = true;

        $content .= $view->render('_poll.tpl');

        /* $content .= '
          <div class="poll_stats">
          '; */

        $content .= '
					</div>
				';
        break;
      case 'sitenews_news':
        $newsHelper = new Sitenews_View_Helper_GetRichContent();  
        $content = $newsHelper->getRichContent($item);
        break;
      case 'video':
        if( Engine_Api::_()->hasModuleBootstrap('sitevideo') ) {
          $videoHelper = new Sitevideo_View_Helper_GetRichContent();
          $content = $videoHelper->getRichContent($item);
          break;
        }
        $session = new Zend_Session_Namespace('mobile');
        $mobile = $session->mobile;
        $view = false;
        $params = array();


        if( strtolower($item->getModuleName()) == 'ynvideo' ) {
          //compitable with younet advanced video.
          $paramsForCompile = array_merge(array(
            'video_id' => $item->video_id,
            'code' => $item->code,
            'view' => $view,
            'mobile' => $mobile,
            'duration' => $item->duration
            ), $params);
          if( $item->type == Ynvideo_Plugin_Factory::getUploadedType() ) {
            $paramsForCompile['location'] = Engine_Api::_()->storage()->get($item->file_id, $item->getType())->getHref();
          }

          $videoEmbedded = Ynvideo_Plugin_Factory::getPlugin((int) $item->type)->compileVideo($paramsForCompile);
        } else {
          // if video type is youtube

          if( $item->type == 1 || $item->type == 'youtube' ) {
            $videoEmbedded = $item->compileYouTube($item->video_id, $item->code, $view, $mobile);
          }
          // if video type is vimeo
          if( $item->type == 2 || $item->type == 'vimeo' ) {
            $videoEmbedded = $item->compileVimeo($item->video_id, $item->code, $view, $mobile);
          }
          // if video type is iframely
          if( $item->type == 'iframely' ) {
            $videoEmbedded = $item->code;
          }
          // if video type is dailymotion
          if( $item->type == 4 || $item->type == 'dailymotion' ) {
            if( strtolower($item->getModuleName()) == 'sitevideo' ) {
              $item = Engine_Api::_()->getItem('sitevideo_video', $item->video_id);
            }
            $videoEmbedded = $item->compileDailymotion($item->video_id, $item->code, $view, $mobile);
          }

          // if video type is uploaded
          if( $item->type == 3 || $item->type == 'upload' ) {

            $storage_file = Engine_Api::_()->storage()->get($item->file_id, $item->getType());
            $video_location = $storage_file->getHref();
            if( $storage_file->extension === 'flv' ) {
              $videoEmbedded = $item->compileFlowPlayer($video_location, $view);
            } else {
              $videoEmbedded = $item->compileHTML5Media($video_location, $view);
            }
          }
        }

        // $view == false means that this rich content is requested from the activity feed
        if( $view == false ) {

          // prepare the duration
          //
          $video_duration = "";
          if( $item->duration ) {
            if( $item->duration >= 3600 ) {
              $duration = gmdate("H:i:s", $item->duration);
            } else {
              $duration = gmdate("i:s", $item->duration);
            }
            $duration = ltrim($duration, '0:');

            $video_duration = "<span class='video_length'>" . $duration . "</span>";
          }

          // prepare the thumbnail
          $thumb = '';
          if( $item->type != 'iframely' ) {
            $thumb = Zend_Registry::get('Zend_View')->itemPhoto($item, 'thumb.video.activity', null, array('width' => '250px'));

            if( $item->photo_id ) {
              $thumb = Zend_Registry::get('Zend_View')->itemPhoto($item, 'thumb.video.activity', null, array('width' => '250px'));
            } else {
              $thumb = '<img alt="" src="' . Zend_Registry::get('StaticBaseUrl') . 'application/modules/Sitevideo/externals/images/video_default.png">';
            }

            if( !$mobile ) {
              $thumb = '<a class="Sitevideo_thumb" id="video_thumb_' . $item->video_id . '" style="" href="javascript:void(0);" onclick="javascript:var myElement = $(this);myElement.style.display=\'none\';var next = myElement.getNext(); next.style.display=\'block\';">
                  <div class="video_thumb_wrapper"><span class="video_overlay"></span> <span class="play_icon"></span>' . $video_duration . $thumb . '</div>
                  </a>';
            } else {
              $thumb = '<a class="Sitevideo_thumb" id="video_thumb_' . $item->video_id . '" class="video_thumb" href="javascript:void(0);" onclick="javascript: $(\'videoFrame' . $item->video_id . '\').style.display=\'block\'; $(\'videoFrame' . $item->video_id . '\').src = $(\'videoFrame' . $item->video_id . '\').src; var myElement = $(this); myElement.style.display=\'none\'; var next = myElement.getNext(); next.style.display=\'block\';">
                  <div class="video_thumb_wrapper"><span class="video_overlay"></span> <span class="play_icon"></span>' . $video_duration . $thumb . '</div>
                  </a>';
            }
          }
          // prepare title and description
          $title = "<div class='feed_item_link_title'><a class='sea_add_tooltip_link feed_video_title' rel= \"" . $item->getType() . ' ' . $item->getIdentity() . "\" href='" . $item->getHref($params) . "' >$item->title</a></div>";
          $tmpBody = strip_tags($item->description);
          $description = "<div class='video_desc'>" . (Engine_String::strlen($tmpBody) > 255 ? Engine_String::substr($tmpBody, 0, 255) . '...' : $tmpBody) . "</div>";
          if( $item->type == 'iframely' ) {
            $videoEmbedded = $thumb . '<div id="video_object_' . $item->video_id . '" class="video_object video_object_' . $item->type . '" >' . $videoEmbedded . '
            </div><div class="video_info">' . $title . $description . '</div>';
          } else {
            $videoEmbedded = $thumb . '<div id="video_object_' . $item->video_id . '" class="video_object" style="display:none" >' . $videoEmbedded . '
            </div><div class="video_info">' . $title . $description . '</div>';
          }
        }
        // return $videoEmbedded;
        $content = $videoEmbedded;
        break;

      case 'avp_video':
        $view = false;
        $params = array();
        $viewer = Engine_Api::_()->user()->getViewer();
        $group = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('avp_video', $item->video_id, 'auth_view');

        $video_hide = "<div id='avp-video-{$item->video_id}' style='display: none;'></div>";

        if( $item->hasGroupPrivacy() && !in_array($viewer->getIdentity(), array_merge(explode(";", $item->can_view), array($item->owner_id))) ) {
          $video_hide .= <<<EOT
<script type="text/javascript">
//<![CDATA[
avp.hide.id({$item->video_id});
//]]>
</script>   
EOT;
        }

        $override = array(
          'width' => 464,
          'height' => 261,
          'autoplay' => false,
          'autostart' => false
        );

        $description = "<div class='avp_desc'>" . $item->getShortDescription() . "</div>";

        $videoEmbedded = "<div class='video_info'><a href='" . $item->getHref($params) . "'>{$item->title}</a></div>" . $item->getPlayer(false, true, $override) . "<div class='video_info'>{$description}</div>";

        $avp_duration = "";

        if( $item->duration ) {
          if( $item->duration > 3600 )
            $duration = gmdate("H:i:s", $item->duration);
          else
            $duration = gmdate("i:s", $item->duration);

          if( $duration[0] == '0' )
            $duration = substr($duration, 1);
          if( count(explode(":", $duration)) > 2 && substr($duration, 0, 2) == "0:" )
            $duration = substr($duration, 2);

          $avp_duration = "<span class='avp_length'>{$duration}</span>";
        }

        $thumb = "<div class='avp_thumb_wrapper'><div class='avp_thumb_wrapper_play' onclick=\"avpGetById('avp-feed-item-{$item->video_id}').innerHTML=avpUrlDecode('" . urlencode($videoEmbedded) . "')\"></div>{$avp_duration}" . $item->getThumbnail() . "</div>";
        $title = "<a href='" . $item->getHref($params) . "' class='sea_add_tooltip_link feed_video_title' rel= \"" . $item->getType() . ' ' . $item->getIdentity() . "\" >{$item->title}</a>";
        $description = "<div class='avp_desc'>" . $item->getShortDescription() . "</div>";

        $videoEmbedded = '<div id="avp-feed-item-' . $item->getIdentity() . '">' . $thumb . '<div class="video_info">' . $title . $description . '</div></div>';

        return $video_hide . $videoEmbedded;

        $content = $video_hide . $videoEmbedded;
        break;
      case 'core_link' :
        $content = $this->coreLinkContent($item);
        break;
      case 'advancedactivity_sell' :
        $content = $this->advancedactivitySellContent($item, $feedSettings);
        break;
      default:
        $content = $item->getRichContent();
    }

    return $content;
  }

  private function coreLinkContent($link)
  {
    $parseUrl = parse_url($link->uri);
    if( $parseUrl['host'] !== 'soundcloud.com' || empty($parseUrl['path']) || count(explode('/', $parseUrl['path'])) <= 1 ) {
      return $link->getRichContent();
    }
    return '<div><iframe frameborder="no" height="400" width="100%" src="https://w.soundcloud.com/player/?visual=true&amp;url=' . $link->uri . '&amp;show_artwork=true" scrolling="no"></iframe></div>';
  }

  public function advancedactivitySellContent($sell, $feedSettings)
  {

    if( Engine_Api::_()->hasModuleBootstrap('sitemulticurrency') ) {
      $price = Engine_Api::_()->sitemulticurrency()->convertCurrencyRate($sell->price, 0, 0, $sell->currency);
    } else {
      $localeObject = Zend_Registry::get('Locale');
      $symbol = Zend_Locale_Data::getContent($localeObject, 'currencysymbol', $sell->currency);
      $price = $symbol . ' ' . $sell->price;
    }
    $more = '';
    $file_ids = explode(" ", $sell->photo_id);
    $photosCount = count(array_filter($file_ids));
    $viewMaxPhoto = $feedSettings['viewMaxPhoto'];
    if( $photosCount > $viewMaxPhoto ) {
      $more = "aaf_item_attachment_more";
      $photosCount = $viewMaxPhoto;
    }
    $view = $this->view;
    $view->addHelperPath(APPLICATION_PATH . '/application/modules/Fields/View/Helper', 'Fields_View_Helper');
    $fieldStructure = Engine_Api::_()->fields()->getFieldsStructurePartial($sell);

    $includeSym = '<div class="aaf_sell_product_body"> <div class="aaf_sell_product_title">' . $sell->title . ' </div><div class="aaf_sell_product_price">' . $price . ' </div><div class="aaf_sell_product_place">' . $sell->place . ' </div>'
      . $this->view->fieldValueLoop($sell, $fieldStructure);
    $trimDescription = trim($sell->description);
    if( !empty($trimDescription) ) {
      $includeSym .= '<div class="aaf_sell_product_description">' . $sell->description . '</div>';
    }
    $includeSym .= '</div><div class="feed_item_attachments ' . $more . ' aaf_item_attachment_' . $photosCount . '">';

    foreach( array_filter($file_ids) as $file_id ) {
      $file = Engine_Api::_()->getItem('album_photo', $file_id);
      if( !empty($file) ) {
        $includeSym .= '<span class="feed_attachment_album_photo feed_attachment_photo"> <div class="feed_attachment_photo aaf_feed_attachment_photo feed_attachment_aaf"> <a href="' . $file->getHref() . '" onclick="openSeaocoreLightBox(\'' . $file->getHref() . '\'); return false;">';
        if( !empty($more) && $file_ids[$photosCount - 1] == $file_id ) {
          $includeSym .= '<span class="feed_attachment_photo_overlay"></span><span class="feed_attachment_photo_more_count">+' . (count($file_ids) - $photosCount) . '</span><img src="' . $file->getPhotoUrl() . '" class="thumb_main item_photo_album_photo"  /></a></div></span> ';
          break;
        } else {
          $includeSym .= '<img src="' . $file->getPhotoUrl() . '" class="thumb_main item_photo_album_photo"  /></a></div></span> ';
        }
      }
    }
    $includeSym .= '</div>';
    $viewer = Engine_Api::_()->user()->getViewer();
    if(!empty($viewer)){
        $viewer_id = $viewer->getIdentity();
    }
    if( !empty($viewer_id) && $viewer_id == $sell->owner_id ) {
      if( $sell->closed ) {
        $includeSym .= '<span class="aaf_feed_buysell_product_open" onclick="openCloseSell(this,' . $sell->sell_id . ')">' . $view->translate('Mark As Open') . '</span>';
      } else {
        $includeSym .= '<span class="aaf_feed_buysell_product_close" onclick="openCloseSell(this,' . $sell->sell_id . ')">' . $view->translate('Mark As Close') . '</span>';
      }
    } elseif( $sell->closed ) {
      $includeSym .= '<span class="aaf_feed_buysell_product_close">' . $view->translate('Sale Closed') . '</span>';
    } else {
      $includeSym .= '<span class="aaf_feed_buysell_product_open">' . $view->translate('Sale Open') . '</span>';
    }
    return $includeSym;
  }

}
