<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: delete.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>
 
 
 
 <div class="layout_middle">
	<form method="post" class="global_form">
		<div>
			<div>
				<h3><?php echo $this->translate('Delete Review?'); ?></h3>
				<p>
					 <?php echo $this->translate('Are you sure that you want to delete the Review with title "%s"? It will not be recoverable after being deleted.', $this->review->getTitle()); ?>
				</p>
				<br />
			
				<input type="hidden" name="confirm" value="true"/>
					<button type='submit' data-theme="b" data-inline="true" ><?php echo $this->translate('Delete'); ?></button>
          <?php echo $this->translate('or'); ?> 
            <a href="#" data-rel="back" data-role="button" data-inline="true" >
              <?php echo $this->translate('Cancel') ?>
            </a>

			
			</div>
		</div>
	</form>
</div>
