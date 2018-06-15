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

<?php if($this->viewType): ?>

	<ul class="sr_editor_listing">
	  <?php foreach( $this->editors as $user ): ?>
	    <li>
	    	<div class="sr_editor_listing_photo">
		      <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.profile'), array('class' => '', 'title' => $user->displayname), array('title' => $user->getUserTitle($user->user_id))) ?>
	      </div>
	      <div class='sr_editor_listing_info'>
	        <div class='sr_editor_listing_name'>
	          <?php echo $this->htmlLink($user->getHref(), $user->getUserTitle($user->user_id), array('title' =>  $user->getUserTitle($user->user_id))) ?>
	        </div>
	        
	        <?php if(!empty($user->designation)): ?>
	        	<div class='sr_editor_listing_stat'>
	          	<?php echo $user->designation ?>
	           </div>
	        <?php endif; ?>
          
          <?php if($this->countListingtypes > 1): ?>  
            <?php $params = array(); $params['visible'] = 1; $params['editorReviewAllow'] = 1;?>
            <?php $getDetails = $this->editorTable->getEditorDetails($user->user_id, 0, $params); ?>
            <?php if(($getCount = Count($getDetails)) > 0):  ?>
              <div class="sr_editor_listing_stat seaocore_txt_light">
                <?php $count = 0; ?>
                <?php echo $this->translate("Editor For:"); ?>
                <?php foreach($getDetails as $getDetail): ?>
                  <?php $count++; ?>
                  <?php echo $this->htmlLink(array('route' => 'sitereview_general_listtype_'.$getDetail->listingtype_id), $getDetail->title_plural); ?><?php if($count < $getCount): ?>,<?php endif; ?>
                <?php endforeach;?>
              </div>
            <?php endif; ?>
          <?php endif; ?>            
          
	        <div class='sr_editor_listing_stat seaocore_txt_light'>
						<?php 
							$params = array();
							$params['owner_id'] = $user->user_id;
							$params['type'] = 'editor';
						?>  
	          <?php $totalReviews = Engine_Api::_()->getDbTable('reviews', 'sitereview')->totalReviews($params); ?>
	          <?php echo $this->translate(array('%s Review', '%s Reviews', $totalReviews), $this->locale()->toNumber($totalReviews));?>
	        </div>
	      </div>
	    </li>
	  <?php endforeach; ?>
	</ul>
	<div class="sr_editor_listing_more">
		<?php echo $this->htmlLink(array('route' => "sitereview_review_editor", 'action' => 'home'), $this->translate('View all Editors &raquo;')) ?>
	</div>
	
<?php else: ?>
	
	<ul class="seaocore_sidebar_list o_hidden">
    <?php foreach( $this->editors as $user ): ?>
      <li>
        <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon'), array('class' => 'popularmembers_thumb', 'title' => $user->displayname), array('title' => $user->getUserTitle($user->user_id))) ?>
        <div class='seaocore_sidebar_list_info'>
          <div class='seaocore_sidebar_list_title'>
            <?php echo $this->htmlLink($user->getHref(), $user->getUserTitle($user->user_id), array('title' =>  $user->getUserTitle($user->user_id))) ?>
          </div>

          <?php if(!empty($user->designation)): ?>
            <div class='seaocore_sidebar_list_details'>
              <?php echo $user->designation ?>
              </div>
            <?php endif; ?>
          <div class='seaocore_sidebar_list_details'>
            <?php 
              $params = array();
              $params['owner_id'] = $user->user_id;
              $params['type'] = 'editor';
            ?>
            <?php $totalReviews = Engine_Api::_()->getDbTable('reviews', 'sitereview')->totalReviews($params); ?>
            <?php echo $this->translate(array('%s Review', '%s Reviews', $totalReviews), $this->locale()->toNumber($totalReviews));?>
          </div>
        </div>
      </li>
    <?php endforeach; ?>
    <li class="seaocore_sidebar_more_link bold"><?php echo $this->htmlLink(array('route' => "sitereview_review_editor", 'action' => 'home'), $this->translate('View all Editors &raquo;')) ?></li>
  </ul>
<?php endif; ?>