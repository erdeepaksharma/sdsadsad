<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: delete.php 6590 2016-07-07 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<form method="post" class="global_form_popup">
  <div>
    <h3><?php echo $this->translate('Delete Advertise?') ?></h3>
    <p>
      <?php echo $this->translate('Are you sure that you want to delete this advertisement? It will not be recoverable after being deleted.') ?>
    </p>
    <br />
    <p>
      <input type="hidden" name="confirm" value="<?php echo $this->sell_id ?>"/>
      <button type='submit'><?php echo $this->translate("Delete") ?></button>
      <?php echo $this->translate(" or ") ?>
      <a href='javascript:void(0);' onclick='javascript:parent.Smoothbox.close()'>
        <?php echo $this->translate("cancel") ?>
      </a>
    </p>
  </div>
</form>

<?php if (@$this->closeSmoothbox): ?>
  <script type="text/javascript">
    parent.Smoothbox.close();
  </script>
<?php endif; ?>
