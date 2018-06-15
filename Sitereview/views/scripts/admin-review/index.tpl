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

<h2>
  <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) { echo $this->translate('Reviews & Ratings - Multiple Listing Types Plugin'); } else { echo $this->translate('Reviews & Ratings Plugin'); }?>
</h2>

<?php if (count($this->navigation)): ?>
  <div class='seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
  </div>
<?php endif; ?>

<?php if( count($this->subNavigation) ): ?>
  <div class='tabs'>
    <?php
      echo $this->navigation()->menu()->setContainer($this->subNavigation)->render()
    ?>
  </div>
<?php endif; ?>

<div class='seaocore_settings_form'>
	<div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>

<script type="text/javascript">

	window.addEvent('domready', function(){
		prosconsInReviews('<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.proscons', 1); ?>');
	});

	function prosconsInReviews(option) {

		if($('sitereview_proncons-wrapper')) {
			if(option == 1) {
			$('sitereview_proncons-wrapper').style.display = 'block';
			$('sitereview_limit_proscons-wrapper').style.display = 'block';
			} else {
				$('sitereview_proncons-wrapper').style.display = 'none';
				$('sitereview_limit_proscons-wrapper').style.display = 'none';
			}
		}

	}

</script>