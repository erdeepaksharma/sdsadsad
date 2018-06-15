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

<div class="sr_listing_breadcrumb">
	<a href="<?php echo $this->url(array('action' => 'home'), "sitereview_general_listtype_" . $this->listingtype_id); ?>">
	  <?php echo $this->title_plural ?></a>
	<?php echo '<span class="brd-sep seaocore_txt_light">&raquo;</span>'; ?>
	<?php if ($this->category_name): ?>
	  <a href="<?php echo $this->url(array('category_id' => $this->sitereview->category_id, 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $this->sitereview->category_id)->getCategorySlug()), "sitereview_general_category_listtype_" . $this->listingtype_id); ?>">
	    <?php echo $this->translate($this->category_name); ?>
	  </a>
	  <?php if (!empty($this->subcategory_name)): echo '<span class="brd-sep seaocore_txt_light">&raquo;</span>'; ?>
	    <a href="<?php echo $this->url(array('category_id' => $this->sitereview->category_id, 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $this->sitereview->category_id)->getCategorySlug(), 'subcategory_id' => $this->sitereview->subcategory_id, 'subcategoryname' => Engine_Api::_()->getItem('sitereview_category', $this->sitereview->subcategory_id)->getCategorySlug()), "sitereview_general_subcategory_listtype_" . $this->listingtype_id) ?>">
	      <?php echo $this->translate($this->subcategory_name); ?>
	    </a>
	    <?php if (!empty($this->subsubcategory_name)): echo '<span class="brd-sep seaocore_txt_light">&raquo;</span>'; ?>
	      <a href="<?php echo $this->url(array('category_id' => $this->sitereview->category_id, 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $this->sitereview->category_id)->getCategorySlug(), 'subcategory_id' => $this->sitereview->subcategory_id, 'subcategoryname' =>  Engine_Api::_()->getItem('sitereview_category', $this->sitereview->subcategory_id)->getCategorySlug(), 'subsubcategory_id' => $this->sitereview->subsubcategory_id, 'subsubcategoryname' => Engine_Api::_()->getItem('sitereview_category', $this->sitereview->subsubcategory_id)->getCategorySlug()), 'sitereview_general_subsubcategory_listtype_' . $this->listingtype_id) ?>">
	        <?php echo $this->translate($this->subsubcategory_name); ?></a>
	    <?php endif; ?>
	  <?php endif; ?>
	<?php endif; ?>
	<?php echo '<span class="brd-sep seaocore_txt_light">&raquo;</span>'; ?>
	<?php echo $this->htmlLink($this->sitereview->getHref(), $this->sitereview->getTitle()) ?>
	<?php echo '<span class="brd-sep seaocore_txt_light">&raquo;</span>';?>
	<a href='<?php echo $this->url(array('listing_id' => $this->sitereview->listing_id, 'slug' => $this->sitereview->getSlug(), 'tab' => $this->tab_id), 'sitereview_entry_view_listtype_'.$this->listingtype_id, true) ?>'><?php echo $this->translate('Reviews'); ?></a>
	<?php echo '<span class="brd-sep seaocore_txt_light">&raquo;</span>';?>
	<?php echo $this->reviews->getTitle(); ?>
</div>