<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: request-delete.tpl 6590 2014-05-19 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<form method="post" class="global_form_popup">
  <div>
    <h3><?php echo "Delete Listing Claim Request?"; ?></h3>
    <p>
      <?php echo "Are you sure that you want to delete this listing claim request? It will not be recoverable after being deleted."; ?>
    </p>
    <br />
    <p>
      <input type="hidden" name="confirm" value="<?php echo $this->claim_id ?>"/>
      <button type='submit'><?php echo "Delete"; ?></button>
      <?php echo " or "; ?> 
      <a href='javascript:void(0);' onclick='javascript:parent.Smoothbox.close()'>
        <?php echo "cancel"; ?>
      </a>
    </p>
  </div>
</form>

<?php if (@$this->closeSmoothbox): ?>
  <script type="text/javascript">
    TB_close();
  </script>
<?php endif; ?>