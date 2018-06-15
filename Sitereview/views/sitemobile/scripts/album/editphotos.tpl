<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: editphotos.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>
<?php echo $this->partial('application/modules/Sitereview/views/sitemobile/scripts/dashboard/header.tpl', array('sitereview' => $this->sitereview)); ?>
<div class="dashboard-content">
  <div class="global_form">
    <h3><?php echo $this->translate("Edit $this->listing_singular_uc Photos"); ?></h3>
    <p class="form-description">
      <?php echo $this->translate("Edit and manage the photos of your $this->listing_singular_lc below."); ?>
      <?php if($this->slideShowEnanle && 0):?>
        <br />
        <?php echo $this->translate("An attractive Slideshow will be displayed on your $this->listing_singular_uc Profile page. Below, you can choose the photos to be displayed in that slideshow by using the 'Show in Slideshow' option."); ?>
        <?php if($this->enableVideoPlugin && $this->allowed_upload_video):?>
          <?php echo $this->translate("You can also choose the photo snapshot pic for the video displayed in the slideshow by using 'Make Video Snapshot' option."); ?>
          <br />
          <b><?php echo $this->translate("Note: ");?></b><?php echo $this->translate("You can select the video to be displayed in the Slideshow from the 'Videos' section of this Dashboard."); ?>
        <?php endif; ?>
      <?php endif; ?>
    </p>
    <?php if(!empty($this->upload_photo)):?>
      <div class="clr">
        <?php echo $this->htmlLink(array('route' => "sitereview_photoalbumupload_listtype_".$this->sitereview->listingtype_id,'album_id' => $this->album_id, 'listing_id' => $this->listing_id), $this->translate('Add New Photos'), array('data-role'=>'button', 'data-icon'=>'plus')) ?>
      </div>
    <?php endif;?>

    <?php if( $this->paginator->count() > 0 ): ?>
      <?php echo $this->paginationControl($this->paginator); ?>
    <?php endif; ?>

    <form action="<?php echo $this->escape($this->form->getAction()) ?>" method="<?php echo $this->escape($this->form->getMethod()) ?>">
      <?php echo $this->form->album_id; ?>
      <ul class='dashboard-content-manage-media' id="photo">
        <?php if(!empty($this->count)): ?>
          <?php foreach ($this->paginator as $photo):?>
            <li class="b_medium">
              <div class="media-img b_medium">
                <?php echo $this->itemPhoto($photo, 'thumb.normal') ?>
              </div>
              <?php
                $key = $photo->getGuid();
                echo $this->form->getSubForm($key)->render($this);
              ?>
              <div class='sr_edit_media_options'>
                <div class="sr_edit_media_options_check">
                  <input id="main_photo_id_<?php echo $photo->photo_id ?>" type="radio" name="cover" value="<?php echo $photo->file_id ?>" <?php if ($this->sitereview->photo_id == $photo->file_id): ?> checked="checked"<?php endif; ?> />
                </div>
                <div class="sr_edit_media_options_label">
                  <label for="main_photo_id_<?php echo $photo->photo_id ?>">
                      <?php if(stristr($this->listing_singular_uc, 'job') || stristr($this->listing_singular_lc, 'job')): ?>
                        <?php echo $this->translate('Company Logo'); ?>
                      <?php else: ?>
                        <?php echo $this->translate('Main Photo'); ?>
                      <?php endif; ?>
                  </label>
                </div>
              </div>
              <?php if($this->enableVideoPlugin && $this->allowed_upload_video): ?>
                <div class="sr_edit_media_options" class='video_snapshot_id-wrapper'>              
                  <div class="sr_edit_media_options_check">
                    <input id="video_snapshot_id_<?php echo $photo->photo_id ?>" type="radio" name="video_snapshot_id" value="<?php echo $photo->photo_id ?>" <?php if ($this->sitereview->video_snapshot_id == $photo->photo_id): ?> checked="checked"<?php endif; ?> />
                  </div>
                  <div class="sr_edit_media_options_label">
                    <label for="video_snapshot_id_<?php echo $photo->photo_id ?>"><?php echo $this->translate('Make Video Snapshot');  ?></label>
                  </div>
                </div>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        <?php else:?>
          <div class="tip">
            <span>
              <?php $url = $this->url(array('listing_id' => $this->listing_id), 'sitereview_photoalbumupload_listtype_'.$this->sitereview->listingtype_id, true);?>
              <?php echo $this->translate('There are currently no photos in this '.$this->listing_singular_lc.'. %1$sClick here%2$s to add photos now!', "<a href='$url'>", "</a>"); ?>
            </span>
          </div>
        <?php endif;?>
      </ul>
      <?php if(!empty($this->count)): ?>
        <div class="clr">
          <br />
          <?php echo $this->form->submit->render(); ?>
        </div>
      <?php endif;?>
    </form>
    <?php if( $this->paginator->count() > 0 ): ?>
      <br />
      <?php echo $this->paginationControl($this->paginator); ?>
    <?php endif; ?>
  </div>
</div>