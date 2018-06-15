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

<?php

  $this->headLink()
    ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_rating.css')
		->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css')
    ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/styles/styles.css');
	$this->headScript()
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/scripts/core.js');
?>

<?php if( $this->enableLocation && $this->map_view && $this->paginator->count() > 0): ?>

  <?php
//GET API KEY
  $apiKey = Engine_Api::_()->seaocore()->getGoogleMapApiKey();
$this->headScript()->appendFile("https://maps.googleapis.com/maps/api/js?libraries=places&key=$apiKey");
  ?>

<?php endif; ?>
  
<div id="sitereview_location_map_none" style="display: none;"></div>  

<?php if($this->is_ajax_load): ?>
  <?php 
    $ratingValue = $this->ratingType; 
    $ratingShow = 'small-star';
      if ($this->ratingType == 'rating_editor') {$ratingType = 'editor';} elseif ($this->ratingType == 'rating_avg') {$ratingType = 'overall';} else { $ratingType = 'user';}
  ?>

  <?php

  $reviewApi = Engine_Api::_()->sitereview();
  $expirySettings = $reviewApi->expirySettings($this->listingtype_id);
  $approveDate = null;
  if ($expirySettings == 2):
    $approveDate = $reviewApi->adminExpiryDuration($this->listingtype_id);
  endif;

  ?> 
  <?php $latitude = $this->settings->getSetting('sitereview.map.latitude', 0); ?>
  <?php $longitude = $this->settings->getSetting('sitereview.map.longitude', 0); ?>
  <?php $defaultZoom = $this->settings->getSetting('sitereview.map.zoom', 1); ?>
  <?php $enableBouce = $this->settings->getSetting('sitereview.map.sponsored', 1); ?>

  <?php $doNotShowTopContent = 0;?>
  <?php if($this->categoryName && !empty($this->categoryObject->top_content)): ?>

    <h4 class="sr_browse_lists_view_options_head mbot10" style="display: inherit;">
      <?php echo $this->translate($this->categoryName); ?>
    </h4>

    <?php $doNotShowTopContent = 1;?>
  <?php endif; ?>

  <?php if($this->category_id && !$this->subcategory_id && !$this->subsubcategory_id):?>
    <div class="sr_browse_cat_cont clr"><?php echo Engine_Api::_()->getItem('sitereview_category', $this->category_id)->top_content;?></div>
  <?php elseif($this->subcategory_id && $this->category_id && !$this->subsubcategory_id):?>
    <div class="sr_browse_cat_cont clr"><?php echo Engine_Api::_()->getItem('sitereview_category', $this->subcategory_id)->top_content;?></div>
  <?php elseif($this->subsubcategory_id && $this->category_id && $this->subcategory_id):?>
    <div class="sr_browse_cat_cont clr"><?php echo Engine_Api::_()->getItem('sitereview_category', $this->subsubcategory_id)->top_content;?></div>
  <?php endif;?> 

  <?php if ($this->paginator->count() > 0): ?>

    <script type="text/javascript">
      var pageAction = function(page){

        var form;
        if($('filter_form')) {
          form=document.getElementById('filter_form');
          }else if($('filter_form_sitereview')){
            form=$('filter_form_sitereview');
          }
        form.elements['page'].value = page;

        form.submit();
      } 
    </script>

    <form id='filter_form_sitereview' class='global_form_box' method='get' action='<?php echo $this->url(array('action' => 'index'), "sitereview_general_listtype_$this->listingtype_id", true) ?>' style='display: none;'>
      <input type="hidden" id="page" name="page"  value=""/>
    </form>

    <?php if (($this->list_view && $this->grid_view) || ($this->map_view && $this->grid_view) || ($this->list_view && $this->map_view)): ?>
      <div class="sr_browse_lists_view_options b_medium">
        <div class="fleft"> 
          <?php if($this->categoryName && $doNotShowTopContent != 1): ?>
            <h4 class="sr_browse_lists_view_options_head">
              <?php echo $this->translate($this->categoryName); ?>
            </h4>
          <?php endif; ?>
          <?php echo $this->translate(array('%s '.strtolower($this->listingtypeArray->title_singular).' found.', '%s '.strtolower($this->listingtypeArray->title_plural).' found.', $this->totalResults),$this->locale()->toNumber($this->totalResults)) ?>
        </div>

        <?php  if( $this->enableLocation  && $this->map_view): ?> 
          <span class="seaocore_tab_select_wrapper fright">
            <div class="seaocore_tab_select_view_tooltip"><?php echo $this->translate("Map View"); ?></div>
            <span class="seaocore_tab_icon tab_icon_map_view" onclick="switchview(2);"></span>
          </span>
        <?php endif;?>
        <?php  if( $this->grid_view): ?>
          <span class="seaocore_tab_select_wrapper fright">
            <div class="seaocore_tab_select_view_tooltip"><?php echo $this->translate("Grid View"); ?></div>
            <span class="seaocore_tab_icon tab_icon_grid_view" onclick="switchview(1);"></span>
          </span>
        <?php endif;?>
        <?php  if( $this->list_view): ?>
          <span class="seaocore_tab_select_wrapper fright">
            <div class="seaocore_tab_select_view_tooltip"><?php echo $this->translate("List View"); ?></div>
             <span class="seaocore_tab_icon tab_icon_list_view" onclick="switchview(0);"></span>
          </span>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if( $this->list_view): ?>

      <div id="grid_view" <?php if($this->defaultView !=0): ?>style="display: none;" <?php endif; ?>>

        <?php if(empty($this->viewType)):?>

        <ul class="sr_browse_list">
          <?php foreach ($this->paginator as $sitereview): ?>
             <?php Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
                  $listingType = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);?>
            <?php if(!empty($sitereview->sponsored)):?>
              <li class="list_sponsered b_medium">
            <?php else: ?>
              <li class="b_medium">
            <?php endif;?>
              <div class='sr_browse_list_photo b_medium'>
                <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)):?>
                  <?php if($sitereview->featured):?>
                    <i class="sr_list_featured_label" title="<?php echo $this->translate('Featured'); ?>"></i>
                  <?php endif;?>
                  <?php if($sitereview->newlabel):?>
                    <i class="sr_list_new_label" title="<?php echo $this->translate('New'); ?>"></i>
                  <?php endif;?>
                <?php endif;?>

                <?php echo $this->htmlLink($sitereview->getHref(array('profile_link' => 1)), $this->itemPhoto($sitereview, 'thumb.normal', '', array('align' => 'center'))) ?>

                <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)):?>
                  <?php if (!empty($sitereview->sponsored)): ?>
                      <div class="sr_list_sponsored_label" style="background: <?php echo $listingType->sponsored_color; ?>">
                        <?php echo $this->translate('SPONSORED'); ?>                 
                      </div>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
                <?php if($this->listingtypeArray->reviews): ?>
                  <div class="sr_browse_list_rating">
                    <?php if(!empty($sitereview->rating_editor) && ($ratingValue == 'rating_both'|| $ratingValue == 'rating_editor')): ?>
                      <div class="clr">	
                        <div class="sr_browse_list_rating_stats">
                          <?php echo $this->translate("Editor Rating");?>
                        </div>
                       <?php $ratingData = $this->ratingTable->ratingbyCategory($sitereview->listing_id,'editor', $sitereview->getType()); ?>
                        <div class="sr_ur_show_rating_star fnone o_hidden">
                          <span class="sr_browse_list_rating_stars">
                            <span class="fleft">
                              <?php echo $this->showRatingStar($sitereview->rating_editor, 'editor', 'big-star', $sitereview->listingtype_id); ?>
                            </span>
                            <?php if (count($ratingData) > 1): ?>
                              <i class="fright arrow_btm"></i>
                          <?php endif; ?>
                          </span>

                          <?php if (count($ratingData) > 1): ?>
                            <div class="sr_ur_show_rating br_body_bg b_medium">
                              <div class="sr_profile_rating_parameters sr_ur_show_rating_box">

                                <?php foreach ($ratingData as $reviewcat): ?>

                                  <div class="o_hidden">
                                    <?php if (!empty($reviewcat['ratingparam_name'])): ?>
                                      <div class="parameter_title">
                                        <?php echo $this->translate($reviewcat['ratingparam_name']); ?>
                                      </div>
                                      <div class="parameter_value">
                                        <?php echo $this->showRatingStar($reviewcat['avg_rating'], 'editor', 'small-box', $sitereview->listingtype_id,$reviewcat['ratingparam_name']); ?>
                                      </div>
                                    <?php else: ?>
                                      <div class="parameter_title">
                                        <?php echo $this->translate("Overall Rating"); ?>
                                      </div>	
                                      <div class="parameter_value" style="margin: 0px 0px 5px;">
                                        <?php echo $this->showRatingStar($reviewcat['avg_rating'], 'editor', 'big-star', $sitereview->listingtype_id); ?>
                                      </div>
                                    <?php endif; ?> 
                                  </div>

                                <?php endforeach; ?>
                              </div>
                            </div>
                          <?php endif; ?>
                        </div>
                       </div> 
                     <?php endif; ?>
                    <?php if(!empty($sitereview->rating_users) && ($ratingValue == 'rating_both'|| $ratingValue == 'rating_users')): ?>
                      <div class="clr">
                        <div class="sr_browse_list_rating_stats">
                        <?php echo $this->translate("User Ratings");?><br />
                          <?php 
                            $totalUserReviews = $sitereview->review_count;
                            if($sitereview->rating_editor){
                              $totalUserReviews = $sitereview->review_count - 1;
                            }
                          ?>
                          <?php if($this->listingtypeArray->allow_review): ?>
														<?php echo $this->translate(array('Based on %s review', 'Based on %s reviews', $totalUserReviews), $this->locale()->toNumber($totalUserReviews)) ?>
                          <?php endif;?>
                        </div>
                         <?php $ratingData = $this->ratingTable->ratingbyCategory($sitereview->listing_id, 'user', $sitereview->getType());?>
                          <div class="sr_ur_show_rating_star fnone o_hidden">
                            <span class="sr_browse_list_rating_stars">
                             <span class="fleft">
                               <?php echo $this->showRatingStar($sitereview->rating_users, 'user', 'big-star', $sitereview->listingtype_id); ?>
                             </span>
                             <?php if (count($ratingData) > 1): ?>
                               <i class="fright arrow_btm"></i>
                           <?php endif; ?>
                           </span>

                           <?php if (count($ratingData) > 1): ?>
                             <div class="sr_ur_show_rating  br_body_bg b_medium">
                               <div class="sr_profile_rating_parameters sr_ur_show_rating_box">

                                 <?php foreach ($ratingData as $reviewcat): ?>

                                   <div class="o_hidden">
                                     <?php if (!empty($reviewcat['ratingparam_name'])): ?>
                                       <div class="parameter_title">
                                         <?php echo $this->translate($reviewcat['ratingparam_name']); ?>
                                       </div>
                                       <div class="parameter_value">
                                         <?php echo $this->showRatingStar($reviewcat['avg_rating'], 'user', 'small-box', $sitereview->listingtype_id,$reviewcat['ratingparam_name']); ?>
                                       </div>
                                     <?php else: ?>
                                       <div class="parameter_title">
                                         <?php echo $this->translate("Overall Rating"); ?>
                                       </div>	
                                       <div class="parameter_value" style="margin: 0px 0px 5px;">
                                         <?php echo $this->showRatingStar($reviewcat['avg_rating'], 'user', 'big-star', $sitereview->listingtype_id); ?>
                                       </div>
                                     <?php endif; ?> 
                                   </div>

                                 <?php endforeach; ?>
                               </div>
                             </div> 
                           <?php endif; ?> 
                         </div>
                       </div>  
                   <?php endif;?>

                    <?php if(!empty($sitereview->rating_avg) && ($ratingValue == 'rating_avg')): ?>
                      <div class="clr">
                        <?php if($this->listingtypeArray->allow_review): ?>
													<div class="sr_browse_list_rating_stats">
		<!--	                    <?php //echo $this->translate("Overall Rating");?><br />-->

														<?php echo $this->translate(array('Based on %s review', 'Based on %s reviews', $sitereview->review_count), $this->locale()->toNumber($sitereview->review_count)) ?>
													</div>
                        <?php endif;?>
                         <?php $ratingData = $this->ratingTable->ratingbyCategory($sitereview->listing_id, null, $sitereview->getType());?>
                          <div class="sr_ur_show_rating_star fnone o_hidden">
                            <span class="sr_browse_list_rating_stars">
                             <span class="fleft">
                               <?php echo $this->showRatingStar($sitereview->rating_avg, $ratingType, 'big-star', $sitereview->listingtype_id); ?>
                             </span>
                             <?php if (count($ratingData) > 1): ?>
                               <i class="fright arrow_btm"></i>
                           <?php endif; ?>
                           </span>

                           <?php if (count($ratingData) > 1): ?>
                             <div class="sr_ur_show_rating  br_body_bg b_medium">
                               <div class="sr_profile_rating_parameters sr_ur_show_rating_box">

                                 <?php foreach ($ratingData as $reviewcat): ?>

                                   <div class="o_hidden">
                                     <?php if (!empty($reviewcat['ratingparam_name'])): ?>
                                       <div class="parameter_title">
                                         <?php echo $this->translate($reviewcat['ratingparam_name']); ?>
                                       </div>
                                       <div class="parameter_value">
                                         <?php echo $this->showRatingStar($reviewcat['avg_rating'], $ratingType, 'small-box', $sitereview->listingtype_id,$reviewcat['ratingparam_name']); ?>
                                       </div>
                                     <?php else: ?>
                                       <div class="parameter_title">
                                         <?php echo $this->translate("Overall Rating"); ?>
                                       </div>	
                                       <div class="parameter_value" style="margin: 0px 0px 5px;">
                                         <?php echo $this->showRatingStar($reviewcat['avg_rating'], 'user', 'big-star', $sitereview->listingtype_id); ?>
                                       </div>
                                     <?php endif; ?> 
                                   </div>

                                 <?php endforeach; ?>
                               </div>
                             </div> 
                           <?php endif; ?> 
                         </div>
                       </div>  
                   <?php endif;?>
                  </div>
                <?php endif; ?>

              <div class='sr_browse_list_info'>  

                <div class='sr_browse_list_info_header o_hidden'>
                  <div class="sr_list_title">
                    <?php echo $this->htmlLink($sitereview->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($sitereview->getTitle(), $this->title_truncation), array('title' => $sitereview->getTitle())); ?>
                  </div>
                  <div class="clear"></div>
                </div>

                <div class='sr_browse_list_info_stat seaocore_txt_light'>
                  <?php echo $this->timestamp(strtotime($sitereview->creation_date)) ?><?php if($this->postedby): ?> - <?php echo $this->translate(strtoupper($this->listingtypeArray->title_singular). '_posted_by'); ?>
                  <?php echo $this->htmlLink($sitereview->getOwner()->getHref(), $sitereview->getOwner()->getTitle()) ?><?php endif ?><?php if(!empty($this->statistics)): ?>,

                  <?php 

                    $statistics = '';

                    if(in_array('commentCount', $this->statistics)) {
                      $statistics .= $this->translate(array('%s comment', '%s comments', $sitereview->comment_count), $this->locale()->toNumber($sitereview->comment_count)).', ';
                    }

                                         if(in_array('reviewCount', $this->statistics) && (!empty($this->listingtypeArray->allow_review) || (isset($sitereview->rating_editor) && $sitereview->rating_editor))) {
                      $statistics .= $this->partial(
                      '_showReview.tpl', 'sitereview', array('sitereview'=>$sitereview)).', ';
                    }

                    if(in_array('viewCount', $this->statistics)) {
                      $statistics .= $this->translate(array('%s view', '%s views', $sitereview->view_count), $this->locale()->toNumber($sitereview->view_count)).', ';
                    }

                    if(in_array('likeCount', $this->statistics)) {
                      $statistics .= $this->translate(array('%s like', '%s likes', $sitereview->like_count), $this->locale()->toNumber($sitereview->like_count)).', ';
                    }                 

                    $statistics = trim($statistics);
                    $statistics = rtrim($statistics, ',');

                  ?>

                  <?php echo $statistics; ?>
                  <?php endif; ?>
                </div>

                <?php if($this->showExpiry):?>
                  <?php if ($expirySettings == 2): $exp=$sitereview->getExpiryTime();
                    echo '<div class="sr_browse_list_info_stat seaocore_txt_light">' . $exp ? $this->translate("Expiry On: %s",$this->locale()->toDate($exp, array('size'=>'medium'))) :'' . '</div>';
                    $now = new DateTime(date("Y-m-d H:i:s"));
                    $ref = new DateTime($this->locale()->toDate($exp));
                    $diff = $now->diff($ref);
                    echo '<div class="sr_browse_list_info_stat seaocore_txt_light">';
                    echo $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i);
                    echo '</div>';

                  elseif ($expirySettings == 1 && $sitereview->end_date && $sitereview->end_date !='0000-00-00 00:00:00'):
                    echo '<div class="sr_browse_list_info_stat seaocore_txt_light">' . $this->translate("Ending On: %s",$this->locale()->toDate(strtotime($sitereview->end_date), array('size'=>'medium'))) . '</div>';
                        $now = new DateTime(date("Y-m-d H:i:s"));
                        $ref = new DateTime($sitereview->end_date);
                        $diff = $now->diff($ref);
                        echo '<div class="sr_browse_list_info_stat seaocore_txt_light">';
                        echo $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i);
                        echo '</div>';

                  endif;?>
                <?php endif; ?>
                <div class='sr_browse_list_info_stat seaocore_txt_light'>
                   <a href="<?php echo $this->url(array('category_id' => $sitereview->category_id, 'categoryname' => $sitereview->getCategory()->getCategorySlug()), "sitereview_general_category_listtype_" . $sitereview->listingtype_id); ?>"> 
                      <?php echo $this->translate($sitereview->getCategory()->getTitle(true)) ?>
                    </a>
                </div>

                <?php if($sitereview->price > 0 && $this->listingtypeArray->price): ?>
                  <div class='sr_browse_list_info_stat'>
                    <b><?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($sitereview->price);  ?></b>
                  </div>
                <?php endif; ?>

                <?php if(!empty($sitereview->location) && $this->listingtypeArray->location): ?>
                  <div class='sr_browse_list_info_stat seaocore_txt_light'>
                    <?php echo $this->translate($sitereview->location); ?>
                    - <b><?php echo  $this->htmlLink(array('route' => 'seaocore_viewmap', "id" => $sitereview->listing_id, 'resouce_type' => 'sitereview_listing'), $this->translate("Get Directions"), array('class' => 'smoothbox')) ; ?></b>
                  </div>
                <?php endif; ?>
                <div class='sr_browse_list_info_blurb'>
                  <?php if($this->bottomLine): ?>
                    <?php echo $this->viewMore($sitereview->getBottomLine(), 125, 5000);?>
                  <?php else: ?>
                    <?php echo $this->viewMore(strip_tags($sitereview->body), 125, 5000);?>
                  <?php endif; ?>
                </div>
                <div class="sr_browse_list_info_footer clr o_hidden">
                  <?php echo $this->compareButton($sitereview);?>
                  <?php echo $this->addToWishlist($sitereview, array('classIcon' => 'sr_wishlist_href_link', 'classLink' => ''));?>
                  <?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)) :?>   
                  <span class="sr_browse_list_info_footer_icons">
                    <?php if( $sitereview->closed ): ?>
                      <img alt="close" src='<?php echo $this->layout()->staticBaseUrl?>application/modules/Sitereview/externals/images/close.png'/>
                    <?php endif;?>  
                    <?php if ($sitereview->sponsored == 1): ?>
                      <i class="sr_icon seaocore_icon_sponsored" title="<?php echo $this->translate('Sponsored');?>"></i>
                    <?php endif; ?>
                    <?php if ($sitereview->featured == 1): ?>
                      <i class="sr_icon seaocore_icon_featured" title="<?php echo $this->translate('Featured'); ?>"></i>
                    <?php endif; ?>
                  </span>
                 <?php endif; ?>
                </div>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>

        <?php else: ?>

          <ul class="sr_browse_list">
            <?php foreach($this->paginator as $sitereview):?>
            <?php Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
            $listingType = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);?>
            <?php if(!empty($sitereview->sponsored)):?>
              <li class="list_sponsered b_medium">
            <?php else: ?>
              <li class="b_medium">
            <?php endif;?>
                <div class='sr_browse_list_photo b_medium'>
                  <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)):?>
                    <?php if($sitereview->featured):?>
                      <i class="sr_list_featured_label" title="<?php echo $this->translate('Featured'); ?>"></i>
                    <?php endif;?>
                    <?php if($sitereview->newlabel):?>
                      <i class="sr_list_new_label" title="<?php echo $this->translate('New'); ?>"></i>
                    <?php endif;?>
                  <?php endif;?>
                  <?php echo $this->htmlLink($sitereview->getHref(array('profile_link' => 1)), $this->itemPhoto($sitereview, 'thumb.normal', '', array('align' => 'center'))) ?>
                  <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)):?>
                    <?php if (!empty($sitereview->sponsored)): ?>
                        <div class="sr_list_sponsored_label" style="background: <?php echo $listingType->sponsored_color; ?>">
                          <?php echo $this->translate('SPONSORED'); ?>                 
                        </div>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>

                <?php $priceInfos = $sitereview->getPriceInfo();?>
                <?php $priceInfoCount = Count($priceInfos);?>
                <div class='sr_browse_list_info'>
                  <?php if($sitereview->allowWhereToBuy() ): ?>
                    <div class="sr_browse_list_price_info">
                      <?php if($priceInfoCount > 0):?>
                      <?php $minPrice = $sitereview->getWheretoBuyMinPrice(); $maxPrice = $sitereview->getWheretoBuyMaxPrice()?>
                        <?php if($minPrice): ?>
                        <div class="sr_price">

                          <?php if($minPrice == $maxPrice):?>
                            <?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($minPrice); ?>
                          <?php elseif($priceInfoCount == 2):?>
                            <?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($minPrice), Engine_Api::_()->sitereview()->getPriceWithCurrency($maxPrice); ?>
                          <?php else: ?>
                            <?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($minPrice); ?> - <?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($maxPrice); ?>
                          <?php endif ?>
                        </div>
                        <?php endif ?>
                        <div class="sr_browse_list_price_info_stats">
                          <?php echo $this->translate(array(strtoupper($this->listingtypeArray->title_singular). '_AT %s store', strtoupper($this->listingtypeArray->title_singular).'_AT %s stores', $priceInfoCount), $this->locale()->toNumber($priceInfoCount)) ?>
                        </div>

                        <?php $iPrice = 0;?>
                        <?php foreach($priceInfos as $priceInfo):?>
                        <?php $url=$this->url(array('action'=>'redirect','id'=>$sitereview->getIdentity()),'sitereview_priceinfo_listtype_'.$sitereview->listingtype_id,true).'?url='.@base64_encode($priceInfo->url);?>
                            <div class="sr_browse_list_price_info_stats">
                             <?php if($priceInfo->price>0):?> <?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($priceInfo->price); ?> <?php endif;?> - <a href="<?php echo $url; ?>" target="_blank"><?php echo $priceInfo->wheretobuy_id == 1 ? $priceInfo->title : $priceInfo->wheretobuy_title; ?></a>
                            </div>
                            <?php if($iPrice > 1)break;?>
                            <?php $iPrice++;?>
                        <?php endforeach;?>

                      <?php elseif($sitereview->price > 0 && $this->listingtypeArray->where_to_buy == 2): ?>
                        <div class="sr_price">
                          <?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($sitereview->price);  ?>
                        </div>
                      <?php endif; ?>

                    </div>  
                  <?php endif; ?>

                  <div class="sr_browse_list_rating">
                    <?php if(!empty($sitereview->rating_editor) && ($ratingValue == 'rating_both'|| $ratingValue == 'rating_editor')): ?>
                      <div class="clr">	
                        <div class="sr_browse_list_rating_stats">
                          <?php echo $this->translate("Editor Rating");?>
                        </div>
                       <?php $ratingData = $this->ratingTable->ratingbyCategory($sitereview->listing_id,'editor', $sitereview->getType()); ?>
                        <div class="sr_ur_show_rating_star fnone o_hidden">
                          <span class="sr_browse_list_rating_stars">
                            <span class="fleft">
                              <?php echo $this->showRatingStar($sitereview->rating_editor, 'editor', 'big-star', $sitereview->listingtype_id); ?>
                            </span>
                            <?php if (count($ratingData) > 1): ?>
                              <i class="fright arrow_btm"></i>
                          <?php endif; ?>
                          </span>

                          <?php if (count($ratingData) > 1): ?>
                            <div class="sr_ur_show_rating br_body_bg b_medium">
                              <div class="sr_profile_rating_parameters sr_ur_show_rating_box">

                                <?php foreach ($ratingData as $reviewcat): ?>

                                  <div class="o_hidden">
                                    <?php if (!empty($reviewcat['ratingparam_name'])): ?>
                                      <div class="parameter_title">
                                        <?php echo $this->translate($reviewcat['ratingparam_name']); ?>
                                      </div>
                                      <div class="parameter_value">
                                        <?php echo $this->showRatingStar($reviewcat['avg_rating'], 'editor', 'small-box', $sitereview->listingtype_id,$reviewcat['ratingparam_name']); ?>
                                      </div>
                                    <?php else: ?>
                                      <div class="parameter_title">
                                        <?php echo $this->translate("Overall Rating"); ?>
                                      </div>	
                                      <div class="parameter_value" style="margin: 0px 0px 5px;">
                                        <?php echo $this->showRatingStar($reviewcat['avg_rating'], 'editor', 'big-star', $sitereview->listingtype_id); ?>
                                      </div>
                                    <?php endif; ?> 
                                  </div>

                                <?php endforeach; ?>
                              </div>
                            </div>
                          <?php endif; ?>
                        </div>
                       </div> 
                     <?php endif; ?>
                    <?php if(!empty($sitereview->rating_users) && ($ratingValue == 'rating_both'|| $ratingValue == 'rating_users')): ?>
                      <div class="clr">
                        <div class="sr_browse_list_rating_stats">
                        <?php echo $this->translate("User Ratings");?><br />
                          <?php 
                            $totalUserReviews = $sitereview->review_count;
                            if($sitereview->rating_editor){
                              $totalUserReviews = $sitereview->review_count - 1;
                            }
                          ?>
                          <?php if($this->listingtypeArray->allow_review): ?>
														<?php echo $this->translate(array('Based on %s review', 'Based on %s reviews', $totalUserReviews), $this->locale()->toNumber($totalUserReviews)) ?>
                          <?php endif;?>
                        </div>
                         <?php $ratingData = $this->ratingTable->ratingbyCategory($sitereview->listing_id, 'user', $sitereview->getType());?>
                          <div class="sr_ur_show_rating_star fnone o_hidden">
                            <span class="sr_browse_list_rating_stars">
                             <span class="fleft">
                               <?php echo $this->showRatingStar($sitereview->rating_users, 'user', 'big-star', $sitereview->listingtype_id); ?>
                             </span>
                             <?php if (count($ratingData) > 1): ?>
                               <i class="fright arrow_btm"></i>
                           <?php endif; ?>
                           </span>

                           <?php if (count($ratingData) > 1): ?>
                             <div class="sr_ur_show_rating  br_body_bg b_medium">
                               <div class="sr_profile_rating_parameters sr_ur_show_rating_box">

                                 <?php foreach ($ratingData as $reviewcat): ?>

                                   <div class="o_hidden">
                                     <?php if (!empty($reviewcat['ratingparam_name'])): ?>
                                       <div class="parameter_title">
                                         <?php echo $this->translate($reviewcat['ratingparam_name']); ?>
                                       </div>
                                       <div class="parameter_value">
                                         <?php echo $this->showRatingStar($reviewcat['avg_rating'], 'user', 'small-box', $sitereview->listingtype_id,$reviewcat['ratingparam_name']); ?>
                                       </div>
                                     <?php else: ?>
                                       <div class="parameter_title">
                                         <?php echo $this->translate("Overall Rating"); ?>
                                       </div>	
                                       <div class="parameter_value" style="margin: 0px 0px 5px;">
                                         <?php echo $this->showRatingStar($reviewcat['avg_rating'], 'user', 'big-star', $sitereview->listingtype_id); ?>
                                       </div>
                                     <?php endif; ?> 
                                   </div>

                                 <?php endforeach; ?>
                               </div>
                             </div> 
                           <?php endif; ?> 
                         </div>
                       </div>  
                   <?php endif;?>

                    <?php if(!empty($sitereview->rating_avg) && ($ratingValue == 'rating_avg')): ?>
                      <div class="clr">
                        <?php if($this->listingtypeArray->allow_review): ?>
													<div class="sr_browse_list_rating_stats">
		<!--	                    <?php //echo $this->translate("Overall Rating");?><br />-->

														<?php echo $this->translate(array('Based on %s review', 'Based on %s reviews', $sitereview->review_count), $this->locale()->toNumber($sitereview->review_count)) ?>
													</div>
                        <?php endif;?>
                         <?php $ratingData = $this->ratingTable->ratingbyCategory($sitereview->listing_id, null, $sitereview->getType());?>
                          <div class="sr_ur_show_rating_star fnone o_hidden">
                            <span class="sr_browse_list_rating_stars">
                             <span class="fleft">
                               <?php echo $this->showRatingStar($sitereview->rating_avg, $ratingType, 'big-star', $sitereview->listingtype_id); ?>
                             </span>
                             <?php if (count($ratingData) > 1): ?>
                               <i class="fright arrow_btm"></i>
                           <?php endif; ?>
                           </span>

                           <?php if (count($ratingData) > 1): ?>
                             <div class="sr_ur_show_rating  br_body_bg b_medium">
                               <div class="sr_profile_rating_parameters sr_ur_show_rating_box">

                                 <?php foreach ($ratingData as $reviewcat): ?>

                                   <div class="o_hidden">
                                     <?php if (!empty($reviewcat['ratingparam_name'])): ?>
                                       <div class="parameter_title">
                                         <?php echo $this->translate($reviewcat['ratingparam_name']); ?>
                                       </div>
                                       <div class="parameter_value">
                                         <?php echo $this->showRatingStar($reviewcat['avg_rating'], $ratingType, 'small-box', $sitereview->listingtype_id,$reviewcat['ratingparam_name']); ?>
                                       </div>
                                     <?php else: ?>
                                       <div class="parameter_title">
                                         <?php echo $this->translate("Overall Rating"); ?>
                                       </div>	
                                       <div class="parameter_value" style="margin: 0px 0px 5px;">
                                         <?php echo $this->showRatingStar($reviewcat['avg_rating'], $ratingType, 'big-star', $sitereview->listingtype_id); ?>
                                       </div>
                                     <?php endif; ?> 
                                   </div>

                                 <?php endforeach; ?>
                               </div>
                             </div> 
                           <?php endif; ?> 
                         </div>
                       </div>  
                   <?php endif;?>
                  </div>

                  <div class="sr_browse_list_info">
                    <div class="sr_browse_list_info_header">
                      <div class="sr_list_title">
                        <?php echo $this->htmlLink($sitereview->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($sitereview->getTitle(), $this->title_truncation), array('title' => $sitereview->getTitle())); ?>
                      </div>
                    </div>  

                    <div class='sr_browse_list_info_blurb'>
                      <?php if($this->bottomLine): ?>
                        <?php echo $this->viewMore($sitereview->getBottomLine(), 125, 5000);?>
                      <?php else: ?>
                        <?php echo $this->viewMore(strip_tags($sitereview->body), 125, 5000);?>
                      <?php endif; ?>
                    </div>

                    <?php if(!empty($this->statistics)): ?>
                      <div class='sr_browse_list_info_stat seaocore_txt_light'>
                        <?php 

                          $statistics = '';

                          if(in_array('commentCount', $this->statistics)) {
                            $statistics .= $this->translate(array('%s comment', '%s comments', $sitereview->comment_count), $this->locale()->toNumber($sitereview->comment_count)).', ';
                          }

                                               if(in_array('reviewCount', $this->statistics) && (!empty($this->listingtypeArray->allow_review) || (isset($sitereview->rating_editor) && $sitereview->rating_editor))) {
                            $statistics .= $this->partial(
                          '_showReview.tpl', 'sitereview', array('sitereview'=>$sitereview)).', ';
                          }

                          if(in_array('viewCount', $this->statistics)) {
                            $statistics .= $this->translate(array('%s view', '%s views', $sitereview->view_count), $this->locale()->toNumber($sitereview->view_count)).', ';
                          }

                          if(in_array('likeCount', $this->statistics)) {
                            $statistics .= $this->translate(array('%s like', '%s likes', $sitereview->like_count), $this->locale()->toNumber($sitereview->like_count)).', ';
                          }                 

                          $statistics = trim($statistics);
                          $statistics = rtrim($statistics, ',');

                        ?>
                        <?php echo $statistics ?>
                      </div>   
                    <?php endif; ?>
                    <?php if(!empty($sitereview->location) && $this->listingtypeArray->location): ?>
                      <div class='sr_browse_list_info_stat seaocore_txt_light'>
                        <?php echo $this->translate($sitereview->location); ?>
                        - <b><?php echo  $this->htmlLink(array('route' => 'seaocore_viewmap', "id" => $sitereview->listing_id, 'resouce_type' => 'sitereview_listing'), $this->translate("Get Directions"), array('class' => 'smoothbox')) ; ?></b>
                      </div>
                    <?php endif; ?>
                    <div class='sr_browse_list_info_stat seaocore_txt_light'>
                      <?php echo $this->timestamp(strtotime($sitereview->creation_date)) ?><?php if($this->postedby): ?> - <?php echo $this->translate(strtoupper($this->listingtypeArray->title_singular). '_posted_by'); ?>
                      <?php echo $this->htmlLink($sitereview->getOwner()->getHref(), $sitereview->getOwner()->getTitle()) ?><?php endif; ?>
                    </div>
                    <?php if($this->showExpiry):?>
                      <?php if ($expirySettings == 2): $exp=$sitereview->getExpiryTime();
                        echo '<div class="sr_browse_list_info_stat seaocore_txt_light">' . $exp ? $this->translate("Expiry On: %s",$this->locale()->toDate($exp, array('size'=>'medium'))) :'' . '</div>';
                        $now = new DateTime(date("Y-m-d H:i:s"));
                        $ref = new DateTime($this->locale()->toDate($exp));
                        $diff = $now->diff($ref);
                        echo '<div class="sr_browse_list_info_stat seaocore_txt_light">';
                        echo $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i);
                        echo '</div>';

                      elseif ($expirySettings == 1 && $sitereview->end_date && $sitereview->end_date !='0000-00-00 00:00:00'):
                        echo '<div class="sr_browse_list_info_stat seaocore_txt_light">' . $this->translate("Ending On: %s",$this->locale()->toDate(strtotime($sitereview->end_date), array('size'=>'medium'))) . '</div>';
                            $now = new DateTime(date("Y-m-d H:i:s"));
                            $ref = new DateTime($sitereview->end_date);
                            $diff = $now->diff($ref);
                            echo '<div class="sr_browse_list_info_stat seaocore_txt_light">';
                            echo $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i);
                            echo '</div>';

                      endif;?>                  
                    <?php endif; ?>
                    <div class='sr_browse_list_info_stat seaocore_txt_light'>
                      <a href="<?php echo $this->url(array('category_id' => $sitereview->category_id, 'categoryname' => $sitereview->getCategory()->getCategorySlug()), "sitereview_general_category_listtype_" . $sitereview->listingtype_id); ?>"> 
                        <?php echo $this->translate($sitereview->getCategory()->getTitle(true)) ?>
                      </a>
                    </div>

                    <?php if($sitereview->price > 0 && $this->listingtypeArray->price): ?>
                      <div class='sr_browse_list_info_stat'>
                        <b><?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($sitereview->price); ?></b>
                      </div>
                    <?php endif; ?>

                    <div class="mtop10 sr_browse_list_info_footer clr o_hidden">
                      <?php echo $this->compareButton($sitereview);?>
                      <?php echo $this->addToWishlist($sitereview, array('classIcon' => 'sr_wishlist_href_link', 'classLink' => ''));?>

                      <?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)) :?>  
                      <span class="sr_browse_list_info_footer_icons">
                        <?php if( $sitereview->closed ): ?>
                          <img alt="close" src='<?php echo $this->layout()->staticBaseUrl?>application/modules/Sitereview/externals/images/close.png'/>
                        <?php endif;?>
                        <?php if ($sitereview->sponsored == 1): ?>
                          <i class="sr_icon seaocore_icon_sponsored" title="<?php echo $this->translate('Sponsored');?>"></i>
                        <?php endif; ?>
                        <?php if ($sitereview->featured == 1): ?>
                          <i class="sr_icon seaocore_icon_featured" title="<?php echo $this->translate('Featured'); ?>"></i>
                        <?php endif; ?>
                      </span>
                      <?php endif;?>
                    </div>
                  </div>
              </li>
            <?php endforeach; ?>
          </ul>

        <?php endif; ?>

      </div>

    <?php endif; ?>

    <?php if( $this->grid_view):?>
      <div id="image_view" class="sr_container" <?php if($this->defaultView !=1): ?>style="display: none;" <?php endif; ?>>
      <ul class="sitereview_thumb_view sr_grid_view mtop10">
          <?php $isLarge = ($this->columnWidth>170); ?>
          <?php foreach ($this->paginator as $sitereview): ?>          
             <li class="b_medium" style="width: <?php echo $this->columnWidth; ?>px;">
               <div class="sr_product_details <?php if($isLarge): ?>largephoto<?php endif;?>" style="height:<?php echo $this->columnHeight; ?>px;">
                    <?php if($sitereview->newlabel):?>
                      <i class="sr_list_new_label" title="<?php echo $this->translate('New'); ?>"></i>
                    <?php endif;?>
                  <a href="<?php echo $sitereview->getHref(array('profile_link' => 1)) ?>" class ="sr_thumb">
                    <?php
                    $url = $sitereview->getPhotoUrl($isLarge ?'thumb.midum' :'thumb.normal');


                    if (empty($url)):  $url = $this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/nophoto_listing_thumb_normal.png';
                    endif;
                    ?>
                    <span style="background-image: url(<?php echo $url; ?>);"></span>
                  </a>
                  <div class="sr_title">
                    <?php echo $this->htmlLink($sitereview->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($sitereview->getTitle(), $this->title_truncationGrid),array('title'=>$sitereview->getTitle())) ?>
                  </div>
                  <?php if($this->showExpiry):?>    
                    <?php if ($expirySettings == 2): $exp=$sitereview->getExpiryTime();
                      echo '<div class="sr_date seaocore_txt_light">' . $exp ? $this->translate("Expiry On: %s",$this->locale()->toDate($exp, array('size'=>'medium'))) :'' . '</div>';
                      $now = new DateTime(date("Y-m-d H:i:s"));
                      $ref = new DateTime($this->locale()->toDate($exp));
                      $diff = $now->diff($ref);
                      echo '<div class="sr_date seaocore_txt_light">';
                      echo $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i);
                      echo '</div>';

                    elseif ($expirySettings == 1 && $sitereview->end_date && $sitereview->end_date !='0000-00-00 00:00:00'):
                      echo '<div class="sr_date seaocore_txt_light">' . $this->translate("Ending On: %s",$this->locale()->toDate(strtotime($sitereview->end_date), array('size'=>'medium'))) . '</div>';
                          $now = new DateTime(date("Y-m-d H:i:s"));
                          $ref = new DateTime($sitereview->end_date);
                          $diff = $now->diff($ref);
                          echo '<div class="sr_date seaocore_txt_light">';
                          echo $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i);
                          echo '</div>';

                    endif;?>                      
                  <?php endif;?>    
                  <div class="sr_category clr">
                     <?php if($sitereview->price > 0 && (empty ($this->listingtype_id)|| ($this->listingtype_id && $this->listingtypeArray->price))): ?>
                      <span class="fright">   
                        <b><?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($sitereview->price); ?></b>
                      </span>
                    <?php endif; ?>                

                    <a href="<?php echo $this->url(array('category_id' => $sitereview->category_id, 'categoryname' => $sitereview->getCategory()->getCategorySlug()), "sitereview_general_category_listtype_" . $sitereview->listingtype_id); ?>"> <?php echo $this->translate($sitereview->getCategory()->getTitle(true)) ?> </a>
                  </div>
                  <div class="sr_ratingbar seaocore_txt_light">
                    <?php if(in_array('reviewCount', $this->statistics)): ?>
                      <span class="fright">
                       <?php echo $this->htmlLink($sitereview->getHref(), $this->partial(
                      '_showReview.tpl', 'sitereview', array('sitereview'=>$sitereview)), array('title' => $this->partial(
                      '_showReview.tpl', 'sitereview', array('sitereview'=>$sitereview)))); ?>
                      </span>
                    <?php endif; ?> 
                    <?php if ($ratingValue == 'rating_both'): ?>
                      <?php echo $this->showRatingStar($sitereview->rating_editor, 'editor', $ratingShow, $sitereview->listingtype_id); ?>
                      <br/>
                      <?php echo $this->showRatingStar($sitereview->rating_users, 'user', $ratingShow, $sitereview->listingtype_id); ?>
                    <?php else: ?>
                      <?php echo $this->showRatingStar($sitereview->$ratingValue, $ratingType, $ratingShow, $sitereview->listingtype_id); ?>
                    <?php endif; ?> 
                  </div>
                  <div class="sr_grid_view_list_btm b_medium">

                    <?php echo $this->compareButton($sitereview); ?>
                    <span class="fright">
                      <?php if ($sitereview->sponsored == 1): ?>
                        <i class="sr_icon seaocore_icon_sponsored" title="<?php echo $this->translate('Sponsored');?>"></i>
                      <?php endif; ?>
                      <?php if ($sitereview->featured == 1): ?>
                        <i class="sr_icon seaocore_icon_featured" title="<?php echo $this->translate('Featured'); ?>"></i>
                      <?php endif; ?>
                      <?php echo $this->addToWishlist($sitereview, array('classIcon' => 'icon_wishlist_add', 'classLink' => 'sr_wishlist_link', 'text' => ''));?>
                    </span>
                  </div>
                </div>
              </li>
            <?php endforeach; ?>
      </ul>
      </div>
    <?php endif; ?>

    <div id="sr_map_canvas_view_browse" <?php if($this->defaultView !=2): ?> style="display: none;" <?php endif; ?>>
      <div class="seaocore_map clr" style="overflow:hidden;">
        <div id="sitereview_browse_map_canvas" class="sr_list_map"> </div>
        <div class="clear mtop10"></div>
        <?php $siteTitle = Engine_Api::_()->getApi('settings', 'core')->core_general_site_title; ?>
        <?php if (!empty($siteTitle)) : ?>
          <div class="seaocore_map_info"><?php echo $this->translate("Locations on %s","<a href='' target='_blank'>$siteTitle</a>");?></div>
        <?php endif; ?>
      </div>
      <?php if( $this->enableLocation && $this->flageSponsored && $this->map_view && $enableBouce): ?>
        <a href="javascript:void(0);" onclick="toggleBounce()" class="fleft sr_list_map_bounce_link"> <?php echo $this->translate('Stop Bounce'); ?></a>
      <?php endif;?>  
    </div>

    <div class="clear"></div>
        
    <div class="seaocore_pagination">
      <?php echo $this->paginationControl($this->result, null, array("pagination/pagination.tpl", "sitereview"), array("orderby" => $this->orderby)); ?>
    </div>
    <?php elseif (isset($this->params['tag_id']) || isset($this->params['category_id'])): ?>
      <br/>
      <div class="tip mtip10">
        <span> <?php echo $this->translate('Nobody has posted a '.strtolower($this->listingtypeArray->title_singular).' with that criteria.'); ?>
            <?php if ($this->can_create):?>
              <?php if (Engine_Api::_()->sitereview()->hasPackageEnable()):?>
                <?php echo $this->translate('BE_THE_FIRST_'.strtoupper($this->listingtypeArray->title_singular).'_TO %1$spost%2$s one!', '<a href="' . $this->url(array('action' => 'index'), "sitereview_package_listtype_$this->listingtype_id") . '">', '</a>'); ?>
              <?php else:?>
                <?php echo $this->translate('BE_THE_FIRST_'.strtoupper($this->listingtypeArray->title_singular).'_TO %1$spost%2$s one!', '<a href="' . $this->url(array('action' => 'create'), "sitereview_general_listtype_$this->listingtype_id") . '">', '</a>'); ?>
               <?php endif;?>
            <?php endif; ?>
        </span> 
      </div>
    <?php else: ?>
        <div class="tip mtop10"> 
          <span> 
            <?php echo $this->translate('No '.strtolower($this->listingtypeArray->title_plural).' have been posted yet.'); ?>
            <?php if ($this->can_create):?>
              <?php if (Engine_Api::_()->sitereview()->hasPackageEnable()):?>
                <?php echo $this->translate('BE_THE_FIRST_'.strtoupper($this->listingtypeArray->title_singular).'_TO %1$spost%2$s one!', '<a href="' . $this->url(array('action' => 'index'), "sitereview_package_listtype_$this->listingtype_id") . '">', '</a>'); ?>
              <?php else:?>
                <?php echo $this->translate('BE_THE_FIRST_'.strtoupper($this->listingtypeArray->title_singular).'_TO %1$spost%2$s one!', '<a href="' . $this->url(array('action' => 'create'), "sitereview_general_listtype_$this->listingtype_id") . '">', '</a>'); ?>
              <?php endif;?>
            <?php endif; ?>
          </span>
        </div>
      <?php endif; ?>

    <script type="text/javascript" >
      function switchview(flage){
        if(flage==2){
          if($('sr_map_canvas_view_browse')){
          $('sr_map_canvas_view_browse').style.display='block';
        <?php if( $this->enableLocation && $this->map_view && $this->paginator->count() > 0): ?>
          google.maps.event.trigger(map, 'resize');
          map.setZoom(<?php echo $defaultZoom?>);
          map.setCenter(new google.maps.LatLng(<?php echo $latitude ?>,<?php echo $longitude?>));
        <?php endif; ?>
          if($('grid_view'))
          $('grid_view').style.display='none';
          if($('image_view'))
          $('image_view').style.display='none';
        }
        }else if(flage==1){
          if($('image_view')){
          if($('sr_map_canvas_view_browse'))
          $('sr_map_canvas_view_browse').style.display='none';
          if($('grid_view'))
          $('grid_view').style.display='none';
          $('image_view').style.display='block';
          }
        }else{
          if($('grid_view')){
          if($('sr_map_canvas_view_browse'))
          $('sr_map_canvas_view_browse').style.display='none';
          $('grid_view').style.display='block';
          if($('image_view'))
          $('image_view').style.display='none';
          }
        }
      }
    </script>

    <script type="text/javascript">

      /* moo style */
      en4.core.runonce.add(function() {
        //opacity / display fix
        $$('.sitereview_tooltip').setStyles({
          opacity: 0,
          display: 'block'
        });
        //put the effect in place
        $$('.jq-sitereview_tooltip li').each(function(el,i) {
          el.addEvents({
            'mouseenter': function() {
              el.getElement('div').fade('in');
            },
            'mouseleave': function() {
              el.getElement('div').fade('out');
            }
          });
        });
      <?php if($this->paginator->count()>0):?>
        <?php if( $this->enableLocation && $this->map_view): ?>
        initialize();  
        <?php endif; ?>

          switchview(<?php echo $this->defaultView ?>);
        <?php endif;?>
      });

    </script>

    <?php if( $this->enableLocation && $this->map_view && $this->paginator->count() > 0): ?>

    <script type="text/javascript">
      //<![CDATA[
      // this variable will collect the html which will eventually be placed in the side_bar
      var side_bar_html = "";

      // arrays to hold copies of the markers and html used by the side_bar
      // because the function closure trick doesnt work there
      var gmarkers = [];

      // global "map" variable
      var map = null;
      // A function to create the marker and set up the event window function
      function createMarker(latlng, name, html) {
        var contentString = html;
        if(name ==0){
          var marker = new google.maps.Marker({
            position: latlng,
            map: map,
            animation: google.maps.Animation.DROP,
            zIndex: Math.round(latlng.lat()*-100000)<<5
          });
        }
        else{
          var marker =new google.maps.Marker({
            position: latlng,
            map: map,
            draggable: false,
            animation: google.maps.Animation.BOUNCE
          });
        }
        gmarkers.push(marker);
        google.maps.event.addListener(marker, 'click', function() {
          infowindow.setContent(contentString);
          google.maps.event.trigger(map, 'resize');
          infowindow.open(map,marker);
        });
      }

      function initialize() {

        // create the map
        var myOptions = {
          zoom: <?php echo $defaultZoom?>,
          center: new google.maps.LatLng(<?php echo $latitude ?>,<?php echo $longitude?>),
          navigationControl: true,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        }
        map = new google.maps.Map(document.getElementById("sitereview_browse_map_canvas"),
        myOptions);

        google.maps.event.addListener(map, 'click', function() {
          infowindow.close();
          google.maps.event.trigger(map, 'resize');
        });

    <?php foreach ($this->locations as $location) : ?>
      <?php if( Engine_Api::_()->authorization()->isAllowed($this->sitereview[$location->listing_id], $this->viewer(), 'view')):?>
        // obtain the attribues of each marker
        var lat = <?php echo $location->latitude ?>;
        var lng =<?php echo $location->longitude  ?>;
        var point = new google.maps.LatLng(lat,lng);
        <?php if(!empty ($enableBouce)):?>
        var sponsored = <?php echo $this->sitereview[$location->listing_id]->sponsored ?>
          <?php else:?>
          var sponsored =0;
        <?php endif; ?>

        var contentString = "<?php
      echo $this->string()->escapeJavascript($this->partial('application/modules/Sitereview/views/scripts/_mapInfoWindowContent.tpl', array(
                  'sitereview' => $this->sitereview[$location->listing_id],
                  'ratingValue' => $ratingValue,
                  'ratingType' => $ratingType,
                  'postedby' => $this->postedby,
                  'statistics' => $this->statistics,
                  'showContent' => array("price", "location"),
                  'content_type' => null,
                  'postedbytext' => strtoupper($this->listingtypeArray->title_singular),
                  'ratingShow' => $ratingShow)), false);
      ?>";

            var marker = createMarker(point,sponsored,contentString);
          <?php endif; ?>
        <?php   endforeach; ?>
      }

      var infowindow = new google.maps.InfoWindow(
      {
        size: new google.maps.Size(250,50)
      });

      function toggleBounce() {
        for(var i=0; i<gmarkers.length;i++){
          if (gmarkers[i].getAnimation() != null) {
            gmarkers[i].setAnimation(null);
          }
        }
      }
    </script>
  <?php endif;?>

  <?php if($this->category_id && !$this->subcategory_id && !$this->subsubcategory_id):?>
    <div class="sr_browse_cat_cont clr"><?php echo Engine_Api::_()->getItem('sitereview_category', $this->category_id)->bottom_content;?></div>
  <?php elseif($this->subcategory_id && $this->category_id && !$this->subsubcategory_id):?>
    <div class="sr_browse_cat_cont clr"><?php echo Engine_Api::_()->getItem('sitereview_category', $this->subcategory_id)->bottom_content;?></div>
  <?php elseif($this->subsubcategory_id && $this->category_id && $this->subcategory_id):?>
    <div class="sr_browse_cat_cont clr"><?php echo Engine_Api::_()->getItem('sitereview_category', $this->subsubcategory_id)->bottom_content;?></div>
  <?php endif;?> 
  
<?php else: ?>

  <div id="layout_sitereview_browse_listings_<?php echo $this->identity;?>">
<!--    <div class="seaocore_content_loader"></div>-->
  </div>

  <script type="text/javascript">
    var requestParams = $merge(<?php echo json_encode($this->paramsLocation);?>, {'content_id': '<?php echo $this->identity;?>'})
    var params = {
      'detactLocation': <?php echo $this->detactLocation; ?>,
      'responseContainer' : 'layout_sitereview_browse_listings_<?php echo $this->identity;?>',
       requestParams: requestParams      
    };
   
    var timeOutForLocationDetaction = 0;

    <?php if($this->detactLocation): ?>
    var locationsParams = {};
    if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position){
      var lat = position.coords.latitude;
      var lng = position.coords.longitude;

      mapGetDirection = new google.maps.Map(document.getElementById("sitereview_location_map_none"),  {
        zoom: 8 ,
        center: new google.maps.LatLng(lat,lng),
        navigationControl: true,
        mapTypeId: google.maps.MapTypeId.ROADMAP
      });

      if(!position.address) {
        var service = new google.maps.places.PlacesService(mapGetDirection);
        var request = {
          location: new google.maps.LatLng(lat,lng), 
          radius: 500
        };

        service.search(request, function(results, status) { 
          if (status  ==  'OK') { 
            var index = 0;
            var radian = 3.141592653589793/ 180;

            locationsParams.location = (results[index].vicinity) ? results[index].vicinity :'';
            locationsParams.Latitude = lat;
            locationsParams.Longitude = lng;
            locationsParams.locationmiles = <?php echo $this->defaultLocationDistance ?>;
            setLocationsParams();
          } 
        });
      } else { 
        var delimiter = (position.address && position.address.street !=  '' && position.address.city !=  '') ? ', ' : '';
        var location = (position.address) ? (position.address.street + delimiter + position.address.city) : '';

        locationsParams.location = location;
        locationsParams.Latitude = lat;
        locationsParams.Longitude = lng;
        locationsParams.locationmiles = <?php echo $this->defaultLocationDistance ?>;
        setLocationsParams();
      }
    },function(){
      timeOutForLocationDetaction = 1;
      en4.seaocore.locationBased.sendReq(params);
    },{
      maximumAge:6000,
      timeout:3000
    });
    
    var locationTimeout = window.setTimeout(function() {
      if(timeOutForLocationDetaction == 0) {
      en4.seaocore.locationBased.sendReq(params);
      }
    }, 3000);    
    
    var setLocationsParams =  function(){
      if(!document.getElementById('location'))
        return;
      document.getElementById('location').value = locationsParams.location;
      if(document.getElementById('Latitude'))
        document.getElementById('Latitude').value = locationsParams.latitude;
      if(document.getElementById('Longitude'))
        document.getElementById('Longitude').value = locationsParams.longitude;
      if(document.getElementById('locationmiles'))
        document.getElementById('locationmiles').value = locationsParams.locationmiles;
      
      params.requestParams= $merge(params.requestParams,locationsParams);
      
      timeOutForLocationDetaction = 1;
      
      en4.seaocore.locationBased.sendReq(params);
    }              
  }
  else { 
    timeOutForLocationDetaction = 1;
    en4.seaocore.locationBased.sendReq(params);
  }  
  <?php else: ?>
    timeOutForLocationDetaction = 1;
    en4.seaocore.locationBased.sendReq(params);
  <?php endif; ?>  
  </script>  

<?php endif; ?>  