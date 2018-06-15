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

<?php 
	$this->headLink()->appendStylesheet($this->layout()->staticBaseUrl
  	              . 'application/modules/Seaocore/externals/styles/styles.css');
?>

<ul class="sr_sponsored_categories">
  <?php $count = 0; foreach($this->categories as $category): $count++;?>
    <li>
      <?php $htmlImage = ''; if($category->file_id && $this->showIcon):?>
        <?php $src = $this->storage->get($category->file_id, '')->getPhotoUrl(); ?>
        <?php $htmlImage = '<span class="sr_cat_icon">'. $this->htmlImage($src) . '</span>'; ?>
      <?php endif; ?>
      <?php if($category->cat_dependency == 0 && $category->subcat_dependency == 0): ?>
       <?php echo $this->htmlLink($this->url(array('category_id' => $category->category_id, 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $category->category_id)->getCategorySlug()), "sitereview_general_category_listtype_$category->listingtype_id"), $htmlImage.$this->translate($category->category_name)) ?>
      <?php elseif($category->cat_dependency != 0 && $category->subcat_dependency == 0):?>
        <?php $getCatDependancy = $this->tableCategory->getCategory($category->cat_dependency);?>
        <?php echo $this->htmlLink($this->url(array('category_id' => $getCatDependancy->category_id, 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $getCatDependancy->category_id)->getCategorySlug(), 'subcategory_id' => $category->category_id, 'subcategoryname' => Engine_Api::_()->getItem('sitereview_category', $category->category_id)->getCategorySlug()), "sitereview_general_subcategory_listtype_$category->listingtype_id"), $htmlImage.$this->translate($category->category_name)) ?>
      <?php else:?>
        <?php $getSubCatDependancy = $this->tableCategory->getCategory($category->cat_dependency);?>
        <?php $getCatDependancy = $this->tableCategory->getCategory($getSubCatDependancy->cat_dependency); ?>
        <?php echo $this->htmlLink($this->url(array('category_id' => $getCatDependancy->category_id, 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $getCatDependancy->category_id)->getCategorySlug(), 'subcategory_id' => $getSubCatDependancy->category_id, 'subcategoryname' => Engine_Api::_()->getItem('sitereview_category', $getSubCatDependancy->category_id)->getCategorySlug(), 'subsubcategory_id' => $category->category_id, 'subsubcategoryname' => Engine_Api::_()->getItem('sitereview_category', $category->category_id)->getCategorySlug()), "sitereview_general_subsubcategory_listtype_$category->listingtype_id"), $htmlImage.$this->translate($category->category_name)) ?>
      <?php endif;?> 
    	<?php if($count < $this->totalCategories):?>
      	<span>|</span>
    	<?php endif; ?>
    </li>  
  <?php endforeach;?>
</ul>