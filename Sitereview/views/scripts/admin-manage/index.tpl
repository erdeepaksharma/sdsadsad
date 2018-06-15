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
$reviewApi = Engine_Api::_()->sitereview();
?>
<script type="text/javascript">
  var currentOrder = '<?php echo $this->order ?>';
  var currentOrderDirection = '<?php echo $this->order_direction ?>';
  var changeOrder = function(order, default_direction){  

    if( order == currentOrder ) { 
      $('order_direction').value = ( currentOrderDirection == 'ASC' ? 'DESC' : 'ASC' );
    } else { 
      $('order').value = order;
      $('order_direction').value = default_direction;
    }
    $('filter_form').submit();
  }

	function multiDelete()
	{
		return confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to delete selected listings ?")) ?>');
	}

	function selectAll()
	{
	  var i;
	  var multidelete_form = $('multidelete_form');
	  var inputs = multidelete_form.elements;

	  for (i = 1; i < inputs.length - 1; i++) {
	    if (!inputs[i].disabled) {
	      inputs[i].checked = inputs[0].checked;
    	}
  	}
	}
</script>

<h2>
  <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) { echo $this->translate('Reviews & Ratings - Multiple Listing Types Plugin'); } else { echo $this->translate('Reviews & Ratings Plugin'); }?>
</h2>

<?php if( count($this->navigation) ): ?>
  <div class='seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
  </div>
<?php endif; ?>

<h2><?php echo $this->translate('Manage Listings'); ?></h2>
<h4><?php echo $this->translate('This page lists all the listings your users have posted. You can use this page to monitor these listings and delete offensive material if necessary. Entering criteria into the filter fields will help you find specific listing entries. Leaving the filter fields blank will show all the listing entries on your social network. Here, you can also make listings featured / un-featured, sponsored / un-sponsored, new / remove from new, and approve / dis-approve them.');?></h4><br />

<div class="admin_search sr_admin_listing_search">
  <div class="search">
    <form method="post" class="global_form_box" action="" width="100%">
      
      <div>
	      <label>
	      	<?php echo  $this->translate("Title") ?>
	      </label>
	      <?php if( empty($this->title)):?>
	      	<input type="text" name="title" /> 
	      <?php else: ?>
	      	<input type="text" name="title" value="<?php echo $this->translate($this->title)?>"/>
	      <?php endif;?>
      </div>

      <div>
      	<label>
      		<?php echo  $this->translate("Owner") ?>
      	</label>	
      	<?php if( empty($this->owner)):?>
      		<input type="text" name="owner" /> 
      	<?php else: ?> 
      		<input type="text" name="owner" value="<?php echo $this->translate($this->owner)?>" />
      	<?php endif;?>
      </div>
      
      <?php $listingTypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypes(); ?>
      <?php if(Count($listingTypes) > 1): ?>
      <div id="listingtype">
          <label>
            <?php echo  $this->translate("Listing Type") ?>
          </label>
          <select id="listingtype_id" class="sitereview_cat_select" name="listingtype_id" onchange='addOptions(this.value, "listingtype_id", "category_id", 0);'>
            <option value="0"><?php echo  $this->translate("All Types") ?></option>            
            <?php $listinTypesArray[0] = "All Types"; ?>
            <?php foreach ($listingTypes as $listingType): ?>
              <?php $listinTypesArray[$listingType->listingtype_id] = $listingType->title_plural; ?>
              <option value="<?php echo $listingType->listingtype_id;?>" <?php if( $this->listingtype_id == $listingType->listingtype_id) echo "selected";?>><?php echo $this->translate($listingType->title_plural);?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php else: ?>
        <div>
          <input type="hidden" name="listingtype_id" value="1"/>
        </div>      
      <?php endif; ?>

      <div class="form-wrapper" id="category_id-wrapper" style='display:none;'>
        <div class="form-label" id="category_id-label">
          <label class="optional" for="category_id"><?php echo $this->translate('Category'); ?></label>
        </div>
        <div class="form-element" id="category_id-element">
          <select id="category_id" name="category_id" onchange='addOptions(this.value, "cat_dependency", "subcategory_id", 0);'></select>
        </div>
      </div>

      <div class="form-wrapper" id="subcategory_id-wrapper" style='display:none;'>
        <div class="form-label" id="subcategory_id-label">
          <label class="optional" for="subcategory_id"><?php echo $this->translate('Sub-Category'); ?></label>
        </div>
        <div class="form-element" id="subcategory_id-element">
          <select id="subcategory_id" name="subcategory_id" onchange='addOptions(this.value, "subcat_dependency", "subsubcategory_id", 0);'></select>
        </div>
      </div>

      <div class="form-wrapper" id="subsubcategory_id-wrapper" style='display:none;'>
        <div class="form-label" id="subsubcategory_id-label">
          <label class="optional" for="subsubcategory_id"><?php echo $this->translate('3rd Level Category', "<sup>rd</sup>") ?></label>
        </div>
        <div class="form-element" id="subsubcategory_id-element">
          <select id="subsubcategory_id" name="subsubcategory_id"  ></select>
        </div>
      </div>
      
      <div class="clr">
	    	<label>
	      	<?php echo  $this->translate("Listings Having") ?>	
	      </label>
        <select id="" name="review_status">
          <option value="0" ><?php echo $this->translate("Select") ?></option>
          <option value="rating_editor" <?php if( $this->review_status == 'rating_editor') echo "selected";?> ><?php echo $this->translate("Editor Reviews") ?></option>
          <option value="rating_users" <?php if( $this->review_status == 'rating_users') echo "selected";?> ><?php echo $this->translate("User Reviews") ?></option>
          <option value="rating_avg" <?php if( $this->review_status == 'rating_avg') echo "selected";?> ><?php echo $this->translate("Editor or User Reviews") ?></option> 
          <option value="both" <?php if( $this->review_status == 'both') echo "selected";?> ><?php echo $this->translate("Editor and User Reviews") ?></option> 
         </select>
      </div>      
      <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting') && !empty($this->package)):?>
				<div>
					<label>
						<?php echo  $this->translate("Package") ?>
					</label>
					<select id="package_id" name="package_id">
						<option value="0" ></option>
						<?php foreach ( $this->packageList as $package): ?>
						<option value="<?php echo $package->package_id ?>" <?php if( $this->package_id == $package->package_id) echo "selected";?> > <?php echo ucfirst($package->title) ?></option>
					<?php  endforeach; ?>
					</select>
				</div>
      <?php endif; ?>
      <div>
	    	<label>
	      	<?php echo  $this->translate("Featured") ?>	
	      </label>
        <select id="" name="featured">
          <option value="0" ><?php echo $this->translate("Select") ?></option>
          <option value="2" <?php if( $this->featured == 2) echo "selected";?> ><?php echo $this->translate("Yes") ?></option>
          <option value="1" <?php if( $this->featured == 1) echo "selected";?> ><?php echo $this->translate("No") ?></option>
         </select>
      </div>

      <div>
	    	<label>
	      	<?php echo  $this->translate("Sponsored") ?>	
	      </label>
        <select id="sponsored" name="sponsored">
            <option value="0"  ><?php echo $this->translate("Select") ?></option>
          <option value="2" <?php if( $this->sponsored == 2) echo "selected";?> ><?php echo $this->translate("Yes") ?></option>
          <option value="1"  <?php if( $this->sponsored == 1) echo "selected";?>><?php echo $this->translate("No") ?></option>
         </select>
      </div>    
     
      <div>
	    	<label>
	      	<?php echo  $this->translate("New") ?>	
	      </label>
        <select id="newlabel" name="newlabel">
            <option value="0"  ><?php echo $this->translate("Select") ?></option>
          <option value="2" <?php if( $this->newlabel == 2) echo "selected";?> ><?php echo $this->translate("Yes") ?></option>
          <option value="1"  <?php if( $this->newlabel == 1) echo "selected";?>><?php echo $this->translate("No") ?></option>
         </select>
      </div>    

      <div>
	    	<label>
	      	<?php echo  $this->translate("Approved") ?>	
	      </label>
        <select id="sponsored" name="approved">
            <option value="0" ><?php echo $this->translate("Select") ?></option>
          <option value="2" <?php if( $this->approved == 2) echo "selected";?> ><?php echo $this->translate("Yes") ?></option>
          <option value="1" <?php if( $this->approved == 1) echo "selected";?> ><?php echo $this->translate("No") ?></option>
         </select>
      </div>
      
      <div>
	    	<label>
	      	<?php echo  $this->translate("Status") ?>	
	      </label>
        <select id="" name="status">
          <option value="0" ><?php echo $this->translate("Select") ?></option>
          <option value="1" <?php if( $this->status == 1) echo "selected";?> ><?php echo $this->translate("Only Open Listings") ?></option>
          <option value="2" <?php if( $this->status == 2) echo "selected";?> ><?php echo $this->translate("Only Close Listings") ?></option>
         </select>
      </div>

      <div class="browseby">
	    	<label>
	      	<?php echo  $this->translate("Browse By") ?>	
	      </label>
        <select id="" name="listingbrowse">
          <option value="0" ><?php echo $this->translate("Select") ?></option>
          <option value="1" <?php if( $this->listingbrowse == 1) echo "selected";?> ><?php echo $this->translate("Most Viewed") ?></option>
          <option value="2" <?php if( $this->listingbrowse == 2) echo "selected";?> ><?php echo $this->translate("Most Recent") ?></option>
        </select>
      </div>
              
      <div class="clear mtop10">
        <button type="submit" name="search" ><?php echo $this->translate("Search") ?></button>
      </div>
    </form>
  </div>
</div>
<br />

<div class='admin_search'>
  <?php echo $this->formFilter->render($this) ?>
</div>

<div class='admin_members_results'>
  <?php $counter = $this->paginator->getTotalItemCount();?>
	<?php if(!empty($counter)): ?>
		<div class="">
			<?php  echo $this->translate(array('%s listing found.', '%s listings found.', $counter), $this->locale()->toNumber($counter)) ?>
		</div>
  <?php else:?>
		<div class="tip"><span>
			<?php  echo $this->translate("No results were found.") ?></span>
		</div>
  <?php endif; ?>
  <?php  echo $this->paginationControl($this->paginator); ?>
</div>
<br />

<?php if( $this->paginator->getTotalItemCount() > 0):?>
	<form id='multidelete_form' method="post" action="<?php echo $this->url(array('action' => 'multi-delete'));?>" onSubmit="return multiDelete()">
    <div class="managelist_table_scroll">
		<table class='admin_table' width="100%">
    
			<thead>
				<tr>
					<th><input onclick="selectAll()" type='checkbox' class='checkbox'></th>
          
          <?php $class = ( $this->order == 'listing_id' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
					<th class="<?php echo $class ?>" ><a href="javascript:void(0);" onclick="javascript:changeOrder('listing_id', 'DESC');"><?php echo $this->translate('ID'); ?></a></th>
          
          <?php $class = ( $this->order == 'title' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
					<th class="<?php echo $class ?>"  align="left"><a href="javascript:void(0);" onclick="javascript:changeOrder('title', 'ASC');"><?php echo $this->translate('Title'); ?></a></th>
          
          <?php $class = ( $this->order == 'username' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
					<th class="<?php echo $class ?>"  align="left" ><a href="javascript:void(0);" onclick="javascript:changeOrder('username', 'ASC');"><?php echo $this->translate('Owner');?></a></th>
          
          <?php $class = ( $this->order == 'view_count' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
					<th class="<?php echo $class ?> admin_table_centered"><a href="javascript:void(0);" onclick="javascript:changeOrder('view_count', 'DESC');"><?php echo $this->translate('Views'); ?></a></th>
          
          <?php $class = ( $this->order == 'comment_count' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
					<th class="<?php echo $class ?> admin_table_centered"><a href="javascript:void(0);" onclick="javascript:changeOrder('comment_count', 'DESC');"><?php echo $this->translate('Comments'); ?></a></th>
          
          <?php $class = ( $this->order == 'like_count' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
					<th class="<?php echo $class ?> admin_table_centered"><a href="javascript:void(0);" onclick="javascript:changeOrder('like_count', 'DESC');"><?php echo $this->translate('Likes'); ?></a></th>
					
					<?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting')):?>
            <th align="left"  title="<?php echo $this->translate('Package'); ?>" ><?php echo $this->translate('Package')  ?></th>
						<th align="left"> <?php echo $this->translate('Status'); ?> </th>
						<th align="left" title="<?php echo $this->translate('Payment'); ?>"><?php echo $this->translate('Payment')  ?></th>
					<?php endif; ?>
          
          <?php $class = ( $this->order == 'review_count' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
          <th class="<?php echo $class ?> admin_table_centered"><a href="javascript:void(0);" onclick="javascript:changeOrder('review_count', 'DESC');"><?php echo $this->translate('Reviews'); ?></a></th>

          <th align="center" class="admin_table_centered" title="<?php echo $this->translate("Expiry / End Date of listings") ?>" ><?php echo $this->translate('Ex / En'); ?>
            </th>
         
<?php $class = ( $this->order == 'featured' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
          <th class="<?php echo $class ?> admin_table_centered"  title="<?php echo $this->translate('Featured'); ?>"><a href="javascript:void(0);" onclick="javascript:changeOrder('featured', 'ASC');"><?php echo $this->translate('F'); ?></a></th>
          
          <?php $class = ( $this->order == 'sponsored' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
          <th class="<?php echo $class ?> admin_table_centered" title="<?php echo $this->translate('Sponsored'); ?>"><a href="javascript:void(0);" onclick="javascript:changeOrder('sponsored', 'DESC');"><?php echo $this->translate('S'); ?></a></th>
  <?php $class = ( $this->order == 'newlabel' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
          <th class="<?php echo $class ?> admin_table_centered"  title="<?php echo $this->translate('New'); ?>"><a href="javascript:void(0);" onclick="javascript:changeOrder('newlabel', 'ASC');"><?php echo $this->translate('N'); ?></a></th>

          <?php $class = ( $this->order == 'approved' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
					<th class="<?php echo $class ?> admin_table_centered" title="<?php echo $this->translate('Approved'); ?>"><a href="javascript:void(0);" onclick="javascript:changeOrder('approved', 'ASC');"><?php echo $this->translate('A'); ?></a></th>          
          <?php $class = ( $this->order == 'closed' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
					<th class="<?php echo $class ?> admin_table_centered" title="<?php echo $this->translate('Open/Close'); ?>"><a href="javascript:void(0);" onclick="javascript:changeOrder('closed', 'ASC');"><?php echo $this->translate('O/C'); ?></a></th>          
          <?php $class = ( $this->order == 'creation_date' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
					<th align="left" class="<?php echo $class ?>"><a href="javascript:void(0);" onclick="javascript:changeOrder('creation_date', 'DESC');"><?php echo $this->translate('Creation Date'); ?></a></th>
					
					<?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting')):?>
						<th align="left" title="<?php echo $this->translate('Package Expiration Date'); ?>"><?php echo $this->translate('Expiration Date')  ?></th>
					<?php endif;?>
                      
					<th class="<?php echo $class ?>"  class='admin_table_centered'><?php echo $this->translate('Options'); ?></th>
				</tr>
			</thead>

			<tbody>
				<?php if( count($this->paginator) ): ?>
					<?php foreach( $this->paginator as $item ): ?> 
          <?php  $expirySettings = $reviewApi->expirySettings($item->listingtype_id);
              $approveDate = null;
              if ($expirySettings == 2):
                $approveDate = $reviewApi->adminExpiryDuration($item->listingtype_id);
              endif;?>
						<tr>

							<td><input name='delete_<?php echo $item->listing_id;?>' type='checkbox' class='checkbox' value="<?php echo $item->listing_id ?>"/></td>

							<td><?php echo $item->listing_id; ?></td>

							<td class='admin_table_bold' style="white-space:normal;" title="<?php echo $this->translate($item->getTitle()) ?>">
								<a href="<?php echo $this->url(array('listing_id' => $item->listing_id, 'slug' => $item->getSlug()), "sitereview_entry_view_listtype_$item->listingtype_id") ?>"  target='_blank'>
								<?php echo $this->translate(Engine_Api::_()->seaocore()->seaocoreTruncateText($item->getTitle(),10)) ?></a>
							</td>

							<td class='admin_table_bold' title="<?php echo $item->getOwner()->getTitle() ?>"> <?php echo $this->htmlLink($item->getOwner()->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($item->getOwner()->getTitle(),10), array('target' => '_blank')) ?></td>

							<td align="center" class="admin_table_centered"><?php echo $item->view_count ?></td>
							<td align="center" class="admin_table_centered"><?php echo $item->comment_count  ?></td>
							<td align="center" class="admin_table_centered"><?php echo $item->like_count  ?></td>
							
							<?php if(Engine_Api::_()->sitereview()->hasPackageEnable($item->listingtype_id)):?>
								<td align="left">		<?php  echo $this->htmlLink(
					array('route' => 'admin_default', 'module' => 'sitereviewpaidlisting', 'controller' => 'package', 'action' => 'packge-detail', 'id' => $item->package_id), $this->translate(ucfirst(Engine_Api::_()->seaocore()->seaocoreTruncateText($item->getPackage()->title, 10))), array('class' => 'smoothbox','title'=>ucfirst($item->getPackage()->title)));  ?></td>
                <td align="left"><?php echo $item->getListingStatus(); ?></td>
                <td align="center" class="admin_table_centered">
                  <?php if(!$item->getPackage()->isFree()):  ?>
                      <?php if($item->status=="initial"):
                          echo $this->translate("No");
                      elseif($item->status=="active"):
                           echo $this->translate("Yes");
                          else:
                             echo $this->translate(ucfirst($item->status));
                            endif;
                              ?>
                  <?php else:?>
                  <?php echo $this->translate("NA (Free)"); ?>
                  <?php endif ?>
                </td>
                <?php else:?>
                  <td class="admin_table_centered"><?php echo '-';?></td>
                  <td class="admin_table_centered"><?php echo '-';?></td>
                  <td class="admin_table_centered"><?php echo '-';?></td>
                <?php endif ?>
              <td align="center" class="admin_table_centered"><?php echo $item->review_count ?></td>
              <td align="center" class="admin_table_centered" title="<?php echo ($expirySettings == 2) ? "Expiried the listing":($expirySettings == 1 ? 'End Date of listing':''); ?>"  >
                <?php if ($approveDate): ?>  
                  <?php  if(empty($item->approved_date)):?>
                    <?php echo "No Apporved"?>
                      <?php elseif ($approveDate > $item->approved_date): ?>
                      <?php  echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'general', 'action' => 'renew', 'listing_id' => $item->listing_id),"Expiried", array(
                      'class' => 'smoothbox',
                    ));?>
                      <?php else: ?>
                       <?php echo $item->getExpiryTime() ? date('M d,Y, g:i A', $item->getExpiryTime()) :'---'?>
                      <?php endif; ?>
                  <?php endif; ?>
                <?php if ($expirySettings == 1):?>
                 <?php echo (empty ($item->end_date) ||  $item->end_date =='0000-00-00 00:00:00')?$this->translate('Never'):$this->translate(gmdate('M d,Y, g:i A',strtotime($item->end_date))) ?>
                <?php endif; ?>
                <?php if ($expirySettings == 0):?>
                <?php echo $this->translate('N/A') ?>
                <?php endif; ?>
                </td> 
						  <?php if($item->featured == 1):?> 
								<td align="center" class="admin_table_centered"> <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'general', 'action' => 'featured', 'listing_id' => $item->listing_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/images/featured.gif', '', array('title' => $this->translate('Make Un-featured')))) ?></td>
              <?php else: ?>
								<td align="center" class="admin_table_centered"><?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'general', 'action' => 'featured', 'listing_id' => $item->listing_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/images/unfeatured.gif', '', array('title' => $this->translate('Make Featured')))) ?></td>
              <?php endif; ?>

							<?php if($item->sponsored == 1):?>
								<td align="center" class="admin_table_centered"> <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'general', 'action' => 'sponsored', 'listing_id' => $item->listing_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/images/sponsored.png', '', array('title' => $this->translate('Make Unsponsored')))); ?></td>
							<?php else: ?>
								<td align="center" class="admin_table_centered"> <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'general', 'action' => 'sponsored', 'listing_id' => $item->listing_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/images/unsponsored.png', '', array('title' => $this->translate('Make Sponsored')))); ?>
							<?php endif; ?>   

						  <?php if($item->newlabel == 1):?> 
								<td align="center" class="admin_table_centered"><?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'general', 'action' => 'newlabel', 'listing_id' => $item->listing_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/icons/new.png', '', array('title' => $this->translate('Remove New Label')))) ?></td>
              <?php else: ?>
								<td align="center" class="admin_table_centered"> <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'general', 'action' => 'newlabel', 'listing_id' => $item->listing_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/icons/new-disable.png', '', array('title' => $this->translate('Set New Label')))) ?></td>
              <?php endif; ?>
                   
							<?php if($item->approved == 1):?>
								<td align="center" class="admin_table_centered"> <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'general', 'action' => 'approved', 'listing_id' => $item->listing_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/images/approved.gif', '', array('title' => $this->translate('Make Dis-Approved')))) ?></td>
							<?php else: ?>
								<td align="center" class="admin_table_centered"> <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'general', 'action' => 'approved', 'listing_id' => $item->listing_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/images/disapproved.gif', '', array('title' => $this->translate('Make Approved')))) ?></td>
							<?php endif; ?>

              <?php if($item->closed == 0):?>
								<td align="center" class="admin_table_centered">  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'general', 'action' => 'open-close', 'listing_id' => $item->listing_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/unclose.png', '', array('title'=> $this->translate('Make Closed')))) ?>
							<?php else: ?>
								<td align="center" class="admin_table_centered"> <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'general', 'action' => 'open-close', 'listing_id' => $item->listing_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/close.png', '', array('title'=> $this->translate('Make Open')))) ?>
							<?php endif; ?>                
                
              <td><?php echo $this->translate(gmdate('M d,Y, g:i A',strtotime($item->creation_date))) ?></td>                 
						  <?php if(Engine_Api::_()->sitereview()->hasPackageEnable($item->listingtype_id)):?>
								<td align="left" ><?php echo $item->getExpiryDate()  ?></td>
        <?php else:?>
        <td class="admin_table_centered"><?php echo '-';?></td>
							<?php endif; ?>
							<td class='admin_table_options'>
								<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'manage', 'action' => 'detail', 'id' => $item->listing_id), $this->translate('details'), array('class' => 'smoothbox')) ?> |
								<a href="<?php echo $this->url(array('listing_id' => $item->listing_id, 'slug' => $item->getSlug()), "sitereview_entry_view_listtype_$item->listingtype_id") ?>"  target='_blank'><?php echo $this->translate('view'); ?></a> |
								<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'general', 'action' => 'change-owner', 'listing_id' => $item->listing_id), $this->translate('change owner'), array('class' => 'smoothbox')) ?>
                                
                              <?php if(Engine_Api::_()->hasModuleBootstrap('sitereviewlistingtype') && Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeCount() > 1): ?> | 
                                <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'general', 'action' => 'change-listingtype', 'listing_id' => $item->listing_id), $this->translate('change listing type'), array('class' => 'smoothbox')) ?>  
                              <?php endif; ?>  
                                
							  <?php  if (Engine_Api::_()->sitereview()->hasPackageEnable($item->listingtype_id)) : ?>
                |
								<?php echo $this->htmlLink(array('route' => "sitereview_package_listtype_$item->listingtype_id", 'action' => 'update-package', 'listing_id' => $item->listing_id), $this->translate('edit package'), array(
									'target' => '_blank',
								)) ?>   
        <?php if(Engine_Api::_()->sitereviewpaidlisting()->canAdminShowRenewLink($item->listing_id)):?> |
          <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'general', 'action' => 'renew', 'id' => $item->listing_id), $this->translate('renew'), array(
          'class' => 'smoothbox',
          )) ?>
       <?php endif; ?>
         <?php endif;?>
         |
								<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'general', 'action' => 'delete', 'listing_id' => $item->listing_id), $this->translate('delete'), array(
									'class' => 'smoothbox',
								)) ?>
							</td>
            </tr>
            
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
     
		</table>
  </div>
		<div class='buttons'>
			<button type='submit'><?php echo $this->translate('Delete Selected'); ?></button>
		</div>
	</form>
<?php endif;?>

<div id="thankYou" style="display:none;">
	<div>
		<div id="showMessage_featured" class="sr_manage_msg" style="display:none;"><?php echo $this->translate("This listing has already been marked as Featured. If you mark it as New, then its Featured marker will be automatically removed. Click on 'OK' button to mark it as New.");?></div>
		<div id="showMessage_new" class="sr_manage_msg" style="display:none;"><?php echo $this->translate("This listing has already been marked as New. If you mark it as Featured, then its New marker will be automatically removed. Click on 'OK' button to mark it as Featured.");?></div>
    <div id="hidden_url" style="display:none;" ></div>
    <br />
    <button onclick="continueSetLabel();"><?php echo $this->translate('Ok');?></button> <?php echo $this->translate('or');?>
		<a onclick="closeThankYou();" href="javascript:void(0);"> <?php echo $this->translate('cancel');?></a></div>
	</div>			
</div>

<script type="text/javascript">

  function addOptions(element_value, element_type, element_updated, domready) {

    var element = $(element_updated);
    if(domready == 0){
      switch(element_type){
        case 'listingtype_id':
          $('category_id'+'-wrapper').style.display = 'none';
          clear($('category_id'));
          $('category_id').value = 0;
    
        case 'cat_dependency':
          $('subcategory_id'+'-wrapper').style.display = 'none';
          clear($('subcategory_id'));
          $('subcategory_id').value = 0;
  
        case 'subcat_dependency':
          $('subsubcategory_id'+'-wrapper').style.display = 'none';
          clear($('subsubcategory_id'));
          $('subsubcategory_id').value = 0;
      }
    }
   
    if(element_value <= 0) return;  
   
    var url = '<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'general', 'action' => 'categories'), "admin_default", true); ?>';
    en4.core.request.send(new Request.JSON({      	
      url : url,
      data : {
        format : 'json',
        element_value : element_value,
        element_type : element_type
      },

      onSuccess : function(responseJSON) {
        var categories = responseJSON.categories;
        var option = document.createElement("OPTION");
        option.text = "";
        option.value = 0;
        element.options.add(option);
        for (i = 0; i < categories.length; i++) {
          var option = document.createElement("OPTION");
          option.text = categories[i]['category_name'];
          option.value = categories[i]['category_id'];
          element.options.add(option);
        }

        if(categories.length  > 0 )
          $(element_updated+'-wrapper').style.display = '';
        else
          $(element_updated+'-wrapper').style.display = 'none';
        
        if(domready == 1){
          var value=0;
          if(element_updated=='category_id'){
            value = search_category_id;
          }else if(element_updated=='subcategory_id'){
            value = search_subcategory_id;
          }else{
            value =search_subsubcategory_id;
          }
          $(element_updated).value = value;
        }
      }

    }),{'force':true});
  }

  function clear(element)
  { 
    for (var i = (element.options.length-1); i >= 0; i--)	{
      element.options[ i ] = null;
    }
  }
  
  var search_category_id,search_subcategory_id,search_subsubcategory_id;
  window.addEvent('domready', function() {
    
    search_category_id='<?php echo $this->category_id ? $this->category_id : 0 ?>';
    
    var listingtype_id = '<?php echo $this->listingtype_id; ?>';
    if($("listingtype_id")) {
       listingtype_id = $("listingtype_id").value;
    }
    addOptions(listingtype_id,'listingtype_id', 'category_id',1);
   
    if(search_category_id !=0) {
      search_subcategory_id='<?php echo $this->subcategory_id ? $this->subcategory_id : 0 ?>';
      
      addOptions(search_category_id,'cat_dependency', 'subcategory_id',1);
      
      if(search_subcategory_id !=0) {
        search_subsubcategory_id='<?php echo $this->subsubcategory_id ? $this->subsubcategory_id : 0 ?>';
        addOptions(search_subcategory_id,'subcat_dependency', 'subsubcategory_id',1);
      }
    }   
  });

</script>