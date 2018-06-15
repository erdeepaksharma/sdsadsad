<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: detail.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php
  $reviewApi = Engine_Api::_()->sitereview();
  $expirySettings = $reviewApi->expirySettings($this->sitereviewDetail->listingtype_id);
?>
<div class="global_form_popup sr_listing_details_view">
	<h3><?php echo $this->translate('Listing Details'); ?></h3>
	<div class="top clr">
		<?php echo $this->htmlLink($this->sitereviewDetail->getHref(array('profile_link' => 1)), $this->itemPhoto($this->sitereviewDetail, 'thumb.icon'), array('target' => '_blank')); ?>
    <?php echo $this->htmlLink($this->sitereviewDetail->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($this->sitereviewDetail->getTitle(), 19), array('target' => '_blank', 'title' => $this->sitereviewDetail->getTitle())) ?>
	</div>
	<table class="clr">
		<tr>
			<td width="200"><b><?php echo $this->translate('Title :'); ?></b></td>
			<td><?php echo $this->translate($this->sitereviewDetail->title); ?>&nbsp;&nbsp;</td>
			<tr >
				<td><b><?php echo $this->translate(' 	Owner :'); ?></b></td>
				<td><?php echo  $this->translate($this->sitereviewDetail->getOwner()->getTitle());?></td>
			</tr>
      
      <?php if ($this->sitereviewDetail->category_id) : ?>
      <tr>
        
          <?php $category_id = $this->sitereviewDetail->category_id; ?>
          <?php $category = Engine_Api::_()->getItem('sitereview_category', $category_id); ?>
          <?php $categoryName = $category->category_name; ?>
          <?php $categorySlug = $category->getCategorySlug() ?>
          <?php $listingtype_id =  $this->sitereviewDetail->listingtype_id; ?>
          <td><b><?php echo $this->translate('Category:'); ?></b></td> 
          <td>
            <?php echo $this->htmlLink($this->url(array('category_id' => $category_id, 'categoryname' => $categorySlug), 'sitereview_general_category_listtype_'.$listingtype_id), $this->translate($categoryName), array('target' => '_blank')) ?>
          </td>	    
        
      </tr>	
      <?php if ($this->sitereviewDetail->subcategory_id) : ?>
      <tr>
        
          <?php $subcategory_id = $this->sitereviewDetail->subcategory_id; ?>
          <?php $subcategory = Engine_Api::_()->getItem('sitereview_category', $subcategory_id); ?>
          <?php $subcategoryName = $subcategory->category_name; ?>
          <?php $subcategorySlug = $subcategory->getCategorySlug() ?>
          <td><b><?php echo $this->translate('Subcategory:'); ?></b></td> 
          <td>
            <?php echo $this->htmlLink($this->url(array('category_id' => $category_id, 'categoryname' => $categorySlug, 'subcategory_id' => $subcategory_id, 'subcategoryname' => $subcategorySlug), 'sitereview_general_subcategory_listtype_'.$listingtype_id), $this->translate($subcategoryName), array('target' => '_blank')) ?>
          </td>	    
        
      </tr>
      <tr>
        <?php if ($this->sitereviewDetail->subsubcategory_id) : ?>
          <?php $subsubcategory_id = $this->sitereviewDetail->subsubcategory_id; ?>
          <?php $subsubcategory = Engine_Api::_()->getItem('sitereview_category', $subsubcategory_id); ?>
          <?php $subsubCategoryName = $subsubcategory->category_name; ?>
          <?php $subsubcategorySlug = $subsubcategory->getCategorySlug() ?>
          <td><b><?php echo $this->translate('3%s Level Category:', "<sup>rd</sup>"); ?></b></td>
          <td>
            <?php echo $this->htmlLink($this->url(array('category_id' => $category_id, 'categoryname' => $categorySlug, 'subcategory_id' => $subcategory_id, 'subcategoryname' => $subcategorySlug, 'subsubcategory_id' => $subsubcategory_id, 'subsubcategoryname' => $subsubcategorySlug), 'sitereview_general_subsubcategory_listtype_'.$listingtype_id), $this->translate($subsubCategoryName), array('target' => '_blank')) ?>
          </td>
        <?php endif; ?>
      </tr>    
      <?php endif; ?>
      <?php endif; ?>
		
			<tr>
				<td><b><?php echo $this->translate('Featured :'); ?></b></td>
				<td>
					<?php if ($this->sitereviewDetail->featured)
						echo $this->translate('Yes');
						else
						echo $this->translate("No") ;?>
				</td>
			</tr>

			<tr>
				<td><b><?php echo $this->translate('Sponsored :'); ?></b></td>
				<td> <?php if ($this->sitereviewDetail->sponsored)
						echo $this->translate('Yes');
						else
						echo $this->translate("No") ;?>
				</td>
			</tr>

			<tr>
				<td><b><?php echo $this->translate('Creation Date :'); ?></b></td>
				<td>
				<?php echo $this->translate(gmdate('M d,Y, g:i A',strtotime($this->sitereviewDetail->creation_date))); ?>
				</td>
			</tr>
      
			<tr>
				<td><b><?php echo $this->translate('Last Modified Date :'); ?></b></td>
				<td>
				<?php echo $this->translate(gmdate('M d,Y, g:i A',strtotime($this->sitereviewDetail->modified_date))); ?>
				</td>
			</tr>      

			<tr>
				<td><b><?php echo $this->translate('Approved :'); ?></b></td>
				<td>
					<?php  if ($this->sitereviewDetail->approved)
									echo $this->translate('Yes');
								else
									echo $this->translate("No") ;?>
				</td>
			</tr>

			<tr>
				<td><b><?php echo $this->translate('Approved Date :'); ?></b></td>
				<td>
					<?php if(!empty($this->sitereviewDetail->approved_date)): ?>
					<?php echo $this->translate(date('M d,Y, g:i A',strtotime($this->sitereviewDetail->approved_date))); ?>
					<?php else:?>
					<?php echo $this->translate('-'); ?>
					<?php endif;?>
				</td>
			</tr>

      <?php if ($this->sitereviewDetail->price > 0): ?>
				<tr>
					<td><b><?php echo $this->translate('Price :'); ?></b></td>
					<td><?php echo $this->sitereviewDetail->price ?></td>
				</tr>
			<?php endif; ?>
        
      <tr>
        <td><b><?php echo $this->translate('Added in number of Wishlists:'); ?></b></td>
        <td><?php echo Engine_Api::_()->getDbTable('wishlistmaps', 'sitereview')->getWishlistsListingCount($this->sitereviewDetail->listing_id) ?></td>
      </tr>     

			<?php if ($this->sitereviewDetail->location): ?>
				<tr>
					<td><b><?php echo $this->translate('Location :'); ?></b></td>
					<td><?php echo $this->sitereviewDetail->location ?></td>
				</tr>
			<?php endif; ?>
		
			<tr>
				<td><b><?php echo $this->translate('Views :'); ?></b></td>
				<td><?php echo $this->translate($this->sitereviewDetail->view_count ) ;?> </td>
			</tr>

			<tr>
				<td><b><?php echo $this->translate('Comments :'); ?></b></td>
				<td><?php echo $this->translate($this->sitereviewDetail->comment_count ) ;?> </td>
			</tr>

			<tr>
				<td><b><?php echo $this->translate('Likes :'); ?></b></td>
				<td><?php echo $this->translate($this->sitereviewDetail->like_count ) ;?> </td>
			</tr>

			<tr>
				<td><b><?php echo $this->translate('Reviews :'); ?></b></td>
				<td><?php echo $this->sitereviewDetail->review_count ;?> </td>
			</tr>
      <tr>           
				<td><b><?php echo $this->translate('Average Rating :'); ?></b></td>
				<td>
          <?php if($this->sitereviewDetail->rating_avg > 0):?>
            <?php echo $this->showRatingStar($this->sitereviewDetail->rating_avg, 'user', 'small-star', $this->sitereviewDetail->listingtype_id); ?>
          <?php else: ?>
          ---
          <?php endif; ?>
				</td>
			</tr>     
			<tr>           
				<td><b><?php echo $this->translate('Editor Rating :'); ?></b></td>
				<td>
          <?php if($this->sitereviewDetail->rating_editor > 0):?>
            <?php echo $this->showRatingStar($this->sitereviewDetail->rating_editor, 'editor', 'small-star', $this->sitereviewDetail->listingtype_id); ?>
          <?php else: ?>
          ---
          <?php endif; ?>
				</td>
			</tr>
      
      <tr>           
				<td><b><?php echo $this->translate('User Rating :'); ?></b></td>
				<td>
          <?php if($this->sitereviewDetail->rating_users > 0):?>
            <?php echo $this->showRatingStar($this->sitereviewDetail->rating_users, 'user', 'small-star', $this->sitereviewDetail->listingtype_id); ?>
          <?php else: ?>
          ---
          <?php endif; ?>
				</td>
			</tr>
      <?php if ($expirySettings == 2):
        $exp = $this->sitereviewDetail->getExpiryTime(); ?>
        <tr>           
          <td><b><?php echo $this->translate('Expiry Date :'); ?></b></td>
          <td>
            <?php if ($exp): ?>
              <?php echo date('M d,Y, g:i A',$exp); ?>
            <?php else: ?>
              ---
            <?php endif; ?>
          </td>
        </tr>
      <?php endif; ?> 
        <?php if ($expirySettings == 1): ?>
        <tr>           
          <td><b><?php echo $this->translate('End Date :'); ?></b></td>
          <td>
            <?php if ($this->sitereviewDetail->end_date && $this->sitereviewDetail->end_date !='0000-00-00 00:00:00'): ?>
              <?php echo date('M d,Y, g:i A',strtotime($this->sitereviewDetail->end_date)); ?>
            <?php else: ?>
              ---
            <?php endif; ?>
          </td>
        </tr>
      <?php endif; ?> 

		</table>
	<br />
	<button  onclick='javascript:parent.Smoothbox.close()' ><?php echo $this->translate('Close')  ?></button>
</div>

<?php if (@$this->closeSmoothbox): ?>
	<script type="text/javascript">
		TB_close();
	</script>
<?php endif; ?>