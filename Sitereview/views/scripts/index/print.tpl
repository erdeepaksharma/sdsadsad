<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: print.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl.'application/modules/Sitereview/externals/styles/style_sitereview_print.css'); ?>
<link href="<?php echo $this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview_print.css'?>" type="text/css" rel="stylesheet" media="print">

<div class="seaocore_print_page">
	<div class="seaocore_print_title">
		<span class="right">
			<?php echo $this->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.site.title'));?>
		</span>
		<?php if ($this->sitereview->closed != 1): ?>
			<span class="left">
				<?php echo $this->translate($this->sitereview->getTitle()) ?>
			</span>
		<?php endif; ?>	
	</div>
	<div class='seaocore_print_profile_fields'>
		<?php if ($this->sitereview->closed == 1): ?>
			<div class="tip"> 
				<span> <?php echo $this->translate('This '.strtolower($this->listing_singular_lc).' has been closed by the owner.'); ?> </span>
			</div>
			<br/>
		<?php else: ?>
      <div class="seaocore_print_photo">
      	<?php echo $this->itemPhoto($this->sitereview, 'thumb.profile', '' , array('align' => 'left')); ?>
      	<div id="printdiv" class="seaocore_print_button">
					<a href="javascript:void(0);" class="buttonlink seaocore_icon_print" onclick="printData()" align="right"><?php echo $this->translate('Take Print') ?></a>
				</div>
      </div>
      <div class="seaocore_print_details">	      
				<h4>
					<?php echo $this->translate("$this->listing_singular_uc Information") ?>
				</h4>

				<ul>
					<li>
            <span><?php echo $this->translate(strtoupper($this->listingType->title_singular). '_POSTED_BY:'); ?></span>
						<span><?php echo $this->translate($this->sitereview->getParent()->getTitle()) ?></span>
					</li>
					<li>
            <span><?php echo $this->translate(strtoupper($this->listingType->title_singular). '_POSTED_ON:'); ?></span>
						<span><?php echo $this->translate( gmdate('M d, Y', strtotime($this->sitereview->creation_date))) ?></span>
					</li>
					<?php if(!empty($this->sitereview->comment_count)): ?>
						<li>
							<span><?php echo $this->translate('Comments :'); ?></span>
							<span><?php echo $this->translate( $this->sitereview->comment_count) ?></span>
						</li>
					<?php endif; ?>
					<?php if(!empty($this->sitereview->view_count)): ?>
						<li>
							<span><?php echo $this->translate('Views :'); ?></span>
							<span><?php echo $this->translate( $this->sitereview->view_count) ?></span>
						</li>
					<?php endif; ?>
					<?php if(!empty($this->sitereview->like_count)): ?>
						<li>
							<span><?php echo $this->translate('Likes :'); ?></span>
							<span><?php echo $this->translate( $this->sitereview->like_count) ?></span>
						</li>
					<?php endif; ?>
					<?php if(!empty($this->sitereview->review_count) && ($this->listingType->reviews == 3 || $this->listingType->reviews == 2)): ?>
						<li>
							<span><?php echo $this->translate('Reviews :'); ?></span>
							<span><?php echo $this->translate( $this->sitereview->review_count) ?></span>
						</li>
					<?php endif; ?>
					
					<?php if (!empty($this->sitereview->rating_avg)): ?>
						<li>
							<span><?php echo $this->translate('Average User Rating :'); ?></span>
							<span><?php echo $this->showRatingStar($this->sitereview->rating_avg, 'user', 'small-star', $this->sitereview->listingtype_id); ?></span>
						</li>
					<?php endif; ?>
					
					<?php if (!empty($this->sitereview->rating_editor)): ?>
						<li>
							<span><?php echo $this->translate('Editor Rating :'); ?></span>
							<span><?php echo $this->showRatingStar($this->sitereview->rating_editor, 'editor', 'small-star', $this->sitereview->listingtype_id); ?></span>
						</li>
					<?php endif; ?>

					<?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.recommend', 1) && !empty($this->recommend_percentage)):?>
						<li>
							<span><?php echo $this->translate('Recommendations :'); ?></span>
							<span><?php echo $this->translate("Recommended by %s users", '<b>' . $this->recommend_percentage . '%</b>'); ?></span>
						</li>
					<?php endif;?>
					
					<?php if ($this->category_name): ?>
						<li>
							<span><?php echo $this->translate('Category :'); ?></span> 
							<span>
                <?php echo $this->translate($this->category_name) ?>
								<?php if ($this->subcategory_name): ?> &raquo;
									<?php echo $this->translate($this->subcategory_name) ?>
                
                  <?php if ($this->subsubcategory_name): ?> &raquo;
                    <?php echo $this->translate($this->subsubcategory_name) ?>
                  <?php endif; ?>
                
								<?php endif; ?>
							</span>
						</li>
					<?php endif; ?>
					<?php if ($this->sitereviewTags): $tagCount=0;?>
					 <li>
						<span><?php echo $this->translate(strtoupper($this->listingType->title_singular). '_TAG :'); ?></span>
							<span>
								<?php foreach ($this->sitereviewTags as $tag): ?>
									<?php if (!empty($tag->getTag()->text)):?>
										<?php if(empty($tagCount)):?>
											<?php echo "#". $tag->getTag()->text?>
											<?php "#".$tagCount++; ?>
										<?php else: ?>
											<?php echo $tag->getTag()->text?>
										<?php endif; ?>
									<?php endif; ?>
								<?php endforeach; ?>
							</span>
						</li>
					<?php endif; ?>
					<li>
						<span><?php echo $this->translate('Description :'); ?></span>
						<span><?php echo $this->translate(''); ?> <?php echo $this->sitereview->body ?></span>
					</li>
					
          <?php if($this->sitereview->location && $this->listingType->location):?>
						<li>
							<span><?php echo $this->translate('Location :'); ?></span>
							<span><?php echo $this->sitereview->location ?></span>
						</li>
          <?php endif; ?>
          <?php if($this->otherInfo->phone && (in_array("phone", $this->listingType->contact_detail))):?>
						<li>
							<span><?php echo $this->translate('Phone :'); ?></span>
							<span><?php echo $this->otherInfo->phone ?></span>
						</li>
          <?php endif; ?>
          <?php if($this->otherInfo->email && (in_array("email", $this->listingType->contact_detail))):?>
						<li>
							<span><?php echo $this->translate('Email :'); ?></span>
							<span><?php echo $this->otherInfo->email ?></span>
						</li>
          <?php endif; ?>
          <?php if($this->otherInfo->website && (in_array("website", $this->listingType->contact_detail))):?>
						<li>
							<span><?php echo $this->translate('Website :'); ?></span>
							<span><?php echo $this->otherInfo->website ?></span>
						</li>
          <?php endif; ?>
				</ul>
        <?php if($this->sitereview->profile_type): ?>
          <?php $str = $this->fieldValueLoop($this->sitereview, $this->fieldStructure); ?>
          <?php if(!empty($str) ): ?>
            <h4>
              <?php echo $this->translate('Profile Information') ?>
            </h4>
            <?php echo Engine_Api::_()->sitereview()->removeMapLink($this->fieldValueLoop($this->sitereview, $this->fieldStructure)) ?>					
          <?php endif; ?>
        <?php endif; ?>
        <br />
        <?php //FOR OVERVIEW PRINT ?>
				<?php if(!empty($this->otherInfo->overview) && $this->listingType->overview): ?>
					<h4><?php echo$this->translate('Overview') ?></h4>
					<ul><li><?php echo $this->otherInfo->overview;?></li></ul>
				<?php endif; ?>
			</div>	
		<?php endif; ?>
	</div>
</div>

<script type="text/javascript">
 function printData() {
		document.getElementById('printdiv').style.display = "none";
		window.print();
		setTimeout(function() {
					document.getElementById('printdiv').style.display = "block";
		}, 500);
	}
</script>