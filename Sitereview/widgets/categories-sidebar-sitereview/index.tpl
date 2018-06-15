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
  	              . 'application/modules/Sitereview/externals/styles/style_sitereview.css');
?>

<script type="text/javascript">

  function show_subcat(cat_id)
  {
    if(document.getElementById('subcat_' + cat_id)) {
      if(document.getElementById('subcat_' + cat_id).style.display == 'block') {
        document.getElementById('subcat_' + cat_id).style.display = 'none';
        document.getElementById('img_' + cat_id).src = '<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/bullet-right.png';
      }
      else if(document.getElementById('subcat_' + cat_id).style.display == '') {
        document.getElementById('subcat_' + cat_id).style.display = 'none';
        document.getElementById('img_' + cat_id).src = '<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/bullet-right.png';
      }
      else {
        document.getElementById('subcat_' + cat_id).style.display = 'block';
        document.getElementById('img_' + cat_id).src = '<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/bullet-bottom.png';
      }
    }
  }

  function show_subsubcat(cat_id)
  {
    if(document.getElementById('subsubcat_' + cat_id)) {
      if(document.getElementById('subsubcat_' + cat_id).style.display == 'block') {
        document.getElementById('subsubcat_' + cat_id).style.display = 'none';
        document.getElementById('img_' + cat_id).src = '<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/bullet-right.png';
      }
      else if(document.getElementById('subsubcat_' + cat_id).style.display == '') {
        document.getElementById('subsubcat_' + cat_id).style.display = 'none';
        document.getElementById('img_' + cat_id).src = '<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/bullet-right.png';
      }
      else {
        document.getElementById('subsubcat_' + cat_id).style.display = 'block';
        document.getElementById('img_' + cat_id).src = '<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/bullet-bottom.png';
      }
    }
  }

	window.addEvent('domready', function() {
		var subcategory_default = '<?php echo $this->subcategory_id; ?>';
		var subsubcategory_default = '<?php echo $this->subsubcategory_id;?>';
		if(subcategory_default == 0)
		show_subcat('<?php echo $this->category_id; ?>');
		if(subsubcategory_default == 0)
		show_subsubcat('<?php echo $this->subcategory_id; ?>');
	});
</script>

<?php 
	$request = Zend_Controller_Front::getInstance()->getRequest();
	$module = $request->getModuleName();
	$controller = $request->getControllerName();
	$action = $request->getActionName();
?>

<?php if($module == 'sitereview' && $controller == 'index' && $action == 'top-rated'):?>
  <?php $url_action = 'top-rated';?>
<?php else:?>
  <?php $url_action = 'index';?>
<?php endif;?>

<?php if (count($this->categories)):?>
  <ul class="sr_browse_side_category">
    
		<?php foreach ($this->categories as $category):?>
			<?php $total_subcat = count($category['sub_categories']); ?>

			<?php if ($total_subcat > 0): ?>
				<li>
					<a href="javascript:show_subcat('<?php echo $category['category_id'] ?>')" id='button_<?php echo $category['category_id'] ?>' class="right_bottom_arrow">
						<?php if ($this->category_id != $category['category_id']): ?>
							<img alt=""  src='<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/bullet-right.png' border='0' id='img_<?php echo $category['category_id'] ?>'/>
						<?php elseif ($this->subcategory_id != 0 && $this->category_id == $category['category_id']): ?>
							<img alt="" src='<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/bullet-bottom.png' border='0' id='img_<?php echo $category['category_id'] ?>'/>
						<?php elseif ($this->category_id != 0 && $this->category_id == $category['category_id']): ?>
							<img alt="" src='<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/bullet-right.png' border='0' id='img_<?php echo $category['category_id'] ?>'/>
						<?php endif; ?>
					</a>
					<?php $category_name = $this->translate($category['category_name']);?>
					<?php $truncate_category = Engine_Api::_()->seaocore()->seaocoreTruncateText($category_name, $this->catTruncLimit);?>
					<a <?php if ($this->category_id == $category['category_id']): ?>class="bold"<?php endif; ?> href='<?php echo $this->url(array('action' => $url_action,'category_id' => $category['category_id'], 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $category['category_id'])->getCategorySlug()), "sitereview_general_category_listtype_".$category['listingtype_id']) ?>'>
            
						<span class="cat_icon"><?php if($category['file_id']):?><img alt=""  src='<?php echo $this->storage->get($category['file_id'], '')->getPhotoUrl(); ?>' /><?php endif; ?></span>
						<span class="cat_name" title="<?php echo $category_name; ?>"><?php echo $truncate_category; ?></span>
					</a>

					<ul id="subcat_<?php echo $category['category_id'] ?>" <?php if ($this->category_id != $category['category_id'] || $this->subcategory_id == 0): ?>style="display:none;"<?php endif; ?> >
					<?php foreach ($category['sub_categories'] as $subcategory) : ?>
						<?php $total_subsubcat = count($subcategory['tree_sub_cat']); ?>
						<?php if ($total_subsubcat > 0): ?>
							<li>
								<a href="javascript:show_subsubcat('<?php echo $subcategory['sub_cat_id'] ?>')" id='button_<?php echo $subcategory['sub_cat_id'] ?>' class="right_bottom_arrow">
									<?php if ($this->subcategory_id != $subcategory['sub_cat_id']): ?>
										<img alt=""  src='<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/bullet-right.png' border='0' id='img_<?php echo $subcategory['sub_cat_id'] ?>'/>
									<?php elseif ($this->subsubcategory_id != 0 && $this->subcategory_id == $subcategory['sub_cat_id']): ?>
										<img alt="" src='<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/bullet-bottom.png' border='0' id='img_<?php echo $subcategory['sub_cat_id'] ?>'/>
									<?php elseif ($this->subcategory_id != 0 && $this->subcategory_id == $subcategory['sub_cat_id']): ?>
										<img alt="" src='<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/bullet-right.png' border='0' id='img_<?php echo $subcategory['sub_cat_id'] ?>'/>
									<?php endif; ?>
								</a>
								<?php $subcategory_name = $this->translate($subcategory['sub_cat_name']);?>
								<?php $truncate_subcategory = Engine_Api::_()->seaocore()->seaocoreTruncateText($subcategory_name, $this->subCatTruncLimit);?>
								<a <?php if ($this->subcategory_id == $subcategory['sub_cat_id']): ?>class="bold"<?php endif; ?>  href='<?php echo $this->url(array('action' => $url_action,'category_id' => $category['category_id'], 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $category['category_id'])->getCategorySlug(), 'subcategory_id' => $subcategory['sub_cat_id'], 'subcategoryname' => Engine_Api::_()->getItem('sitereview_category', $subcategory['sub_cat_id'])->getCategorySlug()), "sitereview_general_subcategory_listtype_".$category['listingtype_id']) ?>'>

									<span class="cat_icon"><?php if($subcategory['file_id']):?><img alt=""  src='<?php echo $this->storage->get($subcategory['file_id'], '')->getPhotoUrl(); ?>' /><?php endif; ?></span>
									<span class="cat_name" title="<?php echo $subcategory_name ?>"><?php echo $truncate_subcategory ?></span>
								</a>

								<ul id="subsubcat_<?php echo $subcategory['sub_cat_id'] ?>" <?php if ($this->subcategory_id != $subcategory['sub_cat_id'] || $this->subsubcategory_id == 0): ?>style="display:none;"<?php endif; ?> >

									<?php if(isset($subcategory['tree_sub_cat'])):?>
										<?php foreach ($subcategory['tree_sub_cat'] as $subsubcategory) : ?>
											<li>
												<?php $subsubcategory_name = $this->translate($subsubcategory['tree_sub_cat_name']);?>
												<?php $truncate_subsubcategory = Engine_Api::_()->seaocore()->seaocoreTruncateText($subsubcategory_name, $this->subsubCatTruncLimit);?>
												<a <?php if ($this->subsubcategory_id == $subsubcategory['tree_sub_cat_id']): ?>class="bold"<?php endif; ?>  href='<?php echo $this->url(array('action' => $url_action,'category_id' => $category['category_id'], 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $category['category_id'])->getCategorySlug(), 'subcategory_id' => $subcategory['sub_cat_id'], 'subcategoryname' => Engine_Api::_()->getItem('sitereview_category', $subcategory['sub_cat_id'])->getCategorySlug(), 'subsubcategory_id' => $subsubcategory['tree_sub_cat_id'], 'subsubcategoryname' => Engine_Api::_()->getItem('sitereview_category', $subsubcategory['tree_sub_cat_id'])->getCategorySlug()), "sitereview_general_subsubcategory_listtype_".$category['listingtype_id']) ?>'>
													<span class="cat_icon"><?php if($subsubcategory['file_id']):?><img alt=""  src='<?php echo $this->storage->get($subsubcategory['file_id'], '')->getPhotoUrl(); ?>' /><?php endif; ?></span>
													<span class="cat_name" title="<?php echo $subsubcategory_name ?>"><?php echo $truncate_subsubcategory; ?></span>
												</a>
											</li>
										<?php endforeach; ?>
									<?php endif;?>
								</ul>
							</li>  
						<?php else:?>
							<li>
								<?php $subcategory_name = $this->translate($subcategory['sub_cat_name']);?>
								<?php $truncate_subcategory = Engine_Api::_()->seaocore()->seaocoreTruncateText($subcategory_name, $this->subCatTruncLimit);?>
								<a <?php if ($this->subcategory_id == $subcategory['sub_cat_id']): ?>class="bold"<?php endif; ?>  href='<?php echo $this->url(array('action' => $url_action,'category_id' => $category['category_id'], 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $category['category_id'])->getCategorySlug(), 'subcategory_id' => $subcategory['sub_cat_id'], 'subcategoryname' => Engine_Api::_()->getItem('sitereview_category', $subcategory['sub_cat_id'])->getCategorySlug()), "sitereview_general_subcategory_listtype_".$category['listingtype_id']) ?>'>
									<span class="cat_icon"><?php if($subcategory['file_id']):?><img alt=""  src='<?php echo $this->storage->get($subcategory['file_id'], '')->getPhotoUrl(); ?>' /><?php endif; ?></span>
									<span class="cat_name" title="<?php echo $subcategory_name ?>"><?php echo $truncate_subcategory; ?></span>
								</a>
							</li>  
						<?php endif;?>
						<?php endforeach; ?>
					</ul>
				</li>
      <?php else: ?>
        <li>
					<?php $category_name = $this->translate($category['category_name']);?>
					<?php $truncate_category = Engine_Api::_()->seaocore()->seaocoreTruncateText($category_name, $this->catTruncLimit);?>
          
					<a <?php if ($this->category_id == $category['category_id']): ?>class="bold"<?php endif; ?>  href='<?php echo $this->url(array('action' => $url_action,'category_id' => $category['category_id'], 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $category['category_id'])->getCategorySlug()), "sitereview_general_category_listtype_".$category['listingtype_id']) ?>'>
						<span class="cat_icon"><?php if($category['file_id']):?><img alt=""  src='<?php echo $this->storage->get($category['file_id'], '')->getPhotoUrl(); ?>' /><?php endif;?></span>
						<span class="cat_name" title="<?php echo $category_name ?>"><?php echo $truncate_category ?></span>
					</a>
          
        </li>
      <?php endif; ?>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>