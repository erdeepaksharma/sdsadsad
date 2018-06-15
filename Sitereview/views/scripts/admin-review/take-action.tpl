<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: take-action.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<div class='sr_popup_form global_form_popup'>
  <div class="settings">
    <form class="global_form" method="POST">
      <div>
      	<div>
	        <?php if ($this->review->status == 1): ?>
	          <h3><?php echo $this->translate("Details"); ?></h3>
	          <p><?php echo $this->translate("Below are the details of the review request that was approved.") ?></p>
	        <?php elseif ($this->review->status == 2): ?>
	          <h3><?php echo $this->translate("Details"); ?></h3>
	          <p><?php echo $this->translate("Below are the details of the review request that was declined.") ?></p>
	        <?php else: ?>	
	          <h3><?php echo $this->translate("Take Action"); ?></h3>
	          <p><?php echo $this->translate("Here, you can approve / dis-approve and view details of the visitor review. (Note: An email will be sent to the visitor about your action on this review.)") ?></p>
        	<?php endif; ?>
        	<div class="form-elements">
		        <div class="form-wrapper">
		          <div class="form-label">
		            <label><?php echo $this->translate("Reviewer:") ?></label>
		          </div>
		          <div class="form-element">
		            <?php echo $this->review->anonymous_name; ?>
		          </div>
		        </div>
		        <div class="form-wrapper">
		          <div class="form-label">
		            <label><?php echo $this->translate("Email:") ?></label>
		          </div>
		          <div class="form-element">
		            <?php echo $this->review->anonymous_email; ?>
		          </div>
		        </div>
		        <div class="form-wrapper">
		          <div class="form-label">
		            <label><?php echo $this->translate("Review Date:") ?></label>
		          </div>
		          <div class="form-element">
		            <?php echo $this->review->creation_date; ?>
		          </div>
		        </div>		
		        <div class="form-wrapper">
		          <div class="form-label">
		            <label><?php echo $this->translate("Review Title:") ?></label>
		          </div>
		          <div class="form-element">
		            <?php echo $this->htmlLink($this->review->getHref(), $this->review->title, array('title' => $this->review->title, 'target' => '_blank')) ?>
		          </div>
		        </div>
		        <?php if (!empty($this->review->body)): ?>
		          <div class="form-wrapper">
		            <div class="form-label">
		              <label><?php echo $this->translate("Listing:") ?></label>
		            </div>
		            <div class="form-element">
		              <?php echo $this->htmlLink($this->sitereview->getHref(),$this->sitereview->getTitle(), array('target' => '_blank')) ?>
		            </div>
		          </div>
		        <?php endif; ?>		
		        <div class="form-wrapper">
		          <div class="form-label">
		            <label><?php echo $this->translate("Status:") ?> </label>
		          </div>
		          <div class="form-element">
		            <?php if ($this->review->status == 1) : ?>
		              <?php echo $this->translate("Approved") ?>
		            <?php elseif ($this->review->status == 2) : ?>
		              <?php echo $this->translate("Declined") ?>
		            <?php else: ?>
		              <select name="status">
		                <option value="1" <?php if ($this->review->status == 1): ?><?php echo "selected"; ?><?php endif; ?>><?php echo $this->translate("Approved") ?></option>
		                <option value="2" <?php if ($this->review->status == 2): ?><?php echo "selected"; ?><?php endif; ?>><?php echo $this->translate("Declined") ?></option>
		              </select>
		            <?php endif; ?>
		          </div>
		        </div>
		        <div class="form-wrapper">
		          <div class="form-label">
		            <label>&nbsp;</label>
		          </div>
		          <div class="form-element">
		            <?php if ($this->review->status == 1) : ?>
		              <button onclick='javascript:parent.Smoothbox.close()'><?php echo $this->translate("Close") ?></button>
		            <?php elseif ($this->review->status == 2) : ?>
		              <button onclick='javascript:parent.Smoothbox.close()'><?php echo $this->translate("Close") ?></button>
		            <?php else: ?>
		              <button type='submit'><?php echo $this->translate('Save'); ?></button>
		              <?php echo $this->translate(" or ") ?> 
		              <a href='javascript:void(0);' onclick='javascript:parent.Smoothbox.close()'><?php echo $this->translate("cancel") ?></a>
		            <?php endif; ?>
		          </div>
		        </div>
		      </div>
		    </div>    
      </div>
    </form>
  </div>
</div>