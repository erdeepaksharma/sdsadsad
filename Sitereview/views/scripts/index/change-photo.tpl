<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: change-photo.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/_DashboardNavigation.tpl'; ?>

<div class="layout_middle">
  <div class="global_form_popup">
    <?php echo $this->form->render($this); ?>
  </div>
</div>
</div>
<script type="text/javascript">
	function removePhotoListing(url) {
		window.location.href=url;
	}
</script>