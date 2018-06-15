<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: delete-selected.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<div class="settings" >
  <div class='global_form'>
    <?php if ($this->ids): ?>
      <form method="post" class="global_form_popup">
        <div>
          <h3><?php echo $this->translate("Delete the selected listing videos?") ?></h3>
          <p>
            <?php echo $this->translate("Are you sure that you want to delete the %d listing videos? These will not be recoverable after being deleted.", $this->count) ?>
          </p>
          <br />
          <p>
            <input type="hidden" name="confirm" value='true'/>
            <input type="hidden" name="ids" value="<?php echo $this->ids ?>"/>

            <button type='submit'><?php echo $this->translate("Delete") ?></button>
            <?php echo Zend_Registry::get('Zend_Translate')->_(' or ') ?>
            <a href='<?php echo $this->url(array('action' => 'index', 'id' => null)) ?>'>
              <?php echo $this->translate("cancel") ?></a>
          </p>
        </div>
      </form>
    <?php else: ?>
      <?php echo $this->translate("Please select a listing video to delete.") ?> <br/><br/>
      <a href="<?php echo $this->url(array('action' => 'index')) ?>" class="buttonlink icon_back">
        <?php echo $this->translate("Go Back") ?>
      </a>
    <?php endif; ?>
  </div>
</div>

<?php if (@$this->closeSmoothbox): ?>
  <script type="text/javascript">
    TB_close();
  </script>
<?php endif; ?>