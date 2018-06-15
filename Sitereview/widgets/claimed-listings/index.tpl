<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2010-2011 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 2011-05-05 9:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>

<script type="text/javascript">
  var pageAction =function(page){
    $('page').value = page;
    $('filter_form').submit();
  }
</script>

<div class='layout_middle'>

	<h3 class="sitereview_mypage_head mbot10"><?php echo $this->translate("$this->listing_plural_uc I Have Claimed"); ?></h3>
 
  <?php if ($this->paginator->getTotalItemCount() > 0): ?>
	  <ul class="seaocore_browse_list">
	     <?php foreach ($this->paginator as $item): ?>
        <?php $listingItem = Engine_Api::_()->getItem('sitereview_listing', $item->listing_id);?>
        <li>
          <div class='seaocore_browse_list_photo'>
            <?php echo $this->htmlLink($listingItem->getHref(), $this->itemPhoto($listingItem, 'thumb.normal')) ?> 
          </div>
          <?php if( $item->status == 3 && $this->viewer_id == $item->user_id) :?>
            <div class='seaocore_browse_list_options'>
              <a href='<?php echo $this->url(array('action' => 'delete', 'claim_id' => $item->claim_id), 'sitereview_claim_listtype_'.$this->listingtype_id, true) ?>' class='buttonlink smoothbox seaocore_icon_delete'><?php echo $this->translate('Delete Claim Request'); ?></a>
           </div>	
          <?php endif; ?> 
          <div class='seaocore_browse_list_info'>
            <div class='seaocore_browse_list_info_title'>
              <h3> 
               <?php echo $this->htmlLink($listingItem->getHref(), $listingItem->getTitle()) ?>
              </h3>	
            </div>
            <div class='seaocore_browse_list_info_date'>
             <?php echo $this->translate("Status: ") ?>
             <b>
              <?php switch($item->status):
                case '1':
                    echo $this->translate("Approved");
                    break;
                  case '3':
                    echo $this->translate("Pending");
                    break;
                  case '4':
                    echo $this->translate("Hold");
                    break;
                endswitch; ?>
              </b>
            </div>
            <div class='seaocore_browse_list_info_date'>
              <?php echo $this->translate("Claim Date: ") ?><?php echo $this->timestamp(strtotime($item->creation_date)) ?>
            </div>
            <div class='seaocore_browse_list_info_date'>
              <?php echo $this->translate("Last Action Taken: ") ?><?php echo $this->timestamp(strtotime($item->modified_date)) ?>
            </div>
            <div class='seaocore_browse_list_info_date'>
              <?php echo $this->translate("Name Specified: ") ?><?php echo $item->nickname; ?>
            </div>
            <?php if(!empty($item->contactno)): ?>
              <div class='seaocore_browse_list_info_date'>
               <?php echo $this->translate("Contact Number Specified: "); ?><?php echo $item->contactno; ?>
              </div>
            <?php endif; ?>
            <div class='seaocore_browse_list_info_date'>
              <?php echo $this->translate("Email Specified: ") ?><?php echo $item->email; ?>
            </div>
            <?php if(!empty($item->usercomments)): ?>
              <div class='seaocore_browse_list_info_date'>
                <?php echo $this->translate("About You and Your $this->listing_singular_lc: ") ?><?php echo $item->about; ?>
              </div>
            <?php endif; ?>
            <?php if(!empty($item->usercomments)): ?>
             <div class="seaocore_browse_list_info_blurb">
              <?php echo $this->translate("Your Comments: ") ?><?php echo $item->usercomments; ?>
             </div>	
            <?php endif; ?>
            <?php if(!empty($item->comments)): ?>
              <div class="sitereview_claim_comment">
                <?php echo $this->translate("Admin Comment: ") ?><?php echo $item->comments; ?>
              </div>
            <?php endif; ?>
          </div>	
        </li>
	     <?php endforeach; ?>
	   </ul>
  <?php else: ?>
    <div class="tip mtop10">
      <span> <?php echo $this->translate("You have not claimed for any $this->listing_plural_lc yet."); ?></span>
    </div>
	 <?php endif; ?>
	 <?php echo $this->paginationControl($this->paginator, null, array("pagination/pagination.tpl", "sitereview")); ?>
</div>