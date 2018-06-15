<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: post.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');?>

<?php 
  include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/Adintegration.tpl';
?>

<div class="sr_view_top">
	<?php echo $this->htmlLink($this->sitereview->getHref(array('profile_link' => 1)), $this->itemPhoto($this->sitereview, 'thumb.icon', '', array('align' => 'left'))) ?>
	<h2>	
		<?php echo $this->sitereview->__toString() ?>	
		<?php echo $this->translate('&raquo; '); ?>
		<?php echo $this->htmlLink($this->sitereview->getHref(array('tab'=> $this->tab_selected_id)), $this->translate('Discussions')) ?>
    <?php echo $this->translate('&raquo; '); ?>
    <?php echo $this->topic->__toString() ?>
	</h2>
</div>

<?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('communityad') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.communityads', 1) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.advideodelete', 3) && $review_communityad_integration): ?>
	<div class="layout_right" id="communityad_videodelete">
		<?php echo $this->content()->renderWidget("communityad.ads", array( "itemCount"=>Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.advideodelete', 3),"loaded_by_ajax"=>1,'widgetId'=>'review_advideodelete'))?>
	</div>
<?php endif; ?>

<div class="layout_middle">
	<?php if($this->message) echo $this->message ?>
	<?php if($this->form) echo $this->form->render($this) ?>
</div>