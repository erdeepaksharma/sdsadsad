<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2016-2017 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 2017-03-08 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<script type="text/javascript">
  var fetchLevelSettings =function(level_id){
    window.location.href= en4.core.baseUrl+'admin/advancedactivity/member-level/index/id/'+level_id;
    //alert(level_id);
  }
</script>
<h2 >
  <?php echo $this->translate("ADVANCED_ACTIVITY_PLUGIN_NAME") . " " . $this->translate("Plugin") ?>
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
<div class='seaocore_admin_form'>
<div class='settings'>
    <?php echo $this->form->render($this); ?>
</div> 
</div>