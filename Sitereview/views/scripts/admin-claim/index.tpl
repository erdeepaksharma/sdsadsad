<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2014-05-19 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>

<script type="text/javascript">
  var currentOrder = '<?php echo $this->order ?>';
  var currentOrderDirection = '<?php echo $this->order_direction ?>';
  var changeOrder = function(order, default_direction) {  
    if( order == currentOrder ) 
    $('order_direction').value = ( currentOrderDirection == 'ASC' ? 'DESC' : 'ASC' );
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
    return confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to delete selected Claimable listing creators?")) ?>');
  }

  var changeListingType =function(listingtype_id){
    window.location.href= en4.core.baseUrl+'admin/sitereview/claim/index/listingtype_id/'+listingtype_id;
  }
</script>
  
<div class='admin_search'>
  <?php echo $this->formFilter->render($this) ?>
</div>

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
      <li class="active">
        <?php echo $this->htmlLink(array('route'=>'admin_default','module'=>'sitereview','controller'=>'claim','action'=>'index'), 'Claimable Listing Creators', array())
        ?>
      </li>
      <li>
        <?php
          echo $this->htmlLink(array('route'=>'admin_default','module'=>'sitereview','controller'=>'claim','action'=>'processclaim'), 'Listing Claims', array())
        ?>
      </li>			
    </ul>
  </div>
  <div class='clear'>
    <h3><?php echo "Claimable Listing Creators"; ?> </h3>
    <p class="description">
     <?php echo 'Though using the "Claim a Listing" link a user can make a claim for any listing, the listings created by users listed below get the "Claim this Listing" link on the listing itself. This would be useful in cases like if you have certain members whose job is to create only those listings on your site which could later be easily claimed by their rightful owners. Below, you can also add and manage such listing creators. (Note that a listing can be claimed by a member only if his member level has the permission to do so from the member level settings.)'; ?>
    </p>

    <?php $listingTypes = $this->listingTypes; ?>
    <?php if(Count($listingTypes) > 1): ?>
      <?php $startArray = array();?>
      <?php $startArray[0]['title_plural'] = 'All Types';?>
      <?php $startArray[0]['listingtype_id'] = '0';?>
      <?php $listingTypes = array_merge($startArray, $listingTypes->toArray());?>
      <div id="listingtype">
        <label class="fleft">
          <b><?php echo "Listing Type:"; ?></b>
        </label>
        <select onchange="changeListingType($(this).value)" class="sitereview_cat_select" name="listingtype_id">            
          <?php foreach ($listingTypes as $listingType): ?>
            <?php $listinTypesArray[$listingType['listingtype_id']] = $listingType['title_plural']; ?>
            <option value="<?php echo $listingType['listingtype_id'];?>" <?php if( $this->listingtype_id == $listingType['listingtype_id']) echo "selected";?>><?php echo $listingType['title_plural'];?>
            </option>
          <?php endforeach; ?>
        </select>
      </div><br />     
    <?php endif; ?>   
    <?php if(!empty($this->listingtype_id)):?>
      <div class="clr mtop10 fleft"><?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'claim', 'action' => 'listclaimmember', 'listingtype_id' => $this->listingtype_id), 'Add Member', array(
       'class' => 'smoothbox buttonlink seaocore_icon_add'
       )) ?>	</div>
     <br/>	<br/>
    <?php endif;?>
    <?php if( !empty($this->paginator) ):?>
      <?php $counter=$this->paginator->getTotalItemCount(); ?>
    <?php endif;?>
    <?php if(!empty($counter)):?>
       <form id='multidelete_form' method="post" action="<?php echo $this->url(array('action' => 'multi-delete-claimable-member'));?>" onSubmit="return multiDelete()">
      <table class='admin_table' width="80%">
        <thead>
          <tr>
            <th style='width: 1%;'><input onclick="selectAll()" type='checkbox' class='checkbox'></th>
        
            <?php $class = ( $this->order == 'engine4_sitereview_listmemberclaims.user_id' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
            <th width="70" align="left" class="<?php echo $class ?>"><a href="javascript:void(0);" onclick="javascript:changeOrder('engine4_sitereview_listmemberclaims.user_id', 'DESC');"><?php echo "Member Id"; ?></a></th>
            <?php $class = ( $this->order == 'engine4_users.displayname' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
            <th width="70" align="left" class="<?php echo $class ?>"><a href="javascript:void(0);" onclick="javascript:changeOrder('engine4_users.displayname', 'DESC');"><?php echo "Display Name"; ?></a></th>
            <?php $class = ( $this->order == 'engine4_users.username' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
            <th width="70" align="left" class="<?php echo $class ?>"><a href="javascript:void(0);" onclick="javascript:changeOrder('engine4_users.username', 'DESC');"><?php echo "Username"; ?></a></th>
            <th width="70" align="left"><?php echo "Option"; ?></th>
          </tr>
        </thead>					
        <tbody>
          <?php foreach ($this->paginator as $item): ?>
            <tr>
              <td><input name='delete_<?php echo $item->listmemberclaim_id ?>' type='checkbox' class='checkbox' value="<?php echo $item->listmemberclaim_id ?>"/></td>
              <td class='admin_table_bold admin-txt-normal'><?php echo $item->user_id;?></td>								
              <td class='admin_table_user'><?php echo $this->htmlLink($this->item('user', $item->user_id)->getHref(), $this->item('user', $item->user_id)->username, array('target' => '_blank')) ?></td>								
              <td class='admin_table_user'><?php echo $this->htmlLink($this->item('user', $item->user_id)->getHref(), $this->item('user', $item->user_id)->displayname, array('target' => '_blank')) ?></td>		
              <td align="left">
                <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'claim', 'action' => 'delete-claimable-member', 'user_id'=> $item->user_id), 'remove', array(
             'class' => 'smoothbox',)) ?>
              </td>
            </tr>
          <?php  endforeach; ?>							
        </tbody>
     </table>
      <div class='buttons'>
        <button type='submit'><?php echo $this->translate('Delete Selected'); ?></button>
      </div>
       </form>
     <?php echo $this->paginationControl($this->paginator); ?>
    <?php else:?>
      <div class="tip mtop10 fleft">
        <span><?php  echo "No such member has been found whose listings can be claimed easily."; ?></span> 	                   
      </div>   				
    <?php endif;?>				
   </div>