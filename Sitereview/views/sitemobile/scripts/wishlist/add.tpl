<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: add.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>
<?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite', 0)): ?>
    <?php if($this->success == 1): ?>
      <div class="sr_wishlist_popup_list">
        <div class='sr_wishlist_popup_item'>
          <?php echo $this->htmlLink($this->sitereview->getHref(array('profile_link' => 1)), $this->itemPhoto($this->sitereview, 'thumb.normal'), array('target' => '_blank')); ?>
        </div>
        <div class="tip sr_wishlist_popup_item_detail">
          <div class="sr_wishlist_popup_item_title">		
            <?php echo $this->htmlLink($this->sitereview->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($this->sitereview->getTitle(), 99), array('class' =>'sr_wishlist_popup_item_title', 'target' => '_blank', 'title' => $this->sitereview->getTitle())) ?>
          </div>
          <?php if(Count($this->wishlistNewDatas)): ?>
            <b><?php echo $this->translate("You have added this entry to the wishlists:"); ?></b>
            <ul class="clr">
              <?php foreach($this->wishlistNewDatas as $wishlistNewData): ?>
                <li><?php echo $this->htmlLink($wishlistNewData->getHref(),$wishlistNewData->getTitle(), array('target' => '_blank')) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
          <?php if(Count($this->wishlistOldDatas)): ?>
            <b><?php echo $this->translate("You have removed this entry from the wishlists:"); ?></b>
            <ul class="clr">
              <?php foreach($this->wishlistOldDatas as $wishlistOldData): ?>
              <li><?php echo $this->htmlLink($wishlistOldData->getHref(),$wishlistOldData->getTitle(), array('target' => '_blank')) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>     
        <div class="clr mtop10 fleft widthfull">
          <table width="100%">
            <tr>
              <td align="left">
               <a href="<?php echo $this->sitereview->getHref();?>"  data-role="button">
                  <?php echo $this->translate('Close') ?>
               </a>
              </td>
              <td class="sr_wishlist_popup_item_detail_more" align="right">
                <?php echo $this->htmlLink(array('route' => "sitereview_wishlist_general", 'action' => 'browse'), $this->translate('Browse Wishlists &raquo;'), array('target' => '_blank')) ?>
              </td>
            </tr>
          </table>
        </div>
      </div>
    <?php else: ?>
      <?php if(empty($this->can_add)):?>
        <div class="global_form_popup">	
          <div class="tip">
            <span>
              <?php echo $this->translate("Oops! Something went wrong and you can not add this $this->listing_singular_uc to your wishlist. Please try again after sometime."); ?>
            </span>
          </div>
           <a href="<?php echo $this->sitereview->getHref();?>" data-role="button">
             <?php echo $this->translate('Close') ?>
           </a>
        </div>
        <?php return; ?>
      <?php endif;?> 
      <div class='sr_wishlist_popup'>
        <?php echo $this->form->render($this) ?>
      </div>  
    <?php endif; ?>    
<?php endif; ?>

