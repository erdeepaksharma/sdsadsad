<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: delete.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>
<div class="settings">
  <div class='global_form'>
    <form method="post">
      <div>
        <h3><?php echo $this->translate("Delete Report?") ?></h3>
        <p><?php echo $this->translate("Are you sure that you want to delete this report? It will not be recoverable after being deleted.") ?></p>
        <br />
        <p>
          <input type="hidden" name="confirm" value="<?php echo $id?>"/>
          <button type='submit'><?php echo $this->translate('Delete'); ?></button>
          <?php echo $this->translate("or") ?>
          <a href='javascript:void(0);' onclick='javascript:parent.Smoothbox.close()'>
          <?php echo $this->translate("cancel") ?>
        </p>
      </div>
    </form>
  </div>
</div>

<?php if( @$this->closeSmoothbox ): ?>
<script type="text/javascript">
  TB_close();
</script>
<?php endif; ?>
