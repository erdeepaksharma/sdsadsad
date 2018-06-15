<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: readme.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<h2>
  <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) { echo $this->translate('Reviews & Ratings - Multiple Listing Types Plugin'); } else { echo $this->translate('Reviews & Ratings Plugin'); }?>
</h2>

<div class="tabs">
	<ul class="navigation">
		<li class="active">
			<a href="<?php echo $this->baseUrl() .'/admin/sitereview/settings/readme'?>" ><?php echo $this->translate('Please go through these important points and proceed by clicking the button at the bottom of this page.') ?></a>
    </li>
	</ul>
</div>		

<?php include_once APPLICATION_PATH .
'/application/modules/Sitereview/views/scripts/admin-settings/faq_help.tpl'; ?>
<br />
<button onclick="form_submit();"><?php echo $this->translate('Proceed to enter License Key') ?> </button>
	
<script type="text/javascript" >
	function form_submit() {
		
		var url='<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'settings'), 'admin_default', true) ?>';
		window.location.href=url;
	}
</script>