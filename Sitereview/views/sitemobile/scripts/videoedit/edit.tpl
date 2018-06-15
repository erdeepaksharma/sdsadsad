<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: edit.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>
<?php echo $this->partial('application/modules/Sitereview/views/sitemobile/scripts/dashboard/header.tpl', array('sitereview' => $this->sitereview)); ?>
<div class="dashboard-content">
	<div class="global_form">
    <h3> <?php echo $this->translate("Edit $this->listing_singular_uc Videos"); ?></h3>
    <p class="form-description">
      <?php echo $this->translate("Edit and manage the videos of your $this->listing_singular_lc below."); ?>
      <?php if($this->slideShowEnanle && 0):?>                                                          
        <br />                                                            
        <?php echo $this->translate("An attractive Slideshow will be displayed on your $this->listing_singular_uc Profile page. Below, you can select a video to be displayed in that slideshow by using the 'Show in Slideshow' option."); ?>
        <br />
        <b><?php echo $this->translate("Note: ")?></b><?php echo $this->translate("You can choose a snapshot pic for selected video by visiting the 'Photos' section of this Dashboard.")?>
      <?php endif; ?>
    </p>
    <div class="clr">
      <?php if($this->type_video):?>
        <?php echo $this->htmlLink(array('route' => "sitereview_video_upload_listtype_$this->listingtype_id", 'action' => 'index', 'listing_id' => $this->sitereview->listing_id), $this->translate('Add New Video'), array('data-role'=>'button', 'data-icon'=>'plus')) ?>
      <?php else:?>
      <?php echo $this->htmlLink(array('route' => "sitereview_video_create_listtype_$this->listingtype_id", 'listing_id' => $this->sitereview->listing_id), $this->translate('Add New Video'), array('data-role'=>'button', 'data-icon'=>'plus')) ?>
      <?php endif;?>
    </div>
			
    <form action="<?php echo $this->escape($this->form->getAction()) ?>" method="<?php echo $this->escape($this->form->getMethod()) ?>" class="">
      <ul class='dashboard-content-manage-media' id="video">
        <?php if(!empty($this->count)): ?>
          <?php foreach ($this->videos as $item): ?>
            <li class="b_medium">
              <div class="media-img b_medium">
                <?php if (0): ?>
                  <span class="sr_video_length">
                    <?php
                      if ($item->duration > 360)
                        $duration = gmdate("H:i:s", $item->duration); else
                        $duration = gmdate("i:s", $item->duration);
                      if ($duration[0] == '0')
                        $duration = substr($duration, 1); echo $duration;
                    ?>
                  </span>
                <?php endif; ?>
                <?php 
                  if ($item->photo_id)
                    echo $this->htmlLink($item->getHref(array('listingtype_id' => $this->listingtype_id,'content_id' => $this->content_id)), $this->itemPhoto($item, 'thumb.normal'), array());
                  else
                    echo '<img alt="" src="'. $this->layout()->staticBaseUrl . 'application/modules/Video/externals/images/video.png">';
                ?>
              </div>
              <div class="sr_edit_media_info">
                <?php $key= $item->getGuid();
                  echo $this->form->getSubForm($key)->render($this);
                ?>
                <?php if($this->slideShowEnanle):?>
                  <div class="sr_edit_media_options">
                    <div class="sr_edit_media_options_check">
                      <?php if($this->type_video):?>
                        <?php $cover = 'corevideo_cover';?>
                      <?php else:?>
                        <?php $cover = 'reviewvideo_cover';?>
                      <?php endif;?>
                      <input id="show_slideshow_id_<?php echo $item->video_id ?>" type="radio" name="<?php echo $cover;?>" value="<?php echo $item->video_id ?>" <?php if ($this->main_video_id == $item->video_id): ?> checked="checked"<?php endif; ?> />
                    </div>
                    <div class="sr_edit_media_options_label">
                      <label for="show_slideshow_id_<?php echo $item->video_id ?>" ><?php echo $this->translate('Show in Slideshow'); ?></label>
                    </div>
                  </div>
                <?php endif;?>
              </div>
            </li>
          <?php endforeach; ?>
        <?php else:?>
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
        <?php endif;?>
      </ul>
      <?php if(!empty($this->count)): ?>
        <div class="clr">
            <br />
            <?php echo $this->form->button ?>
        </div>
      <?php endif;?>
    </form>
  </div>
</div>