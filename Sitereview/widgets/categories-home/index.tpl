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

<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl
  	              . 'application/modules/Sitereview/externals/styles/style_sitereview.css'); ?>
<script type="text/javascript">
  function more_subcats(cat_id) {
    $$('.categories_more_subcats_'+cat_id).each(function(el){
				el.style.display = 'block';
		});
    
    $('more_link_'+cat_id).style.display = 'none';
    $('fewer_link_'+cat_id).style.display = 'block';
  }
  
  function fewer_subcats(cat_id) {
    $$('.categories_more_subcats_'+cat_id).each(function(el){
				el.style.display = 'none';
		});
    
    $('fewer_link_'+cat_id).style.display = 'none';
    $('more_link_'+cat_id).style.display = 'block';
  }  
  
  function more_subsubcats(subcat_id) {
    $$('.categories_more_subsubcats_'+subcat_id).each(function(el){
				el.style.display = 'block';
		});
    
    $('more_link_subsubcats_'+subcat_id).style.display = 'none';
    $('fewer_link_subsubcats_'+subcat_id).style.display = 'block';
  }
  
  function fewer_subsubcats(subcat_id) {
    $$('.categories_more_subsubcats_'+subcat_id).each(function(el){
				el.style.display = 'none';
		});
    
    $('fewer_link_subsubcats_'+subcat_id).style.display = 'none';
    $('more_link_subsubcats_'+subcat_id).style.display = 'block';
  }    
</script>  

<?php if(!empty($this->listingtype_id) && $this->listingTypeCount > 1): ?>
	<div class="sr_listing_breadcrumb">
	  <?php echo $this->htmlLink(array('route' => 'sitereview_review_categories'), $this->translate('All Categories')); ?>
    <?php echo '<span class="brd-sep seaocore_txt_light">&raquo;</span>'; ?>
    <?php
      $listingtypeArray = Zend_Registry::get('listingtypeArray' . $this->listingtype_id);
      echo ucfirst($listingtypeArray->title_plural);    
    ?>
	</div> 

<?php endif; ?>

<ul class="sr_categories_box">
  <li class="b_light">
  	<?php if(empty($this->listingtype_id) && $this->listingTypeCount > 1): ?>
  		<div class="sr_categories_box_head"><?php echo $this->translate('All Categories'); ?></div>
  	<?php endif; ?>
    <?php $ceil_count = 0; $k = 0; ?>
    <?php for ($i = 0; $i <= $this->totalCategories; $i++) { ?>
			<?php if($ceil_count == 0) :?>
				<div class="b_medium clr o_hidden">
			<?php endif;?>
			<div class="sr_categories_list_col">
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
          <div class="sr_categories_list">
            <?php $total_subcat = count($category['sub_categories']); ?>
            <h6>
							<?php echo $this->htmlLink($this->url(array('category_id' => $category['category_id'], 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $category['category_id'])->getCategorySlug()), "sitereview_general_category_listtype_".$category['listingtype_id']), $this->translate($category['category_name'])) ?><?php if(isset($category['count'])): ?> (<?php echo $category['count'] ?>) <?php endif; ?>
            </h6>
	
						<?php if(!empty($this->show2ndlevelCategory)): ?>
							<div class="sub_cat clr" id="subcat_<?php echo $category['category_id'] ?>">
								<?php $subCatCount = 0; foreach ($category['sub_categories'] as $subcategory) :  $subCatCount++; ?>

									<?php $subcategoryname = $this->translate($subcategory['sub_cat_name']);?>
                
                  <?php if($subCatCount > 4): ?> 
                    <div style="display:none;" class="clr o_hidden sub_cat_list categories_more_subcats_<?php echo $category['category_id']; ?>">
                  <?php else: ?>
                    <div class="clr o_hidden sub_cat_list" style="display:block;">
                  <?php endif; ?>
                    <?php echo $this->htmlLink($this->url(array('category_id' => $category['category_id'], 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $category['category_id'])->getCategorySlug(), 'subcategory_id' => $subcategory['sub_cat_id'], 'subcategoryname' => Engine_Api::_()->getItem('sitereview_category', $subcategory['sub_cat_id'])->getCategorySlug()), "sitereview_general_subcategory_listtype_".$category['listingtype_id']), $this->translate($subcategoryname)) ?> 								 <?php       
                      if(isset($subcategory['count'])) {
                        echo '<span class="fleft">&nbsp;(' . ($subcategory['count']) . ')</span>';
                      }
                    ?>
                  	<span class="sub_cat_list_option">
	                  	<?php if(!empty($this->show3rdlevelCategory) && Count($subcategory['tree_sub_cat']) > 0): ?>  
                      
                        <?php if($this->viewType == 'expanded'): ?>
                          <span id="more_link_subsubcats_<?php echo $subcategory['sub_cat_id']; ?>" style="display:none;">
                        <?php else: ?>
                          <span id="more_link_subsubcats_<?php echo $subcategory['sub_cat_id']; ?>" >
                        <?php endif; ?>
	                          
	                      <a href="javascript:void(0)" onclick="more_subsubcats('<?php echo $subcategory['sub_cat_id']; ?>');" title="<?php echo $this->translate('Click to show 3rd level category') ?>">[+]</a>
                          </span>
                      
                        <?php if($this->viewType == 'expanded'): ?>
                          <span id="fewer_link_subsubcats_<?php echo $subcategory['sub_cat_id']; ?>"> 
                        <?php else: ?>
                          <span id="fewer_link_subsubcats_<?php echo $subcategory['sub_cat_id']; ?>" style="display:none;"> 
                        <?php endif; ?>  
                          
	                      <a href="javascript:void(0)" onclick="fewer_subsubcats('<?php echo $subcategory['sub_cat_id']; ?>');" title="<?php echo $this->translate('Click to hide 3rd level category')?>">[-]</a>
	                      </span>                      
	                    <?php endif; ?>
                    </span>              
										<?php if(!empty($this->show3rdlevelCategory) && isset($subcategory['tree_sub_cat'])):?>
											<?php foreach ($subcategory['tree_sub_cat'] as $subsubcategory) : ?>
	                      
                      <?php if($this->viewType == 'expanded'): ?>
                        <div class="clr third_level_cat categories_more_subsubcats_<?php echo $subcategory['sub_cat_id']; ?>">
                      <?php else: ?>
	                      <div style="display:none;" class="clr third_level_cat categories_more_subsubcats_<?php echo $subcategory['sub_cat_id']; ?>">
                      <?php endif; ?>  
	                      
											<?php $subsubcategoryname = $this->translate($subsubcategory['tree_sub_cat_name']);?>
												<?php echo $this->htmlLink($this->url(array('category_id' => $category['category_id'], 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $category['category_id'])->getCategorySlug(), 'subcategory_id' => $subcategory['sub_cat_id'], 'subcategoryname' => Engine_Api::_()->getItem('sitereview_category', $subcategory['sub_cat_id'])->getCategorySlug(), 'subsubcategory_id' => $subsubcategory['tree_sub_cat_id'], 'subsubcategoryname' => Engine_Api::_()->getItem('sitereview_category', $subsubcategory['tree_sub_cat_id'])->getCategorySlug()), "sitereview_general_subsubcategory_listtype_".$category['listingtype_id']), $this->translate($subsubcategoryname)) ?>
                          <?php 
                            if(isset($subsubcategory['count'])) {
                              echo ' (' . ($subsubcategory['count']) . ') ';     
                            }
                          ?>
	                       </div> 
											<?php endforeach; ?>
										<?php endif;?>
                  </div>
                  
                  <?php if($subCatCount > 4 && $total_subcat == $subCatCount): ?>    
                    <div class="clr o_hidden sub_cat_list sub_cat_list_more" id="more_link_<?php echo $category['category_id']; ?>">
											<a href="javascript:void(0)" onclick="more_subcats(<?php echo $category['category_id']; ?>);">
												<b><?php echo $this->translate("More");?></b>
												<?php echo $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/gray_bullet_bottom.png', '', array('title' => $this->translate('More'))) ?>
											</a>
                    </div>                      
                    <div class="clr o_hidden sub_cat_list sub_cat_list_more" id="fewer_link_<?php echo $category['category_id']; ?>" style="display:none;">   
                    	<a href="javascript:void(0)" onclick="fewer_subcats(<?php echo $category['category_id']; ?>);">
                    		<b><?php echo $this->translate("Fewer");?></b>
                    		<?php echo $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/gray_bullet_top.png', '', array('title' => $this->translate('Fewer'))) ?>
                    	</a>
                    </div>  
                  <?php endif;?>                       
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php if($ceil_count % 4 == 0 && $this->totalCategories % 4 != 0) :?>
				</div>
				<?php $ceil_count = 0; ?>
			<?php endif;?>
    <?php } ?>
  </li>
</ul>
  
<?php if(empty($this->showCount)): ?>
  <div class="sr_categories_list_link fright">
    <?php if($this->listingtype_id > 0): ?>
      [ <?php echo $this->htmlLink(array('route' => 'sitereview_review_categories_'.$this->listingtype_id, 'showCount' => 1), $this->translate('See item counts')); ?> ]
    <?php else: ?>
      [ <?php echo $this->htmlLink(array('route' => 'sitereview_review_categories', 'showCount' => 1), $this->translate('See item counts')); ?> ]
    <?php endif; ?>
	</div> 
<?php endif; ?>