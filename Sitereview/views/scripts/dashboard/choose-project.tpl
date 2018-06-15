<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2016-2017 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: choose-project.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<script type="text/javascript" >
  var submitformajax = 1;
</script>

<?php include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/_DashboardNavigation.tpl'; ?>

<div class="sr_dashboard_content">
	<?php echo $this->partial('application/modules/Sitereview/views/scripts/dashboard/header.tpl', array('sitereview' => $this->sitereview)); ?>
	<?php echo $this->form->render($this); ?>
	<?php echo $this->partial('application/modules/Sitecrowdfunding/views/scripts/_chooseProject.tpl', array('parent_id' => $this->parent_id, 'parent_type' => $this->parent_type)); ?>
  <div id="show_tab_content_child"></div>
</div>
</div>