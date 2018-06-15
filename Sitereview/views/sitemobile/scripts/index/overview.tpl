<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: overview.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php echo $this->partial('application/modules/Sitereview/views/sitemobile/scripts/dashboard/header.tpl', array('sitereview'=>$this->sitereview));?>
<div class="dashboard-content">  
	<?php if(!empty($this->success)): ?>
		<ul class="form-notices" >
			<li>
				<?php echo $this->translate($this->success); ?>
			</li>
		</ul>
  <?php endif; ?>
	<?php echo $this->form->render($this); ?>
</div>
 
<script type="text/javascript"> 
    sm4.core.runonce.add(function(){  
     setTimeout(function() {
       sm4.core.tinymce.showTinymce($.mobile.activePage.find('#body')[0]);
       }, 1000);
    });
 </script>  