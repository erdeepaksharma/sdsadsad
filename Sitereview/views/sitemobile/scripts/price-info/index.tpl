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
  <?php echo $this->partial('application/modules/Sitereview/views/sitemobile/scripts/dashboard/header.tpl', array('sitereview' => $this->sitereview)); ?>
    <div id="priceinfo_content" class="dashboard-content" >
<?php endif; ?>
      
<?php if (Count($this->priceInfos) > 0): ?>
     <p>
      <?php echo $this->translate('DASHBOARD_'.$this->listing_singular_upper.'_WHERE_TO_BUY options of %s', $this->sitereview->getTitle()) ?>
    </p>
      
    <?php echo $this->translate('%1$sAdd More%2$s', '<a href="' . $this->url(array('action' => 'add', 'id' => $this->sitereview->listing_id), 'sitereview_priceinfo_listtype_' . $this->sitereview->listingtype_id) . '" data-role="button" data-icon="plus">', '</a>'); ?>

    <div class="dashboard-content-table">
      <table cellpadding="0" cellspacing="0">
      <?php foreach ($this->priceInfos as $priceInfo): ?>
        <tr class="<?php echo $this->cycle(array("even", "odd"))->next()?>" valign="middle">
          <td class="b_medium">	
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
            <a href="<?php echo $priceInfoUrl; ?>" target="_blank" title="<?php echo $priceInfo->wheretobuy_id == 1 ? $priceInfo->title : $priceInfo->wheretobuy_title; ?>">

              <?php if ($imgSrc): ?>
                <img src='<?php echo $imgSrc ?>' alt="" align="middle" class="pro-img" />
              <?php else: ?>
                <?php echo $priceInfo->wheretobuy_id == 1 ? $priceInfo->title : $priceInfo->wheretobuy_title; ?>
              <?php endif; ?>
            </a>
          </td>

          <td class="b_medium">
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

          <td class="b_medium">
            <?php if($this->show_price && $priceInfo->price > 0):?>
            <a href="<?php echo $priceInfoUrl; ?>" target="_blank" >
              <?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($priceInfo->price); ?>
            </a>
            <?php endif;?>
          </td>

          <td class="b_medium" style="text-align: right;" >
            <a href="<?php echo $priceInfoUrl; ?>" target="_blank" >
              <?php echo $this->translate("See It") ?>
            </a> |
            <a href='<?php echo $this->url(array('action' => 'edit', 'id' => $priceInfo->priceinfo_id), "sitereview_priceinfo_listtype_$this->listingtype_id", true) ?>'><?php echo $this->translate('Edit'); ?></a> |
            <a href='<?php echo $this->url(array('action' => 'delete', 'id' => $priceInfo->priceinfo_id), "sitereview_priceinfo_listtype_$this->listingtype_id", true) ?>'><?php echo $this->translate('Delete'); ?></a>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>

<?php else: ?>
  <div class="sr_review_block sr_db_price_info_wrapper">
    <div class="tip" style="margin:0px;">
      <span style="margin:0px;"> 
        <?php echo $this->translate('There are currently no DASHBOARD_'.$this->listing_singular_upper.'_WHERE_TO_BUY option for this '.$this->listing_singular_lc.'. Click %s to add options now!', '<a href="' . $this->url(array('action' => 'add', 'id' => $this->sitereview->listing_id), 'sitereview_priceinfo_listtype_' . $this->sitereview->listingtype_id) . '">here</a>');?>
      </span>
    </div>
  </div>
<?php endif; ?>

<?php if ($this->includeDiv): ?>
  </div>
<?php endif; ?>
