<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: upload.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php 
  include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/Adintegration.tpl';
?>

<?php	$this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');?>

<?php if ($this->can_edit): ?>
  <?php include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/_DashboardNavigation.tpl'; ?>
<?php else:?>
	<div class="sr_view_top">
		<?php echo $this->htmlLink($this->sitereview->getHref(), $this->itemPhoto($this->sitereview, 'thumb.icon', '', array('align' => 'left'))) ?>
		<h2>	
			<?php echo $this->sitereview->__toString() ?>	
			<?php echo $this->translate('&raquo; '); ?>
			<?php echo $this->htmlLink($this->sitereview->getHref(array('tab'=> $this->tab_id)), $this->translate('Photos')) ?>
		</h2>
	</div>
	<?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('communityad') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.communityads', 1) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.adalbumcreate', 3) && $review_communityad_integration ): ?>
		<div class="layout_right" id="communityad_albumcreate">
			<?php echo $this->content()->renderWidget("communityad.ads", array( "itemCount"=>Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.adalbumcreate', 3),"loaded_by_ajax"=>1,'widgetId'=>'review_adalbumcreate'))?>
		</div>
   <div class="layout_middle">
   <?php endif; ?>
<?php endif; ?>

<script type="text/javascript">
  var listingtype_id = '<?php echo $this->sitereview->listingtype_id; ?>';
</script>

<div class="sr_dashboard_content">
  <?php if ($this->can_edit): ?>
    <?php echo $this->partial('application/modules/Sitereview/views/scripts/dashboard/header.tpl', array('sitereview' => $this->sitereview)); ?>
  <?php endif; ?>
  <?php echo $this->form->render($this) ?>
  <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('communityad') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.communityads', 1) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.adalbumcreate', 3)  && $review_communityad_integration): ?>
    </div>
  <?php endif; ?>
</div>
<?php if ($this->can_edit): ?>
  </div>
<?php endif; ?>