<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: assign-badge.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php if(Count($this->badgeData)): ?>
	<div class="settings sr_assign_badge_list_popup">
		<form method="post" class="global_form">
			<div>
				<?php if($this->previous_badge_id): ?>
					<h3><?php echo $this->translate('Assign / Remove a Badge');?></h3>
					<p><?php echo $this->translate("Assign a badge to this Editor by selecting its radio button. Below, you can also remove the badge which is already assigned to this Editor by selecting the radio button corresponding to the last item titled as 'Remove Badge' and then click on 'Assign / Remove Badge' button to save it.");?></p>
				<?php else:?>
					<h3><?php echo $this->translate('Assign a Badge');?></h3>
					<p><?php echo $this->translate('Assign a badge to the Editor by selecting its radio button.');?></p>
				<?php endif; ?>

				<ul class="sr_assign_badge_list">
					<?php foreach ($this->badgeData as $item):?>
						<?php if($this->previous_badge_id == $item->badge_id): ?>
							<li class="selected">
						<?php else: ?>
							<li>	
						<?php endif; ?>

							<?php
								if(!empty($item->badge_main_id)) {
									$thumb_path = Engine_Api::_()->storage()->get($item->badge_main_id, '')->getPhotoUrl();
									if(!empty($thumb_path)) {
										echo '<center><img src="'. $thumb_path .'" />';?></center> <?php ;
									}
								}
							?>

							<?php if($this->previous_badge_id == $item->badge_id): ?>
								<input type="radio" name="badge_id" value="<?php echo $item->badge_id?>" checked="checked"/>
							<?php else: ?>
								<input type="radio" name="badge_id" value="<?php echo $item->badge_id?>"/>
							<?php endif; ?>

						</li>
					<?php endforeach; ?>

					<?php if($this->previous_badge_id): ?>
						<li>
							<center class="sr_assign_badge_list_tip_wrapper">
								<img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/remove_badge_icon.png" />
								<div class="sr_assign_badge_list_tip">
									<img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/tip-arrow-top.png" alt="" />
									<div>
										<?php echo $this->translate('Please select this radio button to remove badge from this Editor.');?>
									</div>
								</div>
							</center> 
									
							<div class="badge_name sr_assign_badge_list_tip_wrapper">
								<?php echo $this->translate("Remove Badge");?>
								<div class="sr_assign_badge_list_tip">
									<img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/tip-arrow-top.png" alt="" />
									<div>
										<?php echo $this->translate('Please select this radion button to remove badge from this Page.');?>
									</div>
								</div>
							</div>
								
							<input type="radio" name="badge_id" value="0"/>
						</li>
					<?php endif; ?>

				</ul>
				<div>
					<p>
						<input type="hidden" name="page_id" value="<?php echo $this->page_id?>"/>
						
						<?php if($this->previous_badge_id): ?>
							<button type='submit'><?php echo $this->translate("Assign/Remove Badge") ?></button>
						<?php else:?>
							<button type='submit'><?php echo $this->translate("Assign Badge") ?></button>
						<?php endif; ?>
						<?php echo $this->translate(" or ") ?> 
						<a href='javascript:void(0);' onclick='javascript:parent.Smoothbox.close()'>
						<?php echo $this->translate("cancel") ?></a>
					</p>
				</div>
			</div>
		</form>
	</div>
<?php else:?>
	<div class="settings sr_assign_badge_list_popup">
		<div>
			<div class="tip">
				<span>
					<?php echo $this->translate('You have not created any badges yet. Get started by ').$this->htmlLink(array(
									'route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'badge', 'action' => 'manage'
								), $this->translate('creating'), array('target' => '_blank')). $this->translate(" one."); ?>
				</span>
		  </div>
			<button onclick='javascript:parent.Smoothbox.close()' class="clear"><?php echo $this->translate("Close") ?></button>
		</div>
	</div>		
<?php endif;?>

<?php if( @$this->closeSmoothbox ): ?>
  <script type="text/javascript">
    TB_close();
  </script>
<?php endif; ?>