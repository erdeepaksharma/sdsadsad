<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: overview.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/_DashboardNavigation.tpl'; ?>
<div class="sr_dashboard_content">
  <?php echo $this->partial('application/modules/Sitereview/views/scripts/dashboard/header.tpl', array('sitereview'=>$this->sitereview));?>
	<?php if(!empty($this->success)): ?>
		<ul class="form-notices" >
			<li>
				<?php echo $this->translate($this->success); ?>
			</li>
		</ul>
  <?php endif; ?>
	<?php echo $this->form->render($this); ?>

	<script type="text/javascript">
		var catdiv1 = $('overview-label');
		var catdiv2 = $('save-label');  
		var catarea1 = catdiv1.parentNode;
		catarea1.removeChild(catdiv1);
		var catarea2 = catdiv2.parentNode;
		catarea2.removeChild(catdiv2);
	</script>
</div>
</div>