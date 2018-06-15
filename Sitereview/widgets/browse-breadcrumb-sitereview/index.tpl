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
 
<?php if(!empty($this->category_id) || (isset($this->formValues['tag']) && !empty($this->formValues['tag']) && isset($this->formValues['tag_id']) && !empty($this->formValues['tag_id']))): ?>
	<div class="sr_listing_breadcrumb">
		<?php if(!empty($this->category_id)): ?>
    
      <?php echo $this->htmlLink($this->url(array('action' => $url_action), "sitereview_general_listtype_$this->listingtype_id"), $this->translate("Browse $this->listing_plural_uc")) ?>
    
			<?php if ($this->category_name != ''): ?>
				<?php echo '<span class="brd-sep seaocore_txt_light">&raquo;</span>'; ?>
			<?php endif; ?>

				<?php
					$this->category_name = $this->translate($this->category_name);
					$this->subcategory_name = $this->translate($this->subcategory_name);
					$this->subsubcategory_name = $this->translate($this->subsubcategory_name);
				?>
				<?php if ($this->category_name != '' ) :?>
          <?php if ($this->subcategory_name != ''):?> 

            <?php echo $this->htmlLink($this->url(array('action' => $url_action,'category_id' => $this->category_id, 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $this->category_id)->getCategorySlug()), "sitereview_general_category_listtype_$this->listingtype_id"), $this->translate($this->category_name)) ?>
          <?php else: ?>
            <?php echo $this->translate($this->category_name) ?>   
          <?php endif; ?>
					<?php if ($this->subcategory_name != ''):?> 
						<?php echo '<span class="brd-sep seaocore_txt_light">&raquo;</span>'; ?>
            <?php if(!empty($this->subsubcategory_name)): ?>
              <?php echo $this->htmlLink($this->url(array('action' => $url_action,'category_id' => $this->category_id, 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $this->category_id)->getCategorySlug(), 'subcategory_id' => $this->subcategory_id, 'subcategoryname' => ucfirst(Engine_Api::_()->getItem('sitereview_category', $this->subcategory_id)->getCategorySlug())), "sitereview_general_subcategory_listtype_$this->listingtype_id"), $this->translate($this->subcategory_name)) ?>   
            <?php else: ?>
              <?php echo $this->translate($this->subcategory_name) ?>       
            <?php endif; ?>
						<?php if(!empty($this->subsubcategory_name)):?>
							<?php echo '<span class="brd-sep seaocore_txt_light">&raquo;</span>';?>
							<?php echo $this->translate($this->subsubcategory_name); ?>    
						<?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif;?>

			<?php if(((isset($this->formValues['tag']) && !empty($this->formValues['tag']) && isset($this->formValues['tag_id']) && !empty($this->formValues['tag_id'])))): ?>
				<?php $tag_value = $this->formValues['tag']; $tag_value_id = $this->formValues['tag_id']; $browse_url = $this->url(array('action' => $url_action), "sitereview_general_listtype_$this->listingtype_id", true)."?tag=$tag_value&tag_id=$tag_value_id";?>
				<?php if($this->category_name):?><br /><?php endif; ?>
				<?php echo $this->translate("Showing $this->listing_plural_lc tagged with: ");?>
				<b><a href='<?php echo $browse_url;?>'>#<?php echo $this->formValues['tag'] ?></a>
        <?php if($this->current_url2): ?>  
          <a href="<?php echo $this->url(array( 'action' => $url_action), "sitereview_general_listtype_$this->listingtype_id", true)."?".$this->current_url2; ?>"><?php echo $this->translate('(x)');?></a></b>
        <?php else: ?>
          <a href="<?php echo $this->url(array( 'action' => $url_action), "sitereview_general_listtype_$this->listingtype_id", true); ?>"><?php echo $this->translate('(x)');?></a></b>        
        <?php endif; ?>
			<?php endif; ?>
	</div>
<?php endif; ?>

