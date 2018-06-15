<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: show-video.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<h2>
  <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) { echo $this->translate('Reviews & Ratings - Multiple Listing Types Plugin'); } else { echo $this->translate('Reviews & Ratings Plugin'); }?>
</h2>

<?php if (count($this->navigation)): ?>
	<div class='seaocore_admin_tabs'>
		<?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
	</div>
<?php endif; ?>

<h3>
  <?php echo $this->translate("") ?>
</h3>
<?php if( count($this->subNavigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->subNavigation)->render()
    ?>
  </div>
<?php endif; ?>

<div class='seaocore_settings_form'>
	<div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>

<?php $settings = Engine_Api::_()->getApi('settings', 'core'); ?>

<script type="text/javascript">
	window.addEvent('domready', function() {
		showDefaultVideo('<?php echo $settings->getSetting('sitereview.show.video', 1) ?>');
	});
  var videoModuleEnabled = '<?php echo Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('video');?>';
  function showDefaultVideo(option) {
		if(option == 0 || (videoModuleEnabled == 0)) {
			$('sitereview_video_ffmpeg_path-wrapper').style.display='block';
			$('sitereview_video_jobs-wrapper').style.display='block';
			$('sitereview_video_embeds-wrapper').style.display='block';
		}
		else  {
			$('sitereview_video_ffmpeg_path-wrapper').style.display='none';
			$('sitereview_video_jobs-wrapper').style.display='none';
			$('sitereview_video_embeds-wrapper').style.display='none';
		}
  }
</script>