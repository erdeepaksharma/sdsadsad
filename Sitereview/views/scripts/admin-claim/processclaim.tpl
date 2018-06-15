<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: processclaim.tpl 6590 2014-05-19 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<script type="text/javascript">
  var currentOrder = '<?php echo $this->order ?>';
  var currentOrderDirection = '<?php echo $this->order_direction ?>';
  var changeOrder = function(order, default_direction)
  {  
    if( order == currentOrder ) { 
      $('order_direction').value = ( currentOrderDirection == 'ASC' ? 'DESC' : 'ASC' );
    } 
    else { 
      $('order').value = order;
      $('order_direction').value = default_direction;
    }
    $('filter_form').submit();
  }
  
  function selectAll(){
    var i;
    var multidelete_form = $('multidelete_form');
    var inputs = multidelete_form.elements;

    for (i = 1; i < inputs.length - 1; i++) {
     if (!inputs[i].disabled) {
       inputs[i].checked = inputs[0].checked;
     }
    }
	 }
  
  function multiDelete(){
    return confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to delete selected listing ?")) ?>');
  }
  
  var changeListingType =function(listingtype_id){
    window.location.href= en4.core.baseUrl+'admin/sitereview/claim/processclaim/listingtype_id/'+listingtype_id;
  }
</script>

<h2>
  <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) { echo 'Reviews & Ratings - Multiple Listing Types Plugin'; } else { echo 'Reviews & Ratings Plugin'; }?>
</h2>

<?php if( count($this->navigation) ): ?>
  <div class='seaocore_admin_tabs clr'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
  </div>
<?php endif; ?>

<h3><?php echo 'Manage Claims for Listings'; ?></h3>
<p><?php echo 'Whenever someone makes a claim for a listing, that claim comes to you (admin) for review. Below, you can configure the settings for listing claims and manage the claims made for listings.'; ?></p><br />

<div class='tabs'>
  <ul class="navigation">
    <li>
      <?php echo $this->htmlLink(array('route'=>'admin_default','module'=>'sitereview','controller'=>'claim','action'=>'index'), 'Claimable Listing Creators', array())
     ?>
    </li>
    <li class="active">
      <?php echo $this->htmlLink(array('route'=>'admin_default','module'=>'sitereview','controller'=>'claim','action'=>'processclaim'), 'Listing Claims', array())
     ?>
    </li>
  </ul>
</div>

<div class='admin_search'>
  <?php echo $this->formFilter->render($this) ?>
</div>

<div class='clear'>
	<h3><?php echo "Claims received for Listings"; ?> </h3>
	<p>
		<?php echo 'Below you can see all the claims filed by users for Listings on your site. These claims are awaiting your action. You can approve a claim, or decline it, or put it on hold. To view details about a claim and to take an action on it, click on the "take action" link for it.'; ?>
	</p><br />		
	
	<?php $listingTypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypes(); ?>
	 <?php if(Count($listingTypes) > 1): ?>
    <?php $startArray = array();?>
    <?php $startArray[0]['title_plural'] = 'All Types';?>
    <?php $startArray[0]['listingtype_id'] = '0';?>
    <?php $listingTypes = array_merge($startArray, $listingTypes->toArray());?>
    <div id="listingtype" class="fleft">
      <label class="fleft">
      <b><?php echo "Listing Type:"; ?></b>
     </label>
     <select onchange="changeListingType($(this).value)" class="sitereview_cat_select" name="listingtype_id">            
      <?php foreach ($listingTypes as $listingType): ?>
       <option value="<?php echo $listingType['listingtype_id'];?>" <?php if( $this->listingtype_id == $listingType['listingtype_id']) echo "selected";?>><?php echo $listingType['title_plural'];?>
       </option>
      <?php endforeach; ?>
     </select>
    </div><br />     
	 <?php endif; ?>   
	
  <?php 
  	if( !empty($this->paginator) ) {
  		$counter=$this->paginator->getTotalItemCount(); 
  	}
  	if(!empty($counter)): 
  
  ?>
  	<div class="admin_search mtop10 fleft">
      <div>
         <?php echo $this->translate(array('%s claim found.', '%s claims found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
      </div>
    </div>
		<br />
  <form id='multidelete_form' method="post" action="<?php echo $this->url(array('action' => 'multi-delete-request'));?>" onSubmit="return multiDelete()">
	 <table class='admin_table seaocore_admin_table mtop10 fleft' width="100%">
	   <thead>
	     <tr>				
	       	<th><input onclick="selectAll()" type='checkbox' class='checkbox'></th>
           <?php $class = ( $this->order == 'listing_id' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
					   <th align="center" class="<?php echo $class ?>"><a href="javascript:void(0);" onclick="javascript:changeOrder('listing_id', 'DESC');"><?php echo 'Listing ID'; ?></a></th>
        <?php $class = ( $this->order == 'engine4_sitereview_listings.title' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
        <th align="left" class="<?php echo $class ?>"><a href="javascript:void(0);" onclick="javascript:changeOrder('engine4_sitereview_listings.title', 'DESC');"><?php echo 'Listing Title'; ?></a></th>
        <?php $class = ( $this->order == 'engine4_users.displayname' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
        <th align="left" class="<?php echo $class ?>"><a href="javascript:void(0);" title="<?php echo 'Claimer Display Name'; ?>" onclick="javascript:changeOrder('engine4_users.displayname', 'DESC');"><?php echo 'Display Name'; ?></a></th>
        <?php $class = ( $this->order == 'engine4_sitereview_claims.user_id' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
        <th align="center" class="<?php echo $class ?>"><a href="javascript:void(0);" onclick="javascript:changeOrder('engine4_sitereview_claims.user_id', 'DESC');"><?php echo 'Member Id'; ?></a></th>
				    <?php $class = ( $this->order == 'engine4_sitereview_claims.nickname' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
        <th align="left" class="<?php echo $class ?>"><a href="javascript:void(0);" title="<?php echo 'Claimer Name'; ?>" onclick="javascript:changeOrder('engine4_sitereview_claims.nickname', 'DESC');"><?php echo 'Claimer Name'; ?></a></th>
        <th align="left"><?php echo 'About'; ?></th>
				    <?php $class = ( $this->order == 'engine4_sitereview_claims.email' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
        <th align="left" class="<?php echo $class ?>"><a href="javascript:void(0);" onclick="javascript:changeOrder('engine4_sitereview_claims.email', 'DESC');"><?php echo 'Email'; ?></a></th>
					   <?php $class = ( $this->order == 'engine4_sitereview_claims.contactno' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
        <th align="left" class="<?php echo $class ?>"><a title="<?php echo 'Contact Number'; ?>" href="javascript:void(0);" onclick="javascript:changeOrder('engine4_sitereview_claims.contactno', 'DESC');"><?php echo 'Contact No.'; ?></a></th>
				    <?php $class = ( $this->order == 'creation_date' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
        <th class="admin_table_centered <?php echo $class ?>"><a href="javascript:void(0);" onclick="javascript:changeOrder('creation_date', 'DESC');" title="<?php echo 'Claimed Date'; ?>"><?php echo 'Creation Date'; ?></a></th>
				   <?php $class = ( $this->order == 'engine4_sitereview_claims.status' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
        <th align="center" class="<?php echo $class ?>"><a href="javascript:void(0);" onclick="javascript:changeOrder('engine4_sitereview_claims.status', 'DESC');"><?php echo 'Status'; ?></a></th>
					<th align="left"><?php echo 'Options'; ?></th>
				</tr>	
			</thead>								
			<tbody>
				<?php foreach ($this->paginator as $item): ?>
					<tr> 
					  <td class="pleft5"><input name='delete_<?php echo $item->claim_id;?>' type='checkbox' class='checkbox' value="<?php echo $item->claim_id ?>"/></td>	
						<td class='admin_table_centered admin-txt-normal'><?php echo $item->listing_id;?>	</td>
			      <td class="admin-txt-normal" title="<?php echo $item->title ?>">
							<a href="<?php echo $this->url(array('listing_id' => $item->listing_id, 'slug' => $item->getSlug()), "sitereview_entry_view_listtype_$item->listingtype_id") ?>"  target='_blank'>
								<?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($item->getTitle(),10) ?></a>
			      </td>
						<td title="<?php echo $this->item('user', $item->user_id)->getTitle()?>">
			        <?php
			          $display_name = $this->item('user', $item->user_id)->getTitle();
			          $display_name = Engine_Api::_()->seaocore()->seaocoreTruncateText($display_name,16);
			          echo $this->htmlLink($this->item('user', $item->user_id)->getHref(), $display_name, array('target' => '_blank'))
			        ?>
			      </td>			
						<td class="admin_table_centered admin-txt-normal"><?php echo $item->user_id;?>	</td>
						<td title="<?php echo $item->nickname;?>"><?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($item->nickname,15) ?></td>
						<td class="admin-txt-normal" title="<?php echo $item->about;?>"><?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($item->about,20) ?></td>
						<td class="admin-txt-normal" title="<?php echo $item->email;?>"><?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($item->email, 16);?>	</td>
						<?php if(!empty($item->contactno)):?>
						  <td class="admin-txt-normal"><?php echo $item->contactno;?>	</td>	
						<?php else : ?>
							<td class="admin_table_centered" ><?php echo "-" ?>	</td>	
						<?php endif;?>
						<td align="center" class="admin_table_centered"><?php echo gmdate('M d,Y',strtotime($item->creation_date)) ?></td>							
						<?php if($item->status == 1 ):?>
							<?php $status = 'Approved';?>
						<?php elseif($item->status == 2 ):?>
							<?php $status = 'Declined';?>
						<?php elseif($item->status == 3 ):?>
							<?php $status = 'Pending';?>
						<?php elseif($item->status == 4 ):?>
							<?php $status = 'Hold';?>
						<?php endif;?>
						<td class="admin_table_centered"><?php echo $status;?>	</td>									
						<td>	
						<?php if($item->status == 1 || $item->status == 2): ?>
						<?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sitereview', 'controller' => 'admin-claim', 'action' => 'take-action', 'claim_id'=> $item->claim_id,'listing_id' => $item->listing_id), 'details', array(
			      'class' => 'smoothbox',
			    )) ?> |
			      <?php else :?>
			      <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sitereview', 'controller' => 'admin-claim', 'action' => 'take-action', 'claim_id'=> $item->claim_id,'listing_id' => $item->listing_id), 'take action', array(
			      'class' => 'smoothbox',
			    )) ?> |
			       <?php endif;?>
					  <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sitereview', 'controller' => 'admin-claim', 'action' => 'request-delete', 'claim_id' => $item->claim_id), 'delete', array('class' => 'smoothbox')) ?> 	              
						</td>
					</tr>
			  <?php  endforeach; ?>
		  </tbody>			
		</table>
  <div class='buttons'>
			<button type='submit'><?php echo $this->translate('Delete Selected'); ?></button>
		</div>
  </form>
	<?php else:?>         
		<div class="tip mtop10 fleft">
			<span><?php  echo "No listings have been claimed yet."; ?></span> 
		</div>  		
	<?php endif;?>
	<?php  if( !empty($counter)):?>
			<?php echo $this->paginationControl($this->paginator); ?>
	<?php endif;?>
</div>