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

<div class="tip">
	<span> 
		<?php echo $this->translate('No '.strtolower($this->listingtypeArray->title_plural).' have been posted yet.'); ?>
		<?php if ($this->can_create):?>
   <?php if (Engine_Api::_()->sitereview()->hasPackageEnable()):?>
    <?php echo $this->translate('BE_THE_FIRST_'.strtoupper($this->listingtypeArray->title_singular).'_TO %1$spost%2$s one!', '<a href="' . $this->url(array('action' => 'index'), "sitereview_package_listtype_$this->listingtype_id") . '">', '</a>'); ?>
   <?php else:?>
    <?php echo $this->translate('BE_THE_FIRST_'.strtoupper($this->listingtypeArray->title_singular).'_TO %1$spost%2$s one!', '<a href="' . $this->url(array('action' => 'create'), "sitereview_general_listtype_$this->listingtype_id") . '">', '</a>'); ?>
   <?php endif;?>
		<?php endif; ?>
  </span>
</div>