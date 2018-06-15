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
<?php $count = $this->paginator->getTotalItemCount(); ?>
<?php if ($count > 0): ?>
<?php if(!$this->autoContentLoad):?>
  <div class="ui-member-list-head">
    <?php echo $this->translate(array("%s review found.", "%s reviews found.", $count), $this->locale()->toNumber($count)) ?>

  </div>
  <div class="sm-content-list">
    <ul class="sr_reviews_listing" data-role="listview" <?php if (Engine_Api::_()->sitemobile()->isApp()): ?>data-icon="angle-right"<?php else: ?>data-icon="arrow-r"<?php endif?> id='browsereview_ul'>
      <?php endif;?>
      <?php foreach ($this->paginator as $review): ?>
        <li>
          <a href="<?php echo $review->getHref() ?>" >

            <?php if ($review->owner_id): ?>
              <?php echo $this->itemPhoto($review->getOwner(), 'thumb.icon', $review->getOwner()->getTitle()) ?>
            <?php else: ?>
              <?php $itemphoto = $this->layout()->staticBaseUrl . "application/modules/User/externals/images/nophoto_user_thumb_icon.png"; ?>
              <img src="<?php echo $itemphoto; ?>" />
            <?php endif; ?>

            <h3> <?php echo $review->getTitle() ?> </h3>
            <p>  <?php $ratingData = $review->getRatingData(); ?>
              <?php
              $rating_value = 0;
              foreach ($ratingData as $reviewcat):
                if (empty($reviewcat['ratingparam_name'])):
                  $rating_value = $reviewcat['rating'];
                  break;
                endif;
              endforeach;
              ?>
							<?php if (count($ratingData) > 1): ?>
								<?php foreach ($ratingData as $reviewcat): ?>
										<?php if (empty($reviewcat['ratingparam_name'])): ?>
												<?php echo $this->showRatingStar($reviewcat['rating'], $review->type, 'small-star', $review->getParent()->listingtype_id); ?>
										<?php endif; ?>
								<?php endforeach; ?>
              <?php endif; ?> 
<!--              <?php $ratting_typeclass = $review->type == 'editor' ? '_r' : ''; ?>
              <?php for ($x = 1; $x <= $rating_value; $x++): ?>
                <span class="rating_star_generic<?php echo $ratting_typeclass ?> rating_star<?php echo $ratting_typeclass ?>"></span>
							<?php endfor; ?>-->
            </p>
            <p>
						<?php echo $this->translate('For'); ?>  
              <strong><?php echo $review->getParent()->getTitle() ?></strong>
            </p> 
            <p >
              <?php echo $this->timestamp(strtotime($review->modified_date)); ?> -
              <?php if (!empty($review->owner_id)): ?>
                <?php echo $this->translate('by'); ?> <strong><?php echo $review->getOwner()->getTitle() ?></strong> <?php
          if ($review->type == 'editor'):
            echo "(" . $this->translate('Editor') . ")";
          endif;
                ?>
                <?php ?>
              <?php else: ?>
                <?php echo $this->translate('by'); ?><strong> <?php echo $review->anonymous_name; ?> </strong>
    <?php endif; ?>

            </p>

    <?php if (!Engine_Api::_()->sitemobile()->isApp()): ?>   
      <?php if ($review->body): ?>
              <p class="feed_item_link_desc">
                <b><?php echo ($review->type == 'user' || $review->type == 'visitor') ? $this->translate("Summary:") : $this->translate("Conclusion:") ?></b>
                <?php
                $truncation_limit = 100;
                $tmpBody = strip_tags($review->body);
                echo ( Engine_String::strlen($tmpBody) > $truncation_limit ? Engine_String::substr($tmpBody, 0, $truncation_limit) . "... " : $tmpBody );
                ?>
              </p>
      <?php endif; ?>
    <?php endif; ?>

          </a>
        </li>
  <?php endforeach; ?>
<?php if(!$this->autoContentLoad):?>
    </ul>
  </div>
<?php endif; ?>
 <?php if (!Engine_Api::_()->sitemobile()->isApp()): ?> 
  <?php if ($this->paginator->count() > 1): ?>
    <br />
    <?php
    echo $this->paginationControl(
            $this->paginator, null, null, array(
        'pageAsQuery' => false,
        'query' => $this->searchParams
    ));
    ?>
  <?php endif; ?>
 <?php endif; ?>   
<?php elseif ($this->searchParams): ?>
  <div class="tip">
    <span>
  <?php echo $this->translate('Nobody has written a review with that criteria.'); ?> 
    </span>
  </div>    
<?php else: ?>
  <div class="tip">
    <span>
  <?php echo $this->translate('No reviews have been written yet.'); ?>
    </span>
  </div>
<?php endif; ?>

 <?php if (Engine_Api::_()->sitemobile()->isApp()) { ?>
    <script type="text/javascript">       
        sm4.core.runonce.add(function() { 
             var activepage_id = sm4.activity.activityUpdateHandler.getIndexId();
             sm4.core.Module.core.activeParams[activepage_id] = {'currentPage' : '<?php echo sprintf('%d', $this->page) ?>', 'totalPages' : '<?php echo sprintf('%d', $this->totalPages) ?>', 'formValues' : <?php echo json_encode($this->searchParams);?>, 'contentUrl' : '<?php echo $this->url(array('action' => 'browse'));?>', 'activeRequest' : false, 'container' : 'browsereview_ul' }; 
         });
 </script>       
 <?php } ?>   