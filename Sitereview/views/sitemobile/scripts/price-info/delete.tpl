<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: delete.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>
<?php $defaultRoute = 'sitereview_priceinfo_listtype_'. $this->listingtype_id;?>
<form method="post" class="global_form_popup" action='<?php echo $this->url(array("action" => 'delete', "id" => $this->priceinfo_id, "listingtype_id" => $this->listingtype_id), $defaultRoute, true);?>'>
	<div>
		<h3><?php echo $this->translate("Delete Price Info") ?></h3>
		<p>
			<?php echo $this->translate("This is not recoverable after being deleted.") ?>
		</p>
		<br />
		<p>
			<input type="hidden" name="confirm" value="<?php echo $this->priceinfo_id?>"/>
			<button type='submit' data-theme="b"><?php echo $this->translate("Delete") ?></button>
			<div style="text-align: center"><?php echo $this->translate('or'); ?> </div>
			<a href="#" data-rel="back" data-role="button">
            <?php echo $this->translate('Cancel') ?>
        </a>
		</p>
	</div>
</form>