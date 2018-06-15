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

<?php $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');?>
<ul class="sr_editor_listing">
  <?php foreach( $this->editors as $editor ):?>
    <li>
    	<div class="sr_editor_listing_photo">
        <?php echo $this->htmlLink($editor->getHref(), $this->itemPhoto($editor, 'thumb.profile'), array('class' => 'editors_thumb')) ?>
      </div>
      <div class='sr_editor_listing_info'>
        <div class='sr_editor_listing_name'>
          <?php echo $this->htmlLink($editor->getHref(), $editor->getUserTitle($editor->user_id)) ?>
        </div>
                
				<?php if(!empty($editor->designation)): ?>
					<div class="sr_editor_listing_stat"><?php echo $this->translate($editor->designation);?></div>
				<?php endif; ?>
          
        <?php if($this->countListingtypes > 1): ?>  
          <?php $params = array(); $params['visible'] = 1; $params['editorReviewAllow'] = 1;?>
          <?php $getDetails = $this->editorTable->getEditorDetails($editor->user_id, 0, $params); ?>
          <?php if(($getCount = Count($getDetails)) > 0):  ?>
            <div class="sr_editor_listing_stat seaocore_txt_light">
              <?php $count = 0; ?>
              <?php echo $this->translate("Editor For:"); ?>
              <?php foreach($getDetails as $getDetail): ?>
                <?php $count++; ?>
                <?php echo $this->htmlLink(array('route' => 'sitereview_general_listtype_'.$getDetail->listingtype_id), $this->translate($getDetail->title_plural)); ?><?php if($count < $getCount): ?>,<?php endif; ?>
              <?php endforeach;?>
            </div>
          <?php endif; ?>
        <?php endif; ?>  
        <?php 
				$params = array();
				$params['type'] = 'editor';
        $params['owner_id'] = $editor->user_id;
        ?> 
        <?php $totalReviews = Engine_Api::_()->getDbTable('reviews', 'sitereview')->totalReviews($params); ?>
        <div class="sr_editor_listing_stat seaocore_txt_light"> 
          <?php echo $this->translate(array('%s Review', '%s Reviews', $totalReviews), $this->locale()->toNumber($totalReviews));?>
        </div>          
          
        <?php if(!$editor->isSelf($this->viewer()) && $editor->getUserEmail($editor->user_id)): ?>
          <div class="sr_editor_listing_stat"><b><?php echo $this->htmlLink(array('route' => "sitereview_review_editor", 'action' => 'editor-mail', 'user_id' => $editor->user_id), $this->translate('Email %s',$editor->getUserTitle($editor->user_id)),  array('class'=>'smoothbox')) ?></b></div>
        <?php endif; ?>
          
				<div class="sr_editor_listing_stat"><b><?php echo $this->htmlLink($editor->getHref(), $this->translate('View Profile &raquo;')) ?></b></div>
      </div>
    </li>
  <?php endforeach; ?>
</ul>