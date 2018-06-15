<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: remove.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>
<?php
$favouriteSetting = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite', 0);
?>
<form method="post" class="global_form_popup">
  <div>
    <?php if($favouriteSetting): ?>  
        <h3><?php echo $this->translate('Remove from Favourites?'); ?></h3>
        <p>
          <?php echo $this->translate('Are you sure that you want to remove this entry from your favourites? It will not be recoverable after being deleted.'); ?>
        </p>        
    <?php else: ?>  
        <h3><?php echo $this->translate('Remove this entry from this Wishlist?'); ?></h3>
        <p>
          <?php echo $this->translate('Are you sure that you want to remove this entry from this Wishlist? It will not be recoverable after being deleted.'); ?>
        </p>        
    <?php endif; ?>

    <br />
    <p>
      <input type="hidden" name="confirm" value="<?php echo $this->wishlist_id?>"/>
      <button type='submit'><?php echo $this->translate('Remove'); ?></button>
      <?php echo $this->translate('or'); ?>
      <a href="#" data-rel="back" data-role="button">
        <?php echo $this->translate('Cancel') ?>
      </a>
    </p>
  </div>
</form>