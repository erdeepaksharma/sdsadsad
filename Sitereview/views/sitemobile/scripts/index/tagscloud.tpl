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

 <?php echo $this->content()->renderWidget('sitereview.navigation-sitereview') ?>
<div>
	<h3><?php echo $this->translate("Popular $this->title Tags"); ?></h3>
	<p><?php echo $this->translate("Browse the tags created for $this->listing_plural_lc by the various members."); ?></p>
	<?php if(!empty($this->tag_array)):?>
		<div>
			<?php foreach($this->tag_array as $key => $frequency):?>
				<?php $step = $this->tag_data['min_font_size'] + ($frequency - $this->tag_data['min_frequency'])*$this->tag_data['step'] ?>
				<a href='<?php echo $this->url(array('action' => 'index'), "sitereview_general_listtype_" . $this->listingtype_id); ?>?tag=<?php echo urlencode($key) ?>&tag_id=<?php echo $this->tag_id_array[$key] ?>' style="font-size:<?php echo $step ?>px;" title=''><?php echo $key ?><sup><?php echo $frequency ?></sup></a>&nbsp; 
			<?php endforeach;?>
		</div>
	<?php endif; ?>
</div>