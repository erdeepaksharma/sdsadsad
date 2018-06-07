<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: third-party-settings.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<h2>
<?php echo $this->translate("ADVANCED_ACTIVITY_PLUGIN_NAME") . " " . $this->translate("Plugin") ?>
</h2>

    <?php if (count($this->navigation)): ?>
    <div class='seaocore_admin_tabs'>
        <?php
        // Render the menu
        //->setUlClass()
        echo $this->navigation()->menu()->setContainer($this->navigation)->render()
        ?>
    </div>
<?php endif; ?>
 <?php 
 $this->navigationAAF = $navigationAAF = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main_settings', array(), 'advancedactivity_admin_main_settings_third_party');
   
 ?> 

<?php if (count($this->navigationAAF)): ?>
  <div class='seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigationAAF)->render() ?>
  </div>
<?php endif; ?>
<div class="seaocore_settings_form">
    <div class='settings'>
<?php echo $this->form->render($this); ?>
    </div>
</div>	
 