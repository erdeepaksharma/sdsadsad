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

<form method="post" class="global_form_popup">
	<div>
		<h3><?php echo $this->translate("Delete Price Info") ?></h3>
		<p>
			<?php echo $this->translate("This is not recoverable after being deleted.") ?>
		</p>
		<br />
		<p>
			<input type="hidden" name="confirm" value="<?php echo $this->priceinfo_id?>"/>
			<button type='submit'><?php echo $this->translate("Delete") ?></button>
			<?php echo $this->translate(" or ") ?> 
			<a href='javascript:void(0);' onclick='javascript:parent.Smoothbox.close()'>
			<?php echo $this->translate("cancel") ?></a>
		</p>
	</div>
</form>

<?php if($this->responseHTML): ?>
  <script type="text/javascript">
   window.addEvent('domready', function() {
     parent.$('priceinfo_content').innerHTML = "<?php echo $this->string()->escapeJavascript($this->responseHTML , false) ?>";

     en4.core.runonce.trigger();
     parent.Smoothbox.bind(parent.$('priceinfo_content'));
     parent.Smoothbox.close();
     
   });
  </script>
<?php endif; ?>

<?php if( @$this->closeSmoothbox ): ?>
	<script type="text/javascript">
		TB_close();
	</script>
<?php endif; ?>
