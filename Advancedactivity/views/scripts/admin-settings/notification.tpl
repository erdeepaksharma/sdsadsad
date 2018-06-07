<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: notification.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
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
<div class="seaocore_settings_form">
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>

<script type="text/javascript">
  window.addEvent('domready', function() {
    toggleNofiticationUpdate(<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('aaf.isenable.notification', 1) ?>);
  });
  function toggleNofiticationUpdate(value){   
    if($('aaf_notifications_inupdate-wrapper')){     
      if(value==0){
        $('aaf_notifications_inupdate-wrapper').style.display='none';       
      }else{
        $('aaf_notifications_inupdate-wrapper').style.display='block';        
      }
    }
  }
</script>