<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: multi-delete-request.tpl 6590 2014-05-19 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>

<div class="settings" >
  <div class='global_form'>
    <?php if ($this->ids): ?>
      <form method="post" class="global_form_popup">
        <div>
          <h3><?php echo "Delete the selected listing claims?"; ?></h3>
          <p>
            <?php echo $this->translate("Are you sure that you want to delete the %d listing claims? These will not be recoverable after being deleted.", $this->count) ?>
          </p>
          <br />
          <p>
            <input type="hidden" name="confirm" value='true'/>
            <input type="hidden" name="ids" value="<?php echo $this->ids ?>"/>		
            <button type='submit'><?php echo "Delete"; ?></button>
            <?php echo ' or '; ?>
            <a href='<?php echo $this->url(array('action' => 'index', 'id' => null)) ?>'>
              <?php echo "cancel"; ?></a>
          </p>
        </div>
      </form>
    <?php else: ?>
      <?php echo "Please select a listing claim to be deleted."; ?> <br/><br/>
      <a href="<?php echo $this->url(array('action' => 'index')) ?>" class="buttonlink icon_back">
        <?php echo "Go Back"; ?>
      </a>
    <?php endif; ?>
  </div>
</div>

<?php if (@$this->closeSmoothbox): ?>
  <script type="text/javascript">
    TB_close();
  </script>
<?php endif; ?>