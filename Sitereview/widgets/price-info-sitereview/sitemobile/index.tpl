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

<div class="sm-content-list ui-list-manage-page ui-list-store">
	<ul data-role="listview" data-inset="false" data-icon="arrow-r">
		<?php foreach ($this->priceInfos as $priceInfo): ?>
			<li>
				<?php $url=$this->url(array('action'=>'redirect','id'=>$this->sitereview->getIdentity()),'sitereview_priceinfo_listtype_'.$this->sitereview->listingtype_id,true).'?url='.@base64_encode($priceInfo->url);?>
				<?php
					$imgSrc = null;
					if ($priceInfo->photo_id):
						$file = Engine_Api::_()->getItemTable('storage_file')->getFile($priceInfo->photo_id);
						if ($file):
							$imgSrc = $file->map();
						endif;
					endif;
				?>
				<a href="<?php echo $url; ?>" target="_blank">
					<?php if ($imgSrc): ?>
						<img src='<?php echo $imgSrc ?>' alt="" align="center" />
					<?php else: ?>
						<?php echo $priceInfo->wheretobuy_id == 1 ? $priceInfo->title : $priceInfo->wheretobuy_title; ?>
					<?php endif; ?>
					<?php if($priceInfo->price > 0):?>
						<p class="ui-li-aside"><b><?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($priceInfo->price);  ?></b></p>
<!--						<?php if ($this->min_price > 0 && $this->min_price == $priceInfo->price): ?>
							<p class="ui-li-aside sr_price_red_tag" title="<?php echo $this->translate("Lowest Price") ?>"></p>
						<?php endif; ?>-->
					<?php endif;?>
					<?php if ($priceInfo->wheretobuy_id == 1): ?>
						<?php if ($priceInfo->address): ?><p><?php echo $priceInfo->address; ?></p><?php endif; ?>
						<?php if ($priceInfo->contact): ?><p><?php echo $priceInfo->contact ?></p><?php endif; ?>
					<?php endif; ?>
				</a>
			</li>
		<?php endforeach;?>
	</ul><br />
   <?php if($this->show_price): ?>
    <div class="clr seaocore_txt_light btm_note"><?php echo $this->translate('* The above cost (if any) for the %s is estimated and may slightly vary after including the taxes, manufacturer rebate, shipping cost, or any other sales / promotion on '.$this->listing_singular_lc.' Stores.', $this->sitereview->getTitle()) ?>
    </div>
  <?php endif; ?>
</div>