<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: buysell.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php if( $this->viewer() && $this->viewer()->getIdentity() ): ?>
  <div class="comment_form_user_photo">
    <?php echo $this->itemPhoto($this->viewer(), 'thumb.icon') ?>
  </div>
<?php endif; ?>