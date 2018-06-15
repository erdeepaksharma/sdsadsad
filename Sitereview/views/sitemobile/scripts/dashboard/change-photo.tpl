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
<?php echo $this->partial('application/modules/Sitereview/views/sitemobile/scripts/dashboard/header.tpl', array('sitereview' => $this->sitereview)); ?>
<div class="dashboard-content">
  <?php echo $this->form->render($this); ?>
</div>
<script type="text/javascript">
  function removePhotoListing(url) {
    window.location.href=url;
  }
</script>