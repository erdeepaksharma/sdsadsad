<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: browse.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<div class="headline">
  <h2>
    <?php echo $this->translate('Review Videos') ?>
  </h2>
  <div class="tabs">
    <?php
      // Render the menu
      echo $this->navigation()
        ->menu()
        ->setContainer($this->navigation)
        ->render();
    ?>
  </div>
</div>

<div class="generic_layout_container layout_right">
  <?php if( $this->form ): ?>
  <div class="generic_layout_container layout_sitereview_video_browse_search">
  <?php echo $this->form->render($this) ?>
  </div>
  <?php endif ?>
</div>

<div class="generic_layout_container layout_middle">
  <?php if( $this->tag ): ?>
    <h3>
      <?php echo $this->translate('Videos using the tag') ?>
      #<?php echo $this->tag ?>
       <a href="<?php echo $this->url(array('listingtype_id' => $this->listingtype_id), 'sitereview_video_general', true) ?>">(x)</a>
    </h3>
  <?php endif; ?>
  
  <?php if( $this->paginator->getTotalItemCount() > 0 ): ?>

    <ul class="videos_browse">
      <?php foreach( $this->paginator as $item ): ?>
        <li>

          <div class="sr_video_thumb_wrapper">
            <?php if( $item->duration ): ?>
            <span class="sr_video_length">
              <?php
                if( $item->duration >= 3600 ) {
                  $duration = gmdate("H:i:s", $item->duration);
                } else {
                  $duration = gmdate("i:s", $item->duration);
                }
                //$duration = ltrim($duration, '0:');
    //              if( $duration[0] == '0' ) {
    //                $duration= substr($duration, 1);
    //              }
                echo $duration;
              ?>
            </span>
            <?php endif ?>
            <?php
              if( $item->photo_id ) {
                echo $this->htmlLink($item->getHref(), $this->itemPhoto($item, 'thumb.normal'));
              } else {
                echo '<img alt="" src="' . $this->escape($this->layout()->staticBaseUrl) . 'application/modules/Video/externals/images/video.png">';
              }
            ?>
          </div>
          <?php echo $this->htmlLink($item->getHref(), $item->getTitle(), array('class' => 'video_title')) ?>
          <div class="video_author">
            <?php echo $this->translate('By') ?>
            <?php echo $this->htmlLink($item->getOwner()->getHref(), $item->getOwner()->getTitle()) ?>
          </div>
          <div class="video_stats">
            <span class="video_views">
              <?php echo $this->translate(array('%1$s view', '%1$s views', $item->view_count), $this->locale()->toNumber($item->view_count)) ?>
            </span>
            <?php if( $item->rating > 0 ): ?>
              <?php for( $x=1; $x<=$item->rating; $x++ ): ?>
                <span class="rating_star_generic rating_star"></span>
              <?php endfor; ?>
              <?php if( (round($item->rating) - $item->rating) > 0): ?>
                <span class="rating_star_generic rating_star_half"></span>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php elseif( $this->category || $this->tag || $this->text ):?>
    <div class="tip">
      <span>
        <?php echo $this->translate('Nobody has posted a video with that criteria.');?>
      </span>
    </div>
  <?php else:?>
    <div class="tip">
      <span>
        <?php echo $this->translate('Nobody has created a video yet.');?>
        <?php if ($this->can_create):?>
          <?php echo $this->translate('Be the first to %1$spost%2$s one!', '<a href="'.$this->url(array('action' => 'create'), "video_general").'">', '</a>'); ?>
        <?php endif; ?>
      </span>
    </div>
  <?php endif; ?>
  
  <?php echo $this->paginationControl($this->paginator, null, null, array(
    'query' => $this->formValues,
    'pageAsQuery' => true,
  )); ?>
</div>
