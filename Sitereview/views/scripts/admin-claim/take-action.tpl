<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: take-action.tpl 6590 2014-05-19 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<div class="global_form_popup sitereview_claim_action_popup">
	 <div class="settings">
	   <form class="global_form" method="POST">
	     <div>
	       <?php
        $listingTypeId = $this->sitereview->listingtype_id;
        $title = $this->sitereview->title;
        $url = $this->url(array('listing_id' => $this->listing_id), "sitereview_entry_view_listtype_$listingTypeId");
        $sitereview_title = "<a href='$url' target='_blank'>$title</a>";?>

	       <?php if ($this->claiminfo->status == 1): ?>
	         <h3><?php echo "Details"; ?></h3>
	         <p><?php echo "Below are the details of the claim request that was approved."; ?></p>
	       <?php elseif ($this->claiminfo->status == 2): ?>
	         <h3><?php echo "Details"; ?></h3>
	         <p><?php echo "Below are the details of the claim request that was declined."; ?></p>
	       <?php else: ?>	
	         <h3><?php echo "Take an Action"; ?></h3>
	         <p><?php echo "Please take an appropriate action on the claim for this listing: "; ?><?php echo $sitereview_title; ?></p>
	         <p><?php echo "Once you save this form, an email will be sent to this claimer stating the action taken by you."; ?></p><br />
	       <?php endif; ?>
	       <div class="form-wrapper">
	         <div class="form-label">
	           <label><?php echo "Member Id:"; ?></label>
	         </div>
	         <div class="form-element">
	           <?php echo $this->claiminfo->user_id; ?>
	         </div>
	       </div>
	       <div class="form-wrapper">
	         <div class="form-label">
	           <label><?php echo "Claimer Name:"; ?></label>
	         </div>
	         <div class="form-element">
	           <?php echo $this->claiminfo->nickname; ?>
	         </div>
	       </div>
	       <div class="form-wrapper">
	         <div class="form-label">
	           <label><?php echo "Email:"; ?></label>
	         </div>
	         <div class="form-element">
	           <?php echo $this->claiminfo->email; ?>
	         </div>
	       </div>
	       <div class="form-wrapper">
	         <div class="form-label">
	           <label><?php echo "Claimed Date:"; ?></label>
	         </div>
	         <div class="form-element">
	           <?php echo $this->claiminfo->creation_date; ?>
	         </div>
	       </div>		
	       <div class="form-wrapper">
	         <div class="form-label">
	           <label><?php echo "Last Action Taken:"; ?></label>
	         </div>
	         <div class="form-element">
	           <?php echo $this->claiminfo->modified_date; ?>
	         </div>
	       </div>
	       <?php if (!empty($this->claiminfo->contactno)): ?>			
	         <div class="form-wrapper">
	           <div class="form-label">
	             <label><?php echo "Contact Number:"; ?></label>
	           </div>
	           <div class="form-element">
	             <?php echo $this->claiminfo->contactno; ?>
	           </div>
	         </div>
	       <?php endif; ?>
	       <div class="form-wrapper">
	         <div class="form-label">
	           <label><?php echo "About Claimer and Listing:"; ?></label>
	         </div>
	         <div class="form-element">
	           <?php echo $this->claiminfo->about; ?>
	         </div>
	       </div>
	       <?php if (!empty($this->claiminfo->usercomments)): ?>
	         <div class="form-wrapper">
	           <div class="form-label">
	             <label><?php echo "User Comments:"; ?></label>
	           </div>
	           <div class="form-element">
	             <?php echo $this->claiminfo->usercomments; ?>
	           </div>
	         </div>
	       <?php endif; ?>		
	       <div class="form-wrapper">
	         <div class="form-label">
	           <label><?php echo "Status:"; ?> </label>
	         </div>
	         <div class="form-element">
	           <?php if ($this->claiminfo->status == 1) : ?>
	             <?php echo "Approved"; ?>
	           <?php elseif ($this->claiminfo->status == 2) : ?>
	             <?php echo "Declined"; ?>
	           <?php else: ?>
	             <select name="status">
	               <option value="1" <?php if ($this->claiminfo->status == 1): ?><?php echo "selected"; ?><?php endif; ?>><?php echo "Approved"; ?></option>
	               <option value="2" <?php if ($this->claiminfo->status == 2): ?><?php echo "selected"; ?><?php endif; ?>><?php echo "Declined"; ?></option>
	               <option value="4" <?php if ($this->claiminfo->status == 4): ?><?php echo "selected"; ?><?php endif; ?>><?php echo "Hold"; ?></option>
	             </select>
	           <?php endif; ?>
	         </div>
	       </div>
	       <div class="form-wrapper">
	         <div class="form-label">
	           <label><?php echo "Admin's Comments:"; ?> </label>
	         </div>
	         <div class="form-element">
	           <?php if ($this->claiminfo->status == 1 || $this->claiminfo->status == 2) : ?>
	             <?php if (!empty($this->claiminfo->comments)): ?>
	               <?php echo $this->claiminfo->comments; ?>
	             <?php else: ?>
	               <?php echo '---'; ?>
	             <?php endif; ?>
	           <?php elseif ($this->claiminfo->status == 3 || $this->claiminfo->status == 4): ?>		
	             <textarea name="comments"><?php echo $this->claiminfo->comments; ?></textarea>					
	           <?php endif; ?>
	         </div>
	       </div>
	       <div class="form-wrapper">
	         <div class="form-label">
	           <label>&nbsp;</label>
	         </div>
	         <div class="form-element">
	           <?php if ($this->claiminfo->status == 1) : ?>
	             <button onclick='javascript:parent.Smoothbox.close()'><?php echo "Close"; ?></button>
	           <?php elseif ($this->claiminfo->status == 2) : ?>
	             <button onclick='javascript:parent.Smoothbox.close()'><?php echo "Close"; ?></button>
	           <?php else: ?>
	             <button type='submit'><?php echo 'Save'; ?></button>
	             <?php echo " or "; ?> 
	             <a href='javascript:void(0);' onclick='javascript:parent.Smoothbox.close()'><?php echo "cancel"; ?></a>
	           <?php endif; ?>
	         </div>
	       </div>
	     </div>
	   </form>
	 </div>
</div>