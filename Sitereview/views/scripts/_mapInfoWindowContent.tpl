<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _mapInfoWindowContent.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<div id="content">
  <div id="siteNotice">
  </div>
  <div class="sr_map_info_tip o_hidden">
    <div class="sr_map_info_tip_top o_hidden">
    	<div class="fright">
        <span >
          <?php if ($this->sitereview->featured == 1): ?>
          	<i class="sr_icon seaocore_icon_featured" title="<?php echo $this->translate('Featured'); ?>"></i>
          <?php endif; ?>
        </span>
        <span>
          <?php if ($this->sitereview->sponsored == 1 ): ?>
          	<i class="sr_icon seaocore_icon_sponsored" title="<?php echo $this->translate('Sponsored');?>"></i>
          <?php endif; ?>
        </span>
      </div>
    	<div class="sr_map_info_tip_title">
      	<?php echo $this->htmlLink($this->sitereview->getHref(), $this->sitereview->getTitle()) ?>
      </div>
    </div>
    <div class="sr_map_info_tip_photo" >
			<?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)):?>
				<?php if($this->sitereview->newlabel):?>
					<i class="sr_list_new_label" title="<?php echo $this->translate('New'); ?>"></i>
				<?php endif;?>
			<?php endif;?>
      <?php echo $this->htmlLink($this->sitereview->getHref(array('profile_link' => 1)), $this->itemPhoto($this->sitereview, 'sr.thumb.normal')) ?>
    </div>
    <div class="sr_map_info_tip_info">
			<div class="list_rating_star">
        <?php
        if ($this->ratingValue == "rating_both") {
          echo $this->showRatingStar($this->sitereview->rating_editor, "editor", $this->ratingShow, $this->sitereview->listingtype_id);
          echo "<br/>";
          echo $this->showRatingStar($this->sitereview->rating_users, "user", $this->ratingShow, $this->sitereview->listingtype_id);
        } else {
          $ratingValue = $this->ratingValue;
          echo $this->showRatingStar($this->sitereview->$ratingValue, $this->ratingType, $this->ratingShow, $this->sitereview->listingtype_id);
        }
      	?>
      </div>
    	<div class='sr_map_info_tip_info_date'>
      <a href="<?php echo $this->url(array('category_id' => $this->sitereview->category_id, 'categoryname' => $this->sitereview->getCategory()->getCategorySlug()), "sitereview_general_category_listtype_" . $this->sitereview->listingtype_id); ?>"> 
        <?php echo $this->sitereview->getCategory()->getTitle(true) ?>
      </a>

      <div class="sr_map_info_tip_info_date">
        <?php echo $this->timestamp(strtotime($this->sitereview->creation_date)) ?><?php if($this->postedby): ?> - <?php echo $this->translate($this->postedbytext. '_posted_by'); ?> 
        <?php echo $this->htmlLink($this->sitereview->getOwner()->getHref(), $this->sitereview->getOwner()->getTitle()) ?><?php endif; ?>
      </div>

      <?php if(!empty($this->statistics)): ?> 
        <div class="sr_map_info_tip_info_date">

          <?php 

            $statistics = '';

            if(in_array('commentCount', $this->statistics)) {
              $statistics .= $this->translate(array('%s comment', '%s comments', $this->sitereview->comment_count), $this->locale()->toNumber($this->sitereview->comment_count)).', ';
            }

            $listingtypeArray = Zend_Registry::get('listingtypeArray' . $this->sitereview->listingtype_id);
            if(in_array('reviewCount', $this->statistics) && ($listingtypeArray->reviews == 3 || $listingtypeArray->reviews == 2) && (!empty($listingtypeArray->allow_review) || (isset($this->sitereview->rating_editor) && $this->sitereview->rating_editor))) {   
              $statistics .= $this->partial(
              '_showReview.tpl', 'sitereview', array('sitereview'=>$this->sitereview)).', ';
            }

            if(in_array('viewCount', $this->statistics)) {
              $statistics .= $this->translate(array('%s view', '%s views', $this->sitereview->view_count), $this->locale()->toNumber($this->sitereview->view_count)).', ';
            }

            if(in_array('likeCount', $this->statistics)) {
              $statistics .= $this->translate(array('%s like', '%s likes', $this->sitereview->like_count), $this->locale()->toNumber($this->sitereview->like_count)).', ';
            }                 

            $statistics = trim($statistics);
            $statistics = rtrim($statistics, ',');

          ?>

          <?php echo $statistics; ?>

        </div>
      <?php endif ?>  
        
      <?php

      $reviewApi = Engine_Api::_()->sitereview();
      $expirySettings = $reviewApi->expirySettings($this->sitereview->listingtype_id);
      $approveDate = null;
      if ($expirySettings == 2):
        $approveDate = $reviewApi->adminExpiryDuration($this->sitereview->listingtype_id);
      endif;

      ?>        
        
      <?php if ($expirySettings == 2): $exp = $this->sitereview->getExpiryTime();
        echo '<div class="sr_browse_list_info_stat seaocore_txt_light">' . $exp ? $this->translate("Expiry On: %s",$this->locale()->toDate($exp, array('size'=>'medium'))) : '' . '</div>';
        $now = new DateTime(date("Y-m-d H:i:s"));
        $ref = new DateTime($this->locale()->toDate($exp));
        $diff = $now->diff($ref);
        echo '<div class="sr_browse_list_info_stat seaocore_txt_light">';
        echo $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i);
        echo '</div>';

      elseif ($expirySettings == 1 && $this->sitereview->end_date && $this->sitereview->end_date !='0000-00-00 00:00:00'):
        echo '<div class="sr_browse_list_info_stat seaocore_txt_light">' . $this->translate("Ending On: %s",$this->locale()->toDate(strtotime($this->sitereview->end_date), array('size'=>'medium'))) . '</div>';
            $now = new DateTime(date("Y-m-d H:i:s"));
            $ref = new DateTime($this->sitereview->end_date);
            $diff = $now->diff($ref);
            echo '<div class="sr_browse_list_info_stat seaocore_txt_light">';
            echo $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i);
            echo '</div>';

      endif;?>           
        
      <?php if(!empty($this->showContent) && in_array('location', $this->showContent)): ?>  
        <div class="sr_map_info_tip_info_date">
          <i><b><?php echo $this->sitereview->location ?></b></i>
        </div>
      <?php endif; ?>  
        
      <?php if(!empty($this->sitereview->price) && $this->sitereview->price > 0 && Zend_Registry::get('listingtypeArray' . $this->sitereview->listingtype_id)->price  && !empty($this->showContent) && in_array('price', $this->showContent)): ?>
        <div class='sr_browse_list_info_stat seaocore_txt_light'>
           <b><?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($this->sitereview->price); ?></b>
        </div>
      <?php endif; ?>        
    </div>
  </div>
</div>