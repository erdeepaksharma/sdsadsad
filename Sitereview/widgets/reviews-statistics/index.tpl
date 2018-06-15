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

<?php
  $this->headLink()
          ->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_rating.css')
          ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');
?>

<ul class="seaocore_sidebar_list sr_reviews_breakdowns">
  <li>
		<?php echo $this->translate(array('Total <b>%s</b> Review', 'Total <b>%s</b> Reviews', $this->totalReviews), $this->locale()->toNumber($this->totalReviews)) ?>
  </li>
  <li>
  	<b><?php echo $this->translate("Rating Breakdown"); ?></b>
    <div class="sr_rating_breakdowns">
      <ul>
        <?php foreach ($this->ratingCount as $i => $count): ?>
          <li>
           <div class="left"><?php echo $this->translate(array("%s star:", "%s stars:", $i), $i); ?></div>
            <?php $pr = $count > 0 ?($count * 100 / $this->totalReviews):0; ?>
            <div class="count"><?php echo $count; ?></div>
            <div class="rate_bar b_medium">
              <span style="width:<?php echo $pr; ?>%;" <?php echo empty($count) ? "class='sr_border_none'" : "" ?>></span>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </li>
  <li>
    <?php echo $this->translate("%s Out of %1s reviews have recommendations.", '<b>' . $this->totalRecommend . '</b>', '<b>' . $this->totalReviews . '</b>'); ?>
  </li>
</ul>