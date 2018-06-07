<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: navigation.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>
<h2>
  <?php echo $this->translate("ADVANCED_ACTIVITY_PLUGIN_NAME")." ".$this->translate("Plugin") ?>
</h2>

<?php if( count($this->navigation) ): ?>
  <div class='seaocore_admin_tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>
<h3>
  <?php echo $this->translate("Manage Lists") ?>
</h3>


  <?php echo $this->translate("Lists enable users to filter activity feeds. Users can filter based on already existing lists, or create custom lists by choosing friends and content. Below, you can highly configure the way lists work for users.") ?>



<?php if( count($this->subnavigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->subnavigation)->render()
    ?>
  </div>
<?php endif; ?>