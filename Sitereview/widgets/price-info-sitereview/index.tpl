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
<?php if($this->loaded_by_ajax):?>
  <div id="tmp_sitereview_price_info_<?php echo $this->identity ?>"></div>
  
  <script type="text/javascript">
      
    $("tmp_sitereview_price_info_<?php echo $this->identity ?>").getParent('.layout_sitereview_price_info_sitereview').addClass('layout_sitereview_price_info_sitereview<?php echo $this->identity ?>');  
    var params = {
      requestParams :<?php echo json_encode($this->params) ?>,
      responseContainer :$$('.layout_sitereview_price_info_sitereview<?php echo $this->identity ?>')
    }
    en4.sitereview.ajaxTab.attachEvent('<?php echo $this->identity ?>',params);
  </script>
<?php endif;?>
  
<?php if($this->showContent): ?>
    <div class="sr_price_info_block <?php if (!empty($this->layout_column)): ?>sr_side_widget<?php endif;?>">
      <div <?php if (empty($this->layout_column)): ?> class="sr_review_block"<?php endif;?>>
        <table>
          <?php foreach ($this->priceInfos as $priceInfo): ?>
            <?php $url=$this->url(array('action'=>'redirect','id'=>$this->sitereview->getIdentity()),'sitereview_priceinfo_listtype_'.$this->sitereview->listingtype_id,true).'?url='.@base64_encode($priceInfo->url);?>
            <tr class="<?php echo $this->cycle(array("even", "odd"))->next()?>" valign="middle">
              <td class="sr_price_info_image">	
                <?php
                $imgSrc = null;
                if ($priceInfo->photo_id):
                  $file = Engine_Api::_()->getItemTable('storage_file')->getFile($priceInfo->photo_id);
                  if ($file):
                    $imgSrc = $file->map();
                  endif;
                endif;
                ?>
                <a href="<?php echo $url; ?>" target="_blank" title="<?php echo $priceInfo->wheretobuy_id == 1 ? $priceInfo->title : $priceInfo->wheretobuy_title; ?>" class="b_medium">
                  <?php if ($imgSrc): ?>
                    <img src='<?php echo $imgSrc ?>' alt="" align="center" />
                  <?php else: ?>
                    <?php echo $priceInfo->wheretobuy_id == 1 ? $priceInfo->title : $priceInfo->wheretobuy_title; ?>
                  <?php endif; ?>
                </a>
                <?php if ($this->min_price > 0 && $this->min_price == $priceInfo->price): ?>
                  <span class="sr_price_red_tag" title="<?php echo $this->translate("Lowest Price") ?>"></span>
                <?php endif; ?>
              </td>

              <?php if (empty($this->layout_column)): ?>
                <td class="sr_price_contact_info">
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
              <?php endif; ?>
              <?php if($this->show_price):?>
              <td class="sr_price_info_value">
                <?php if($priceInfo->price > 0):?>
                <a href="<?php echo $url; ?>" target="_blank" >
                  <?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($priceInfo->price);  ?>
                </a>
                <?php endif;?>
              </td>
              <?php endif;?>
              <?php if (empty($this->layout_column)): ?>
                <td class="sr_price_info_view_button">
                  <a href="<?php echo $url; ?>" target="_blank" class="price_see_it_button">
                    <?php echo $this->translate("See It") ?>
                  </a>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </table>  
      </div>  
      <?php if ($this->layout_column): ?>
        <?php if(!empty($this->tab_id)):?>
        <div id="view_all_price_link" class="sr_more_link" style="display: block;">
          <a href="<?php echo $this->sitereview->getHref(array('profile_link' => 0)). '/tab/' . $this->tab_id?>">
            <?php echo $this->translate('VIEW_ALL_'.$this->listing_singular_UPPER.'_STORES') ?>
          </a>
        </div>
        <?php else:?>
          <div id="view_all_price_link" class="sr_more_link" style="display: none;">
            <a href="javascript:void(0);" onclick="viewAllPrice()"><?php echo $this->translate('VIEW_ALL_'.$this->listing_singular_UPPER.'_STORES') ?></a>
          </div>
        <?php endif;?>
        <script type="text/javascript">
          en4.core.runonce.add(function(){
            if($('main_tabs') && $('main_tabs').getElement('.tab_layout_sitereview_price_info_sitereview')){
              $('view_all_price_link').style.display='block';
            }
          });
          function viewAllPrice(){

			if($('main_tabs')) {
				tabContainerSwitch($('main_tabs').getElement('.tab_' + '<?php echo $this->contentDetails->content_id ?>'));
			}
			
			var params = {
				requestParams :<?php echo json_encode($this->contentDetails->params) ?>,
				responseContainer :$$('.layout_sitereview_price_info_sitereview<?php echo $this->contentDetails->content_id ?>')
			}
		
			params.requestParams.content_id = '<?php echo $this->contentDetails->content_id ?>';
			en4.sitereview.ajaxTab.sendReq(params);
			
			if($('main_tabs')) {
				location.hash = 'main_tabs';
			}
          }
        </script>

      <?php elseif($this->show_price): ?>
        <div class="clr seaocore_txt_light btm_note"><?php echo $this->translate('* The above cost (if any) for the %s is estimated and may slightly vary after including the taxes, manufacturer rebate, shipping cost, or any other sales / promotion on '.$this->listing_singular_lc.' Stores.', $this->sitereview->getTitle()) ?>
        </div>
      <?php endif; ?>
    </div>
<?php endif; ?>  
