<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: install.php 6590 2016-07-07 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<h2>
<?php echo $this->translate("ADVANCED_ACTIVITY_PLUGIN_NAME") . " " . $this->translate("Plugin") ?>
</h2>
<!--ADD NAVIGATION-->
<?php if( count($this->navigation) ): ?>
  <div class='seaocore_admin_tabs'>
    <?php
     
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>

<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'advancedactivity', 'controller' => 'feelingtype','action' => 'manage', 'feelingtype_id' => $this->feelingtype->getIdentity()), $this->translate("Back to Manage Feelings"), array('class' => 'seaocore_icon_back buttonlink')) ?>
<br style="clear:both;" /><br />
<div class="settings">
  <?php echo $this->form->render($this) ?>
</div>
<style type="text/css">
  .settings #file-label{
    width: 0;
  }
</style>
