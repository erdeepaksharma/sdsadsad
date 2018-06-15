<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: tagscloud.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<script type="text/javascript">  
	var tagAllAction = function(tag_id, tag){
		$('tag').value = tag;
		$('tag_id').value = tag_id;
		$('filter_form_tagscloud').submit();
	}
</script>

<?php include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/navigation_views.tpl'; ?>
<?php 
  include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/Adintegration.tpl';
?>
<?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('communityad') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.communityads', 1) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.adtagview', 3)  && $review_communityad_integration): ?>
  <div class="layout_right" id="communityad_tagcloud">
		<?php echo $this->content()->renderWidget("communityad.ads", array( "itemCount"=>Engine_Api::_()->getApi('settings', 'core')->getSetting('siteevent.adtagview', 3),"loaded_by_ajax"=>1,'widgetId'=>'review_tagcloud'))?>
  </div>
<?php endif; ?>

<div class="layout_middle">

	<h3><?php echo $this->translate("Popular $this->title Tags"); ?></h3>
	<p class="mtop5"><?php echo $this->translate("Browse the tags created for $this->listing_plural_lc by the various members."); ?></p>
	<?php if(!empty($this->tag_array)):?>
		<div class="mtop10">
			<?php foreach($this->tag_array as $key => $frequency):?>
				<?php $step = $this->tag_data['min_font_size'] + ($frequency - $this->tag_data['min_frequency'])*$this->tag_data['step'] ?>
				<a href='<?php echo $this->url(array('action' => 'index'), "sitereview_general_listtype_" . $this->listingtype_id); ?>?tag=<?php echo urlencode($key) ?>&tag_id=<?php echo $this->tag_id_array[$key] ?>' style="font-size:<?php echo $step ?>px;" title=''><?php echo $key ?><sup><?php echo $frequency ?></sup></a>&nbsp; 
			<?php endforeach;?>
		</div>
	<?php endif; ?>

</div>