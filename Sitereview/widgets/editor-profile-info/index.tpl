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

<ul class="seaocore_sidebar_list sr_editor_profile_info">
  <li>
		<div class="sr_editor_profile_details o_hidden">
			<?php if(!empty($this->badge_photo_id)): ?>
				<?php $thumb_path = Engine_Api::_()->storage()->get($this->badge_photo_id, '')->getPhotoUrl(); ?>
				<?php if(!empty($thumb_path)): ?>
					<img width="50px" src='<?php echo $thumb_path?>' alt="" class="fleft" />
				<?php endif; ?>
			<?php endif; ?>
          
      <?php if(!empty($this->editor->details)):  ?>    
				<div class="sr_editor_profile_stats">
					<?php echo $this->viewMore($this->editor->details, 500, 5000); ?>
				</div>          
      <?php endif; ?>    
          
			<?php if(!empty($this->editor->designation) && $this->show_designation): ?>
				<div class="sr_editor_profile_stats o_hidden">
					<span><i><?php echo $this->translate("Designation:"); ?></i></span><br />
					<span><b><?php echo $this->editor->designation; ?></b></span>
				</div>
			<?php endif; ?>
			
	    <?php if($this->countListingtypes > 1): ?>  
	      <?php if(($getCount = Count($this->getDetails)) > 0):  ?>
	        <div class="sr_editor_profile_stats o_hidden">
	          <?php $count = 0; ?>
	          <?php echo $this->translate("Editor For:"); ?>
	          <?php foreach($this->getDetails as $getDetail): ?>
	            <?php $count++; ?>
	            <?php echo $this->htmlLink(array('route' => 'sitereview_general_listtype_'.$getDetail->listingtype_id), $getDetail->title_plural); ?><?php if($count < $getCount): ?>,<?php endif; ?>
	          <?php endforeach;?>
	        </div>
	      <?php endif; ?>
    <?php endif; ?>
		</div>
    <?php echo $this->content()->renderWidget("sitereview.write-sitereview", array("removeContent" => true)); ?>
	</li>
</ul>


