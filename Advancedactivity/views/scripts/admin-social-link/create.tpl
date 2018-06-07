<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: create.php 6590 2016-07-07 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>

<!--ADD NAVIGATION-->
<?php if( count($this->navigation) ): ?>
  <div class='seaocore_admin_tabs'>
    <?php
     
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>
<div class="settings">
  <?php echo $this->form->render($this) ?>
</div>
