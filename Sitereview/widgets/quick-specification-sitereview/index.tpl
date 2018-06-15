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

<div class="sr_quick_specs sr_side_widget">
	
	<?php echo $this->show_fields ?>

 	<?php if(empty($this->review) && $this->contentDetails->content_id && $this->show_specificationlink): ?>
		<div class="sr_more_link">
	  	<a href="javascript:void(0);" onclick='showInfoTab();return false;'>   <?php echo $this->translate($this->show_specificationtext) . ' &raquo;'; ?></a>
	  </div>
	<?php elseif(!empty($this->review)): ?>
		<div class="sr_more_link">
	  	<a href="<?php echo $this->sitereview->getHref(array('profile_link' => 0)). '/tab/' . $this->tab_id?>"><?php echo $this->translate($this->show_specificationtext) . ' &raquo;'; ?></a>
	  </div>
  <?php endif;?>
</div>

<?php if(empty($this->review) && $this->contentDetails->content_id && $this->show_specificationlink): ?>
	<script type="text/javascript">
		function showInfoTab(){ 
			
			if($('main_tabs')) {
				tabContainerSwitch($('main_tabs').getElement('.tab_' + '<?php echo $this->contentDetails->content_id ?>'));
			}
			
			var params = {
				requestParams :<?php echo json_encode($this->contentDetails->params) ?>,
				responseContainer :$$('.layout_sitereview_specification_sitereview')
			}
		
			params.requestParams.content_id = '<?php echo $this->contentDetails->content_id ?>';
			en4.sitereview.ajaxTab.sendReq(params);
			
			if($('main_tabs')) {
				location.hash = 'main_tabs';
			}
		}
	</script>
<?php endif;?>
