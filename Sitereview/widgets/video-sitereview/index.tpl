<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php if($this->loaded_by_ajax):?>
  <script type="text/javascript">
    var params = {
      requestParams :<?php echo json_encode($this->params) ?>,
      responseContainer :$$('.layout_sitereview_video_sitereview')
    }
    en4.sitereview.ajaxTab.attachEvent('<?php echo $this->identity ?>',params);
  </script>
<?php endif;?>
  
<?php if($this->showContent): ?>

  <?php if ($this->allowed_upload_video): ?>
    <div class="seaocore_add clear">
      <?php if($this->type_video):?>
        <a href='<?php echo $this->url(array('action' => 'index', 'listing_id' => $this->sitereview->listing_id, 'content_id' => $this->identity), "sitereview_video_upload_listtype_$this->listingtype_id", true) ?>'  class='buttonlink icon_sitereviews_video_new'><?php echo $this->translate('Add Video'); ?></a>
      <?php else:?>
        <?php echo $this->htmlLink(array('route' => "sitereview_video_create_listtype_$this->listingtype_id", 'listing_id' => $this->sitereview->listing_id,'content_id' => $this->identity), $this->translate('Add Video'), array('class' => 'buttonlink icon_sitereviews_video_new')) ?>
      <?php endif;?>

        <?php if ($this->can_edit && count($this->paginator) > 0): ?>
         <a href='<?php echo $this->url(array('listing_id' => $this->sitereview->listing_id), "sitereview_videospecific_listtype_$this->listingtype_id", true) ?>'  class='buttonlink seaocore_icon_edit'><?php echo $this->translate('Edit Videos'); ?></a>
        <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php  if(count($this->paginator) > 0):?>
    <ul class="sr_profile_videos">
      <?php foreach ($this->paginator as $item): ?>
        <li>
          <?php $videoEmbedded=null;?>
          <div class="sr_video_thumb_wrapper">
            <?php if( $item->duration ): ?>
              <span class="sr_video_length">
                <?php
                  if( $item->duration >= 3600 ) {
                    $duration = gmdate("H:i:s", $item->duration);
                  } else {
                    $duration = gmdate("i:s", $item->duration);
                  }
                  echo $duration;
                ?>
              </span>
            <?php endif ?>
            <?php
              if( $item->photo_id ) {
                echo $this->htmlLink($item->getHref(array('content_id' => $this->identity)), $this->itemPhoto($item, 'thumb.normal'));
              } else {
                echo '<img alt="" src="' . $this->escape($this->layout()->staticBaseUrl) . 'application/modules/Video/externals/images/video.png">';
              }
              ?>
          </div>
          <div class="sr_profile_video_info o_hidden clr">
            <div class="sr_profile_video_title">
              <?php echo $this->htmlLink($item->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($item->getTitle(), $this->title_truncation), array('class' => 'video_title')) ?>
            </div>
            <div class="sr_profile_video_options clr">
              <?php if (($this->can_edit || ($this->viewer_id) == ($item->owner_id))): ?>
                <?php if (!$this->type_video): ?>
                  <a href='<?php echo $this->url(array('listing_id' => $this->sitereview->listing_id,'video_id' => $item->video_id,'tab' => $this->identity), "sitereview_video_edit_listtype_$this->listingtype_id", true) ?>' title="<?php echo $this->translate('Edit Video'); ?>"><i class="sr_icon seaocore_icon_edit"></i></a>
                <?php elseif($this->can_edit):?>
                  <?php echo $this->htmlLink(Array('action' => 'edit', 'route' => "sitereview_videospecific_listtype_$this->listingtype_id", 'listing_id' => $this->sitereview->getIdentity(),'video_id' => $item->video_id), "<i class='sr_icon seaocore_icon_edit'></i>", array('title'=>$this->translate("Edit Video"))); ?>
                <?php endif; ?>
              <?php endif; ?>

              <?php if (($this->can_edit || ($this->viewer_id) == ($item->owner_id))): ?>
                <?php if($this->type_video):?>
                <?php echo $this->htmlLink(Array('action' => 'delete', 'route' => "sitereview_videospecific_listtype_$this->listingtype_id", 'listing_id' => $this->sitereview->getIdentity(),'video_id' => $item->video_id), "<i class='sr_icon seaocore_icon_delete'></i>", array('class' => 'smoothbox','title'=>$this->translate("Delete Video"))); ?>
                <?php else: ?>  
                 <?php echo $this->htmlLink(Array('route' => "sitereview_video_delete_listtype_$this->listingtype_id", 'listing_id' => $this->sitereview->getIdentity(),'video_id' => $item->video_id,'format'=>'smoothbox'), "<i class='sr_icon seaocore_icon_delete'></i>", array('class' => 'smoothbox','title'=>$this->translate("Delete Video"))); ?>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>	
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else:?>
    <?php if ($this->allowed_upload_video): ?>
      <div class="tip">
        <span>    
          <?php if($this->type_video):?>
            <?php $url = $this->url(array('action' => 'index', 'listing_id' => $this->sitereview->listing_id, 'content_id' => $this->identity), "sitereview_video_upload_listtype_$this->listingtype_id", true);?>
            <?php echo $this->translate('You have not added any video in your '.$this->listing_singular_lc.'. %1$sClick here%2$s to add your first video.', "<a href='$url'>", "</a>"); ?>
          <?php else:?>
          <?php $url = $this->url(array('listing_id' => $this->sitereview->listing_id,'content_id' => $this->identity), "sitereview_video_create_listtype_$this->listingtype_id", true);?>
            <?php echo $this->translate('There are currently no videos in this '.$this->listing_singular_lc.'. %1$sClick here%2$s to add your first video.', "<a href='$url'>", "</a>"); ?>
          <?php endif;?>
        </span>
      </div>
      <br />
    <?php endif; ?>
  <?php endif; ?>

  <div>
    <?php if ($this->paginator->getCurrentPageNumber() > 1): ?>
      <div id="user_group_members_previous" class="paginator_previous">
        <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array( 'onclick' => 'paginateSitereviewVideo(sitereviewVideoPage - 1)', 'class' => 'buttonlink icon_previous')); ?>
      </div>
    <?php endif; ?>
    <?php if ($this->paginator->getCurrentPageNumber() < $this->paginator->count()): ?>
      <div id="user_group_members_next" class="paginator_next">
        <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array( 'onclick' => 'paginateSitereviewVideo(sitereviewVideoPage + 1)', 'class' => 'buttonlink_right icon_next'));?>
      </div>
    <?php endif; ?>
  </div>

  <a id="sitereview_video_anchor" style="position:absolute;"></a>

  <script type="text/javascript">
    var sitereviewVideoPage = <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber()) ?>;
    var paginateSitereviewVideo = function(page) {
      var params = {
          requestParams :<?php echo json_encode($this->params) ?>,
          responseContainer :$$('.layout_sitereview_video_sitereview')
        }
        params.requestParams.content_id = <?php echo sprintf('%d', $this->identity) ?>;
        params.requestParams.page = page;
        en4.sitereview.ajaxTab.sendReq(params);
    }

    en4.core.runonce.add(function() {
      if(en4.sitevideoview){
        en4.sitevideoview.attachClickEvent(Array('video_title','item_photo_sitereview_video','item_photo_video'));
      }
    });
  </script>
<?php endif; ?>