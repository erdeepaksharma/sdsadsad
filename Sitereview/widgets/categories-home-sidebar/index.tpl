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

  function show_cats(listingtype_id)
  { 
    if(document.getElementById('cats_' + listingtype_id)) {
      if(document.getElementById('cats_' + listingtype_id).style.display == 'block' || document.getElementById('cats_' + listingtype_id).style.display == '') {
        document.getElementById('cats_' + listingtype_id).style.display = 'none';
        document.getElementById('img_cats_' + listingtype_id).src = '<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/icons/plus16.gif';
      }
      else if(document.getElementById('cats_' + listingtype_id).style.display == 'none'){
        document.getElementById('cats_' + listingtype_id).style.display = 'block';
        document.getElementById('img_cats_' + listingtype_id).src = '<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/icons/minus16.gif';
      }
    }
  }
  
  function show_subcats(category_id)
  {
    if(document.getElementById('subcats_' + category_id)) {
      if(document.getElementById('subcats_' + category_id).style.display == 'block' || document.getElementById('subcats_' + category_id).style.display == '') {
        document.getElementById('subcats_' + category_id).style.display = 'none';
        document.getElementById('img_subcats_' + category_id).src = '<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/icons/plus16.gif';
      }
      else if(document.getElementById('subcats_' + category_id).style.display == 'none'){
        document.getElementById('subcats_' + category_id).style.display = 'block';
        document.getElementById('img_subcats_' + category_id).src = '<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/icons/minus16.gif';
      }
    }
  }
  
</script>
    
<ul class="sr_browse_side_category">
  <?php foreach($this->listingTypesArray as $listingType): ?>
    <li>
      <a href='javascript:void(0);' onclick ="show_cats('<?php echo $listingType['listingtype_id']; ?>'); return false;">
      	<span class="cat_icon"><img id="img_cats_<?php echo $listingType['listingtype_id']?>" src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/icons/plus16.gif" /></span>
      </a>
      <a href="<?php echo $this->url(array('action' => 'home'), 'sitereview_general_listtype_'.$listingType['listingtype_id'], true) ?>"><?php echo $this->translate($listingType['title_plural']);?></a>
      <ul id="cats_<?php echo $listingType['listingtype_id']?>" style="display: none;">
        
        <?php foreach($listingType['categories'] as $category): ?>
          <li>
            <a href='javascript:void(0);' onclick ="show_subcats('<?php echo $category['category_id']; ?>')"><span class="cat_icon"><img id="img_subcats_<?php echo $category['category_id'] ?>" src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/icons/plus16.gif" /></span></a>
            <a href="<?php echo $this->url(array('category_id' => $category['category_id'], 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $category['category_id'])->getCategorySlug()), 'sitereview_general_category_listtype_'.$listingType['listingtype_id']); ?>"><?php echo $this->translate($category['category_name']);?></a>
            <ul id="subcats_<?php echo $category['category_id']?>" style="display: none;">
              
             <?php foreach($category['subcategories'] as $subcategory): ?>
              <li>
                <a href="<?php echo $this->url(array('category_id' => $category['category_id'], 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $category['category_id'])->getCategorySlug(), 'subcategory_id' => $subcategory['subcategory_id'], 'subcategoryname' => Engine_Api::_()->getItem('sitereview_category', $subcategory['subcategory_id'])->getCategorySlug()), 'sitereview_general_subcategory_listtype_'.$listingType['listingtype_id'])?>"><?php echo $this->translate($subcategory['subcategory_name']);?></a>
              </li>
             <?php endforeach;?>
              
            </ul>
          </li>
        <?php endforeach; ?>
      </ul>
    </li>
  <?php endforeach; ?>
</ul>
