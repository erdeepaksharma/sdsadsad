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

<?php $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css'); ?>

<?php if(in_array('photo', $this->showContent)):?>
	<div class="sm_profile_item_photo">
	<?php echo $this->htmlLink($this->user->getHref(), $this->itemPhoto($this->user, 'thumb.profile', '', array('align' => 'center'))) ?>
	</div>
<?php endif;?>

<?php if(in_array('title', $this->showContent) || in_array('designation', $this->showContent) || in_array('forEditor', $this->showContent)):?>
<div class="sm_profile_item_info">
  <?php if(in_array('title', $this->showContent)) :?>
		<div class="sm_profile_item_title">
			<?php echo $this->htmlLink($this->user->getHref(), $this->user->getTitle()) ?>
		</div>
  <?php endif;?>
  <?php if(in_array('designation', $this->showContent) && $this->editor->designation) :?>
		<div class='sm_profile_item_designation'>
			<?php echo "<b>(".$this->editor->designation.")</b>" ?>
		</div>
	<?php endif;?>
   
	<?php if(in_array('forEditor', $this->showContent) && $this->countListingtypes > 1): ?>  
		<?php if(($getCount = Count($this->getDetails)) > 0):  ?>
			<div class="sr_editor_profile_stats o_hidden">
				<?php $count = 0; ?>
				<?php echo $this->translate("Editor For:"); ?>
				<?php foreach($this->getDetails as $getDetail): ?>
					<?php $count++; ?>
					<?php echo $this->htmlLink(array('route' => 'sitereview_general_listtype_'.$getDetail->listingtype_id), $getDetail->title_plural); ?><?php if($count < $getCount): ?>,<?php endif; ?>
				<?php endforeach;?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
<?php endif;?>

<?php if(in_array('details', $this->showContent) && !empty($this->editor->details)):  ?>
	<br /><br /><?php echo $this->editor->details; ?>
<?php endif; ?>  

<?php if(in_array('about', $this->showContent) && !empty($this->editor->about)):  ?>
	<br /><br /><?php echo htmlspecialchars_decode(nl2br($this->editor->about),  ENT_QUOTES) ;?>
<?php endif; ?> 


<?php if (in_array('emailMe', $this->showContent) && !$this->user->isSelf($this->viewer()) && $this->user->email): ?>
	<div class="seaocore_profile_cover_buttons">
		<table cellpadding="2" cellspacing="0" style="width:100%">
			<tr>
				<td>
					<a href ="<?php echo $this->url(array('action' => 'editor-mail', 'user_id' => $this->user->user_id), 'sitereview_review_editor', true);?>"  data-role='button' data-inset='false' data-mini='true' data-corners='false' data-shadow='true'>
						<span><?php echo $this->translate('Email %s', $this->user->getTitle()) ?></span>
					</a>
				</td>
			</tr>
		</table>
	</div>
<?php endif; ?>


<style type="text/css">
.sm_profile_item_designation{
	font-size: 14px;
	margin: 2px 0 0 8px;
	overflow: hidden;
}
.sr_editor_profile_stats{
	margin: 2px 0 0 8px;
	overflow: hidden;
}
</style>