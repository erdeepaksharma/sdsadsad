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

<div class="sm-content-list">
  <ul data-role="listview" data-icon="arrow-r" id="review_editors">
    <?php foreach( $this->editors as $user ): ?>
			<li>
				<a href='<?php echo $user->getHref();?>'>
          <?php echo $this->itemPhoto($user, 'thumb.icon'); ?>
					<h3><?php echo $user->getUserTitle($user->user_id);?></h3>
					<?php if(!empty($user->designation)): ?><p><?php echo $user->designation ?></p><?php endif;?>
					<?php if($this->countListingtypes > 1): ?>  
						<?php $params = array(); $params['visible'] = 1; $params['editorReviewAllow'] = 1;?>
						<?php $getDetails = $this->editorTable->getEditorDetails($user->user_id, 0, $params); ?>
						<?php if(($getCount = Count($getDetails)) > 0):  ?>
							<p>
								<?php $count = 0; ?>
								<?php echo $this->translate("Editor For:"); ?>
								<?php foreach($getDetails as $getDetail): ?>
									<?php $count++; ?>
									<p><?php echo $getDetail->title_plural; ?><?php if($count < $getCount): ?>,<?php endif; ?></p>
								<?php endforeach;?>
							</p>
						<?php endif; ?>
					<?php endif; ?>
					<p>
						<?php 
							$params = array();
							$params['owner_id'] = $user->user_id;
							$params['type'] = 'editor';
						?>  
						<?php $totalReviews = Engine_Api::_()->getDbTable('reviews', 'sitereview')->totalReviews($params); ?>
						<?php echo $this->translate(array('%s Review', '%s Reviews', $totalReviews), $this->locale()->toNumber($totalReviews));?>
					</p>
				</a>
			</li>
    <?php endforeach;?>
  </ul>
	<?php if ($this->editors->count() > 1): ?>
		<?php
			echo $this->paginationAjaxControl(
					$this->editors, $this->identity, 'review_editors', array('count' => $this->count, 'viewType' => $this->viewType, 'listingtype_id' => $this->listingtype_id, 'superEditor' => $this->view->superEditor));
		?>
	<?php endif; ?>
</div>


<style type="text/css">

.layout_sitereview_editors_sitereview > h3 {
	display:none;
}

</style>