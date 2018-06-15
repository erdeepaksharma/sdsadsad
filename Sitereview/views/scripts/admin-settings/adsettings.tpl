<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: adsettings.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
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

<div class='seaocore_settings_form'>
	<div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<script type="text/javascript">

  window.addEvent('domready', function() {
    showads('<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.communityads', 1); ?>');
  });

  function showads(option) {	 	
    if(option == 1) {
      if($('sitereview_adalbumcreate-wrapper'))
			$('sitereview_adalbumcreate-wrapper').style.display = 'block';
      if($('sitereview_addiscussionview-wrapper'))
			$('sitereview_addiscussionview-wrapper').style.display = 'block';
      if($('sitereview_addiscussioncreate-wrapper'))
			$('sitereview_addiscussioncreate-wrapper').style.display = 'block';
      if($('sitereview_addiscussionreply-wrapper'))
			$('sitereview_addiscussionreply-wrapper').style.display = 'block';		
      if($('sitereview_adtopicview-wrapper'))
      $('sitereview_adtopicview-wrapper').style.display = 'block';			
      if($('sitereview_advideocreate-wrapper'))
			$('sitereview_advideocreate-wrapper').style.display = 'block';
      if($('sitereview_advideoedit-wrapper'))
			$('sitereview_advideoedit-wrapper').style.display = 'block';
      if($('sitereview_advideodelete-wrapper'))
			$('sitereview_advideodelete-wrapper').style.display = 'block';			
      if($('sitereview_adtagview-wrapper')) 		
			$('sitereview_adtagview-wrapper').style.display = 'block';
    } 
    else {
      if($('sitereview_adalbumcreate-wrapper'))
			$('sitereview_adalbumcreate-wrapper').style.display = 'none';
      if($('sitereview_addiscussionview-wrapper'))
			$('sitereview_addiscussionview-wrapper').style.display = 'none';
      if($('sitereview_addiscussioncreate-wrapper'))
			$('sitereview_addiscussioncreate-wrapper').style.display = 'none';
      if($('sitereview_addiscussionreply-wrapper'))
			$('sitereview_addiscussionreply-wrapper').style.display = 'none';		
      if($('sitereview_adtopicview-wrapper'))
      $('sitereview_adtopicview-wrapper').style.display = 'none';			
      if($('sitereview_advideocreate-wrapper'))
			$('sitereview_advideocreate-wrapper').style.display = 'none';
      if($('sitereview_advideoedit-wrapper'))
			$('sitereview_advideoedit-wrapper').style.display = 'none';
      if($('sitereview_advideodelete-wrapper'))
			$('sitereview_advideodelete-wrapper').style.display = 'none';			
      if($('sitereview_adtagview-wrapper')) 		
			$('sitereview_adtagview-wrapper').style.display = 'none';
    } 	
  } 
</script>