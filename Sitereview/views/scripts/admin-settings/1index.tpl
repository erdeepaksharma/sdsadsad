<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>
 <?php
//GET API KEY
$apiKey = Engine_Api::_()->seaocore()->getGoogleMapApiKey();
$this->headScript()->appendFile("https://maps.googleapis.com/maps/api/js?libraries=places&key=$apiKey");
?>
<?php
	if( !empty($this->isModsSupport) ):
		foreach( $this->isModsSupport as $modName ) {
			echo "<div class='tip'><span>" . $this->translate("Note: You do not have the latest version of the '%s'. Please upgrade it to the latest version to enable its integration with Reviews & Ratings plugin.", ucfirst($modName)) . "</span></div>";
		}
	endif;
?>
<?php $url = $this->url(array('module' => 'seaocore', 'controller' => 'settings', 'action' => 'upgrade'), 'admin_default', true); ?>
<?php 
  if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereview') && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) {
    if($this->moduleSitereview->version > $this->moduleSitereviewlistingtype->version) {
      echo "<div class='tip'><span>" . $this->translate('Note: You do not have the latest version of the "%s". Please upgrade it to the latest version. <br />The latest version of this plugin is available to you in your SocialEngineAddOns Client Area. Login into your SocialEngineAddOns Client Area here: <a href="http://www.socialengineaddons.com/user/login">http://www.socialengineaddons.com/user/login</a>.', $this->moduleSitereviewlistingtype->title) . "</span></div>"; 
    }
    elseif($this->moduleSitereview->version < $this->moduleSitereviewlistingtype->version) {
      echo "<div class='tip'><span>" . $this->translate('Note: You do not have the latest version of the "%1$s". Please upgrade it to the latest version from %2$s. <br />The latest version of this plugin is also available to you in your SocialEngineAddOns Client Area. Login into your SocialEngineAddOns Client Area here: <a href="http://www.socialengineaddons.com/user/login">http://www.socialengineaddons.com/user/login</a>.', $this->moduleSitereview->title, "<a href='$url'>here</a>") . "</span></div>";    
    }
  }
?>    

<h2>
  <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) { echo $this->translate('Reviews & Ratings - Multiple Listing Types Plugin'); } else { echo $this->translate('Reviews & Ratings Plugin'); }?>
</h2>
<?php if (count($this->navigation)): ?>
	<div class='seaocore_admin_tabs'>
		<?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
	</div>
<?php endif; ?>

<?php if(false): //if( !empty($this->getHostTypeArray) ): ?>
<div id="dismiss_modules">
  <div class="seaocore-notice">
		<div class="seaocore-notice-icon">
			<img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/notice.png" alt="Notice" />
		</div>
    <div style="float:right;">
      <button onclick="dismissNote();"><?php echo $this->translate('Dismiss'); ?></button>
    </div>
    <div class="seaocore-notice-text">
      <?php echo $this->translate("Note: It seems that this plugin has been used at multiple domains, because of which this plugin may not work properly on domain configures to use this plugin. Please find the list of other domains below :"); ?></br>
      <ul>
      <?php foreach( $this->getHostTypeArray as $getHostName ):
              if( $this->viewAttapt != $getHostName && !empty($getHostName)):
                echo '<li><b>' . $getHostName . '</b></li>';
              endif;      
             endforeach;
      ?>
      </ul>
      <?php echo $this->translate("1) If you do not want to use this plugin on Multiple Domains, then please click on 'Dismiss' button.<br/> 2) If above is not the case and you want to use this plugin on multiple domains, then please file a support ticket from your SocialEngineAddOns <a href='http://www.socialengineaddons.com/user/login' target='_blank'>client area</a>."); ?>
    </div>
  </div>
</div>
<?php
  endif;
?>

<?php include APPLICATION_PATH . '/application/modules/Seaocore/views/scripts/_upgrade_messages.tpl'; ?>
<div class='seaocore_settings_form'>
	<div class='settings'>
    <?php 
    if( !empty($this->supportingModules) ) :
      echo "<div class='seaocore_tip'><span>" . $this->translate("You do not have the latest version of the '%s'. Please upgrade it to the latest version to enable its integration with Reviews & Ratings plugin.", ucfirst($this->supportingModules[0])) . "</span></div>";
    else:
      echo $this->form->render($this); 
    endif;
    
    ?>
  </div>
</div>

<?php $settings = Engine_Api::_()->getApi('settings', 'core'); ?>

<script type="text/javascript">
  if(document.getElementById('sitereview_map_city')) {
    window.addEvent('domready', function() {
      new google.maps.places.Autocomplete(document.getElementById('sitereview_map_city'));
    });
  }
  
  function dismissNote() {
    $('is_remove_note').value = 1;
    $('review_global').submit();
  }

	window.addEvent('domready', function() {
		showDefaultNetwork('<?php echo $settings->getSetting('sitereview.network', 0) ?>');
	});

	function showDefaultNetwork(option) {
		if($('sitereview_default_show-wrapper')) {
			if(option == 0) {
				$('sitereview_default_show-wrapper').style.display='block';
         showDefaultNetworkType($('sitereview_default_show-1').checked);
			}
			else{
         showDefaultNetworkType(1);
				$('sitereview_default_show-wrapper').style.display='none';
			}
		}
	}
  function showDefaultNetworkType(option) {
    if($('sitereview_networks_type-wrapper')) {
      if(option == 1) {
        $('sitereview_networks_type-wrapper').style.display='block';
      }else{
        $('sitereview_networks_type-wrapper').style.display='none';
      }
    }
  }
  
</script>
<style type="text/css">
  .seaocore-notice-text ul {
 list-style: disc outside none;
    margin: 3px 0 0 18px;
}
.seaocore-notice-text ul li{
	margin: 2px 0 2px 0px;
}
</style>