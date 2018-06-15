<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: applay-compare.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<form method="post" class="global_form_popup">
  <div>
    <h3><?php echo $this->translate('Apply Comparison?'); ?></h3>
    <p>
      <?php if($this->category->subcat_dependency):?>
       <?php echo $this->translate('Are you sure you want to allow users to compare listings created under "%s" 3rd level category?',$this->category->getTitle()); ?>
      <?php  $subcat = Engine_Api::_()->getItem('sitereview_category', $this->category->cat_dependency);
      if($subcat->apply_compare):
       echo $this->translate('<br><br>[<b>Note:</b> If you apply comparison on this 3rd level category, then for other 3rd level categories of "%s", comparison will be automatically applied and listings created under same 3rd level categories can be compared.]',$subcat->getTitle()); 
      else:
        $cat = Engine_Api::_()->getItem('sitereview_category', $subcat->cat_dependency);
        echo $this->translate('<br><br>[<b>Note:</b> If you apply comparison on this 3rd level category, then for other sub-categories of "%s", comparison will be automatically applied and listings created under same sub-category can be compared. You can explicitly apply comparison on 3rd level categories by using "Apply Comparison" links for each.]',$cat->getTitle()); 
      endif;
      ?>
      
      <?php elseif($this->category->cat_dependency):?>
         <?php echo $this->translate('Are you sure you want to allow users to compare listings created under "%s" sub-category?',$this->category->getTitle()); ?>
      <?php  $cat = Engine_Api::_()->getItem('sitereview_category', $this->category->cat_dependency);
      if($cat->apply_compare):
       echo $this->translate('<br><br>[<b>Note:</b> If you apply comparison on this sub-category, then for other sub-categories of %s, comparison will be automatically applied and listings created under same sub-categories can be compared.]',$cat->getTitle()); 
      endif;
      ?>
      <?php else:?>
       <?php echo $this->translate('Are you sure you want to allow users to compare listings created under "%s" category?',$this->category->getTitle()); ?>
      <?php endif;?>
    </p>
    <br />
    <p>
      <input type="hidden" name="confirm" value="<?php echo $this->category_id ?>"/>
      <button type='submit'><?php echo $this->translate('Apply'); ?></button>
      <?php echo $this->translate('or'); ?> <a href='javascript:void(0);' onclick='javascript:parent.Smoothbox.close()'><?php echo $this->translate('cancel'); ?></a>
    </p>
  </div>
</form>

