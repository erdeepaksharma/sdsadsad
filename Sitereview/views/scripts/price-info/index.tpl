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

<?php if ($this->includeDiv): ?>
  <?php include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/_DashboardNavigation.tpl'; ?>
  <div class="sr_dashboard_content">
    <?php echo $this->partial('application/modules/Sitereview/views/scripts/dashboard/header.tpl', array('sitereview' => $this->sitereview)); ?>
    <div id="priceinfo_content" class="sr_price_info_block o_hidden" >
<?php endif; ?>
      
<?php if (Count($this->priceInfos) > 0): ?>
   <div class="sr_review_block sr_db_price_info_wrapper">
    <div class="sr_db_price_info_head">
      <div class="fright">
        <?php echo $this->translate('%1$sAdd More%2$s', '<a class="smoothbox seaocore_icon_add buttonlink" href="' . $this->url(array('action' => 'add', 'id' => $this->sitereview->listing_id), 'sitereview_priceinfo_listtype_' . $this->sitereview->listingtype_id) . '">', '</a>'); ?></div>
      <div class="sr_db_price_info_head_title">
        <?php echo $this->translate('DASHBOARD_'.$this->listing_singular_upper.'_WHERE_TO_BUY options of %s', $this->sitereview->getTitle()) ?>
      </div>
    </div>
    <div class="sr_db_price_info b_medium">
      <table>
      <?php foreach ($this->priceInfos as $priceInfo): ?>
        <tr class="<?php echo $this->cycle(array("even", "odd"))->next()?>" valign="middle">
          <td class="sr_db_price_info_image">	
            <?php
            $imgSrc = null;
            if ($priceInfo->photo_id):
              $file = Engine_Api::_()->getItemTable('storage_file')->getFile($priceInfo->photo_id);
              if ($file):
                $imgSrc = $file->map();
              endif;
            endif;
            ?>
             <?php $priceInfoUrl=$this->url(array('action'=>'redirect','id'=>$this->sitereview->getIdentity()),'sitereview_priceinfo_listtype_'.$this->sitereview->listingtype_id,true).'?url='.@base64_encode($priceInfo->url);?>
            <a href="<?php echo $priceInfoUrl; ?>" target="_blank" title="<?php echo $priceInfo->wheretobuy_id == 1 ? $priceInfo->title : $priceInfo->wheretobuy_title; ?>" class="price_info_image">

              <?php if ($imgSrc): ?>
                <img src='<?php echo $imgSrc ?>' alt="" align="middle" />
              <?php else: ?>
                <?php echo $priceInfo->wheretobuy_id == 1 ? $priceInfo->title : $priceInfo->wheretobuy_title; ?>
              <?php endif; ?>
            </a>
          </td>

          <td class="sr_db_price_contact_info">
            <?php if ($priceInfo->wheretobuy_id == 1): ?>
              <?php if ($priceInfo->address): ?>
                <span class="address o_hidden clr fleft">
                  <?php echo  $this->htmlLink('https://maps.google.com/?q=' . urlencode($priceInfo->address), '<i class="fleft"></i>', array('target' => '_blank')); ?>	
                  <span class="o_hidden"><?php echo $priceInfo->address; ?></span>
                </span>
              <?php endif; ?>
              <?php if ($priceInfo->contact): ?>
                <span class="number o_hidden clr fleft">
                  <i class="fleft"></i>
                  <span class="o_hidden"><?php echo $priceInfo->contact ?></span>
                </span>
              <?php endif; ?>
            <?php endif; ?>
          </td>

          <td class="sr_db_price_info_value">
            <?php if($this->show_price && $priceInfo->price > 0):?>
            <a href="<?php echo $priceInfoUrl; ?>" target="_blank" >


              <?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($priceInfo->price); ?>



            </a>
            <?php endif;?>
          </td>

          <td class="sr_db_price_info_options" >
            <a href="<?php echo $priceInfoUrl; ?>" target="_blank" >
              <?php echo $this->translate("See It") ?>
            </a> |
            <a href='<?php echo $this->url(array('action' => 'edit', 'id' => $priceInfo->priceinfo_id), "sitereview_priceinfo_listtype_$this->listingtype_id", true) ?>' class='smoothbox'><?php echo $this->translate('Edit'); ?></a> |
            <a href='<?php echo $this->url(array('action' => 'delete', 'id' => $priceInfo->priceinfo_id), "sitereview_priceinfo_listtype_$this->listingtype_id", true) ?>' class='smoothbox'><?php echo $this->translate('Delete'); ?></a>

          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
  </div>
<?php else: ?>
  <div class="sr_review_block sr_db_price_info_wrapper">
    <div class="tip" style="margin:0px;">
      <span style="margin:0px;"> 
        <?php echo $this->translate('There are currently no DASHBOARD_'.$this->listing_singular_upper.'_WHERE_TO_BUY option for this '.$this->listing_singular_lc.'. Click %s to add options now!', '<a class="smoothbox" href="' . $this->url(array('action' => 'add', 'id' => $this->sitereview->listing_id), 'sitereview_priceinfo_listtype_' . $this->sitereview->listingtype_id) . '">here</a>');?>
      </span>
    </div>
  </div>
<?php endif; ?>

<?php if ($this->includeDiv): ?>
  </div>
  </div>
</div>
<?php endif; ?>
