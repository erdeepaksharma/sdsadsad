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
  <a href="<?php echo $this->listingType->getHref() ?>"><?php echo $this->translate("$this->title_plural Home");?></a>
  <?php echo '<span class="brd-sep seaocore_txt_light">&raquo;</span>'; ?>
  <?php if ($this->category_name): ?>
    <a href="<?php echo $this->url(array('category_id' => $this->sitereview->category_id, 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $this->sitereview->category_id)->getCategorySlug()), "sitereview_general_category_listtype_" . $this->sitereview->listingtype_id); ?>"><?php echo $this->translate($this->category_name); ?></a>
    <?php echo '<span class="brd-sep seaocore_txt_light">&raquo;</span>'; ?>
    <?php if (!empty($this->subcategory_name)): ?>
      <a href="<?php echo $this->url(array('category_id' => $this->sitereview->category_id, 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $this->sitereview->category_id)->getCategorySlug(), 'subcategory_id' => $this->sitereview->subcategory_id, 'subcategoryname' => Engine_Api::_()->getItem('sitereview_category', $this->sitereview->subcategory_id)->getCategorySlug()), "sitereview_general_subcategory_listtype_" . $this->sitereview->listingtype_id) ?>"><?php echo $this->translate($this->subcategory_name); ?></a>
      <?php echo '<span class="brd-sep seaocore_txt_light">&raquo;</span>'; ?>
      <?php if (!empty($this->subsubcategory_name)):?>
        <a href="<?php echo $this->url(array('category_id' => $this->sitereview->category_id, 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $this->sitereview->category_id)->getCategorySlug(), 'subcategory_id' => $this->sitereview->subcategory_id, 'subcategoryname' => Engine_Api::_()->getItem('sitereview_category', $this->sitereview->subcategory_id)->getCategorySlug(), 'subsubcategory_id' => $this->sitereview->subsubcategory_id, 'subsubcategoryname' => Engine_Api::_()->getItem('sitereview_category', $this->sitereview->subsubcategory_id)->getCategorySlug()), "sitereview_general_subsubcategory_listtype_" . $this->sitereview->listingtype_id) ?>"><?php echo $this->translate($this->subsubcategory_name); ?></a>
        <?php echo '<span class="brd-sep seaocore_txt_light">&raquo;</span>'; ?>
      <?php endif; ?>
    <?php endif; ?>
  <?php endif; ?>
  <?php echo $this->sitereview->getTitle(); ?>
</div>
