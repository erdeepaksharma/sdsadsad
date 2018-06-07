<?php $coverSrc = ''; ?>
<?php $coverPhotoId = !empty($this->actionSubject->user_cover) ? $this->actionSubject->user_cover : (!empty($this->actionSubject->coverphoto) ? $this->actionSubject->coverphoto : 0); ?>
<?php if( $coverPhotoId && Engine_Api::_()->hasItemType('album_photo') ): ?>
  <?php $userCoverPhoto = Engine_Api::_()->getItem('album_photo', $coverPhotoId); ?>
  <?php if( $userCoverPhoto ): ?>
    <?php $coverSrc = $userCoverPhoto->getPhotoUrl(); ?>
  <?php endif; ?>
<?php endif; ?>

<?php if( $coverSrc ): ?>
  <div class="aaf_feed_usercover-wappper">
    <div class="user_cover_photo">
      <img style="width: 100% !important;" src="<?php echo $coverSrc; ?>" class="thumb_cover_main"/>
    </div>
    <div class="user_profile_photo">
      <?php echo $this->itemPhoto($this->attachment, $this->attachment->getPhotoUrl() ? 'thumb.main' : 'thumb.normal', $this->attachment->getTitle()) ?>
    </div>
    <div class="profile_photo_username"><?php echo $this->actionSubject->getTitle() ?></div>
  </div>
<?php else: ?>
  <?php echo $this->itemPhoto($this->attachment, $this->attachment->getPhotoUrl() ? 'thumb.main' : 'thumb.normal', $this->attachment->getTitle()) ?>
<?php endif; ?>