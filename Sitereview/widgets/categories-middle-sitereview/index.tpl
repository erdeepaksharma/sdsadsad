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

<ul class="seaocore_categories_box">
  <li>
    <?php $ceil_count = 0; $k = 0; ?>
    <?php for ($i = 0; $i <= count($this->categories); $i++) { ?>

			<?php if($ceil_count == 0) :?>
				<div>
			<?php endif;?>

			<div class="seaocore_categories_list_col">
				<?php $ceil_count++; ?>
					<?php 
						$category = "";
						if (isset($this->categories[$k]) && !empty($this->categories[$k])) {
							$category = $this->categories[$k];
						}

						$k++; 

						if(empty($category)) { 
							break;
						}
					?>

          <div class="seaocore_categories_list">
            <?php $total_subcat = count($category['sub_categories']); ?>

            <h6>
							<?php echo $this->htmlLink($this->url(array('category_id' => $category['category_id'], 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $category['category_id'])->getCategorySlug()), "sitereview_general_category_listtype_$this->listingtype_id"), $this->translate($category['category_name'])) ?> <?php if(isset($category['count'])):?> (<?php echo $category['count'] ?>) <?php endif;?>
            </h6>
	
						<?php if(!empty($this->show2ndlevelCategory)): ?>
							<div class="sub_cat" id="subcat_<?php echo $category['category_id'] ?>">
								<?php foreach ($category['sub_categories'] as $subcategory) : ?>
									<?php $subcategoryname = '<img src="'.$this->layout()->staticBaseUrl.'application/modules/Sitereview/externals/images/gray_bullet.png" alt="">' . $this->translate($subcategory['sub_cat_name']) ;                 
										if(isset($subcategory['count'])): $subcategoryname .= ' (' . ($subcategory['count']) . ')'; endif; 
									?>

									<?php echo $this->htmlLink($this->url(array('category_id' => $category['category_id'], 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $category['category_id'])->getCategorySlug(), 'subcategory_id' => $subcategory['sub_cat_id'], 'subcategoryname' => Engine_Api::_()->getItem('sitereview_category', $subcategory['sub_cat_id'])->getCategorySlug()), "sitereview_general_subcategory_listtype_$this->listingtype_id"), $this->translate($subcategoryname)) ?>

									<?php if(!empty($this->show3rdlevelCategory) && isset($subcategory['tree_sub_cat'])):?>
										<?php foreach ($subcategory['tree_sub_cat'] as $subsubcategory) : ?>
											<?php $subsubcategoryname = '<img src="'.$this->layout()->staticBaseUrl.'application/modules/Sitereview/externals/images/gray_arrow.png" alt="">' . $this->translate($subsubcategory['tree_sub_cat_name']);                                      
                        if(isset($subsubcategory['count'])): $subsubcategoryname .= ' (' . ($subsubcategory['count']) . ')'; endif;                 
											?>
											<?php echo $this->htmlLink($this->url(array('category_id' => $category['category_id'], 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $category['category_id'])->getCategorySlug(), 'subcategory_id' => $subcategory['sub_cat_id'], 'subcategoryname' => Engine_Api::_()->getItem('sitereview_category', $subcategory['sub_cat_id'])->getCategorySlug(), 'subsubcategory_id' => $subsubcategory['tree_sub_cat_id'], 'subsubcategoryname' => Engine_Api::_()->getItem('sitereview_category', $subsubcategory['tree_sub_cat_id'])->getCategorySlug()), "sitereview_general_subsubcategory_listtype_$this->listingtype_id"), $this->translate($subsubcategoryname)) ?>
										<?php endforeach; ?>
									<?php endif;?>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

					</div>
				</div>
			<?php if($ceil_count %3 == 0) :?>
				</div>
				<?php $ceil_count = 0; ?>
			<?php endif;?>
    <?php } ?>
  </li>
</ul>