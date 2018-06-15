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

$baseUrl = $this->layout()->staticBaseUrl;
$this->headLink()
        ->prependStylesheet($baseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');
$this->headLink()
        ->prependStylesheet($baseUrl . 'application/modules/Sitereview/externals/styles/style_rating.css');
$this->headLink()
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/styles/styles.css');
$this->headScript()
      ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/scripts/core.js');
?>
<?php if ($this->enableLocation): ?>
<?php
$apiKey = Engine_Api::_()->seaocore()->getGoogleMapApiKey();
$this->headScript()->appendFile("https://maps.googleapis.com/maps/api/js?libraries=places&key=$apiKey");
?>
<?php endif;?>

<?php if($this->is_ajax_load): ?>

  <?php

  $ratingValue = $this->ratingType;
  $ratingShow = 'small-star';
    if ($this->ratingType == 'rating_editor') {$ratingType = 'editor';} elseif ($this->ratingType == 'rating_avg') {$ratingType = 'overall';} else { $ratingType = 'user';}
   ?>

  <?php if (empty($this->is_ajax)): ?>


    <div class="layout_core_container_tabs">
      <?php if ($this->tabCount > 1 || count($this->layouts_views)>1): ?>
        <div class="tabs_alt tabs_parent tabs_parent_sitereview_home">
          <ul id="main_tabs" rel="<?php echo $this->listingtype_id; ?>" identity='<?php echo $this->identity ?>'>
            <?php if ($this->tabCount > 1): ?>
              <?php foreach ($this->tabs as $key => $tab): ?>
                <li class="tab_li_<?php echo $this->identity ?> <?php echo $key == 0 ? 'active' : ''; ?>" rel="<?php echo $tab; ?>">
                  <a  href='javascript:void(0);' ><?php echo $this->translate(ucwords(str_replace('_', ' ', $tab))); ?> </a>
                </li>
              <?php endforeach; ?>
            <?php endif; ?>
            <?php
            for ($i = count($this->layouts_views) - 1; $i >= 0; $i--):
              ?>
              <li class="seaocore_tab_select_wrapper fright" rel='<?php echo $this->layouts_views[$i] ?>'>
                <div class="seaocore_tab_select_view_tooltip"><?php echo $this->translate(ucwords(str_replace('_', ' ', $this->layouts_views[$i]))) ?></div>
                <span id="<?php echo $this->layouts_views[$i] . "_" . $this->identity ?>"class="seaocore_tab_icon tab_icon_<?php echo $this->layouts_views[$i] ?>" onclick="srTabSwitchview($(this));" ></span>

              </li>
            <?php endfor; ?>
          </ul>
        </div>
      <?php endif; ?>
      <div id="dynamic_app_info_sr_<?php echo $this->identity; ?>">
      <?php endif; ?>
      <?php if (in_array('list_view', $this->layouts_views)): ?> 
        <div class="sr_container" id="list_view_sr_<?php echo $this->listingtype_id; ?>" style="<?php echo $this->defaultLayout !== 'list_view' ? 'display: none;' : '' ?>">
          <ul class="sr_browse_list sr_list_view">
            <?php if($this->totalCount):?>
            <?php foreach ($this->paginator as $sitereview): ?>
              <?php Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
                  $listingType = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);?>
             <?php if($this->listViewType=='list'):?>    
              <li class="b_medium">
                <div class='sr_browse_list_photo b_medium'>
                  <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)):?>
                    <?php if($sitereview->featured):?>
                      <i class="sr_list_featured_label" title="<?php echo $this->translate('Featured'); ?>"></i>
                    <?php endif;?>
                    <?php if($sitereview->newlabel):?>
                      <i class="sr_list_new_label" title="<?php echo $this->translate('New'); ?>"></i>
                    <?php endif;?>
                  <?php endif;?>

                  <?php echo $this->htmlLink($sitereview->getHref(array('profile_link' => 1)), $this->itemPhoto($sitereview, 'thumb.normal', '', array('align' => 'center'))); ?>

                  <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)):?>
                    <?php if (!empty($sitereview->sponsored)): ?>
                        <div class="sr_list_sponsored_label" style="background: <?php echo $listingType->sponsored_color; ?>">
                          <?php echo $this->translate('SPONSORED'); ?>                 
                        </div>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>

                <div class='sr_browse_list_info'>
                  <div class="sr_browse_list_show_rating fright">  
                    <?php if ($ratingValue == 'rating_both'): ?>
                      <?php echo $this->showRatingStar($sitereview->rating_editor, 'editor', $ratingShow, $sitereview->listingtype_id); ?>
                      <?php echo $this->showRatingStar($sitereview->rating_users, 'user', $ratingShow, $sitereview->listingtype_id); ?>
                                    <?php else: ?>
                      <?php echo $this->showRatingStar($sitereview->$ratingValue, $ratingType, $ratingShow, $sitereview->listingtype_id); ?>
                                    <?php endif; ?> 
                                  </div>
                  <div class='sr_browse_list_info_header'>
                    <div class="sr_list_title_small o_hidden">
                       <?php echo $this->htmlLink($sitereview->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($sitereview->getTitle(), $this->title_truncationList), array('title' => $sitereview->getTitle())); ?>
                    </div>
                  </div>
                  <?php if (empty($this->category_id)): ?>
                    <div class='sr_browse_list_info_stat seaocore_txt_light'>
                      <a href="<?php echo $this->url(array('category_id' => $sitereview->category_id, 'categoryname' => $sitereview->getCategory()->getCategorySlug()), "sitereview_general_category_listtype_" . $sitereview->listingtype_id); ?>"> 
                        <?php echo $this->translate($sitereview->getCategory()->getTitle(true)) ?>
                      </a>
                    </div>
                  <?php endif; ?>
                  <div class='sr_browse_list_info_stat seaocore_txt_light'>
                    <?php $listing_singular_upper = strtoupper(Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id)->title_singular) ?>
                    <?php echo $this->timestamp(strtotime($sitereview->creation_date)) ?><?php if($this->postedby): ?> - <?php echo $this->translate($listing_singular_upper. '_posted_by'); ?>
                    <?php echo $this->htmlLink($sitereview->getOwner()->getHref(), $sitereview->getOwner()->getTitle()) ?><?php endif ?>
                  </div>
                  <?php if(!empty($this->statistics)): ?>
                    <div class='sr_browse_list_info_stat seaocore_txt_light'>
                      <?php 

                        $statistics = '';

                        if(in_array('commentCount', $this->statistics)) {
                          $statistics .= $this->translate(array('%s comment', '%s comments', $sitereview->comment_count), $this->locale()->toNumber($sitereview->comment_count)).', ';
                        }

                        $listingtypeArray = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);
                        if(in_array('reviewCount', $this->statistics) && ($listingtypeArray->reviews == 3 || $listingtypeArray->reviews == 2) && (!empty($listingtypeArray->allow_review) || (isset($sitereview->rating_editor) && $sitereview->rating_editor))) {

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
                    </div>
                  <?php endif ?>

                  <?php if(!empty($this->showContent) && in_array('endDate', $this->showContent)): ?>
                    <?php

                    $reviewApi = Engine_Api::_()->sitereview();
                    $expirySettings = $reviewApi->expirySettings($sitereview->listingtype_id);
                    $approveDate = null;
                    if ($expirySettings == 2):
                      $approveDate = $reviewApi->adminExpiryDuration($sitereview->listingtype_id);
                    endif;

                    ?>
                    <?php if ($expirySettings == 2): $exp=$sitereview->getExpiryTime();
                      $b = $exp ? $this->translate("Expiry On: %s",$this->locale()->toDate($exp, array('size'=>'medium'))) :'';
                      echo "<div class='sr_browse_list_info_stat seaocore_txt_light'>$b</div>";
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
                    <?php
                    if (!empty($sitereview->location) && $this->enableLocation && Engine_Api::_()->authorization()->isAllowed($sitereview, $this->viewer(), 'view') && !empty($this->showContent) && in_array('location', $this->showContent)):
                      echo $sitereview->location; ?>
                      - <b><?php echo  $this->htmlLink(array('route' => 'seaocore_viewmap', "id" => $sitereview->listing_id, 'resouce_type' => 'sitereview_listing'), $this->translate("Get Directions"), array('class' => 'smoothbox')) ; ?></b>
                   <?php endif; ?>

                  </div>
                  <?php if(!empty($sitereview->price) && $sitereview->price > 0 && Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id)->price  && !empty($this->showContent) && in_array('price', $this->showContent)): ?>
                    <div class='sr_browse_list_info_stat seaocore_txt_light'>
                       <b><?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($sitereview->price); ?></b>
                    </div>
                  <?php endif; ?>
                  <div class="sr_browse_list_info_footer clr o_hidden mtop5"> 
                    <?php echo $this->compareButton($sitereview); ?>
                    <?php echo $this->addToWishlist($sitereview, array('classIcon' => 'sr_wishlist_href_link', 'classLink' => ''));?>
                    <?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)) :?>
                      <div class="sr_browse_list_info_footer_icons">
                        <?php if ($sitereview->sponsored == 1 ): ?>
                          <i title="<?php echo $this->translate('Sponsored');?>" class="sr_icon seaocore_icon_sponsored"></i>
                        <?php endif; ?>
                        <?php if ($sitereview->featured == 1): ?>
                          <i title="<?php echo $this->translate('Featured');?>" class="sr_icon seaocore_icon_featured"></i>
                        <?php endif; ?>
                      </div>
                    <?php endif;?>
                  </div>
                </div>
              </li>
             <?php else: ?>
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

                <?php if(!empty($this->showContent) && in_array('price', $this->showContent)): ?>
                  <?php $priceInfos = $sitereview->getPriceInfo();?>
                  <?php $priceInfoCount = Count($priceInfos);?>
                  <div class='sr_browse_list_info'>
                    <?php $wheretobuyEnabled = $sitereview->allowWhereToBuy() ; ?>
                    <?php if($wheretobuyEnabled): ?>
                      <div class="sr_browse_list_price_info">
                        <?php if($priceInfoCount > 0):?>
                        <?php $minPrice = $sitereview->getWheretoBuyMinPrice(); $maxPrice = $sitereview->getWheretoBuyMaxPrice()?>
                          <?php if($minPrice): ?>
                          <div class="sr_price">

                            <?php if($minPrice == $maxPrice):?>
                              <?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($minPrice); ?>
                            <?php elseif($priceInfoCount == 2):?>
                              <?php echo $this->translate('%s and %1s',Engine_Api::_()->sitereview()->getPriceWithCurrency($minPrice), Engine_Api::_()->sitereview()->getPriceWithCurrency($maxPrice)); ?>
                            <?php else: ?>
                              <?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($minPrice); ?> - <?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($maxPrice); ?>
                            <?php endif ?>
                          </div>
                          <?php endif ?>
                          <div class="sr_browse_list_price_info_stats">
                            <?php $listingtypeArray = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);?>
                            <?php echo $this->translate(array(strtoupper($listingtypeArray->title_singular). '_AT %s store', strtoupper($listingtypeArray->title_singular).'_AT %s stores', $priceInfoCount), $this->locale()->toNumber($priceInfoCount)) ?>
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

                        <?php elseif($sitereview->price > 0): ?>
                          <div class="sr_price">
                            <?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($sitereview->price); ?>
                          </div>
                        <?php else: ?>
                          <div class="sr_browse_list_price_info_stats">
                            <?php echo $this->translate("No price available.")?>
                          </div>  
                        <?php endif; ?>

                      </div>  
                    <?php endif; ?>
                  <?php endif; ?>

                  <div class="sr_browse_list_rating">
                    <div class="sr_browse_list_show_rating fright">  
                      <?php if(!empty($sitereview->rating_editor) && ($ratingValue == 'rating_both'|| $ratingValue == 'rating_editor')): ?>
                      <div class="clr">	
                        <div class="sr_browse_list_rating_stats">
                          <?php echo $this->translate("Editor Rating");?>
                        </div>
                        <div class="sr_ur_show_rating_star fnone o_hidden">
                          <span class="sr_browse_list_rating_stars">
                              <?php echo $this->showRatingStar($sitereview->rating_editor, 'editor', 'big-star', $sitereview->listingtype_id); ?>
                          </span>
                        </div>
                       </div> 
                     <?php endif; ?>
                    <?php if(!empty($sitereview->rating_users) && ($ratingValue == 'rating_both'|| $ratingValue == 'rating_users')): ?>
                      <div class="clr">
                        <div class="sr_browse_list_rating_stats">
                        <?php echo $this->translate("User Ratings");?><br />
                          <?php  $totalUserReviews=($sitereview->rating_editor)? ($sitereview->review_count - 1):$sitereview->review_count ?>
                          <?php echo $this->translate(array('Based on %s review', 'Based on %s reviews', $totalUserReviews), $this->locale()->toNumber($totalUserReviews)) ?>
                        </div>
                          <div class="sr_ur_show_rating_star fnone o_hidden">
                            <span class="sr_browse_list_rating_stars">
                               <?php echo $this->showRatingStar($sitereview->rating_users, 'user', 'big-star', $sitereview->listingtype_id); ?>
                           </span>
                         </div>
                       </div>  
                   <?php endif;?>

                    <?php if(!empty($sitereview->rating_avg) && ($ratingValue == 'rating_avg')): ?>
                      <div class="clr">
                        <div class="sr_browse_list_rating_stats">

                          <?php echo $this->translate(array('Based on %s review', 'Based on %s reviews', $sitereview->review_count), $this->locale()->toNumber($sitereview->review_count)) ?>
                        </div>
                        <div class="sr_ur_show_rating_star fnone o_hidden">
                            <span class="sr_browse_list_rating_stars">
                               <?php echo $this->showRatingStar($sitereview->rating_avg, $ratingType, 'big-star', $sitereview->listingtype_id); ?>
                           </span>
                         </div>
                      </div>  
                   <?php endif;?>
                  </div>
                  </div>

                  <div class="sr_browse_list_info">
                    <div class="sr_browse_list_info_header">
                      <div class="sr_list_title_small">
                        <?php echo $this->htmlLink($sitereview->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($sitereview->getTitle(), $this->title_truncationList), array('title' => $sitereview->getTitle())); ?>
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

                          $listingtypeArray = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);
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

                    <?php if(!empty($this->showContent) && in_array('endDate', $this->showContent)): ?>
                      <?php

                      $reviewApi = Engine_Api::_()->sitereview();
                      $expirySettings = $reviewApi->expirySettings($sitereview->listingtype_id);
                      $approveDate = null;
                      if ($expirySettings == 2):
                        $approveDate = $reviewApi->adminExpiryDuration($sitereview->listingtype_id);
                      endif;

                      ?>
                      <?php if ($expirySettings == 2): $exp=$sitereview->getExpiryTime();
                      $b = $exp ? $this->translate("Expiry On: %s",$this->locale()->toDate($exp, array('size'=>'medium'))) :'';
                      echo "<div class='sr_browse_list_info_stat seaocore_txt_light'>$b</div>";
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

                    <?php $locationEnabled = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id)->location; ?>
                    <?php if(!empty($sitereview->location) && $locationEnabled && !empty($this->showContent) && in_array('location', $this->showContent)): ?>
                      <div class='sr_browse_list_info_stat seaocore_txt_light'>
                        <?php echo $this->translate($sitereview->location); ?>
                        - <b><?php echo  $this->htmlLink(array('route' => 'seaocore_viewmap', "id" => $sitereview->listing_id, 'resouce_type' => 'sitereview_listing'), $this->translate("Get Directions"), array('class' => 'smoothbox')) ; ?></b>
                      </div>
                    <?php endif; ?>
                    <div class='sr_browse_list_info_stat seaocore_txt_light'>
                      <?php $listingtypeArray = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);?>
                      <?php echo $this->timestamp(strtotime($sitereview->creation_date)) ?><?php if($this->postedby): ?> - <?php echo $this->translate(strtoupper($listingtypeArray->title_singular). '_posted_by'); ?>
                      <?php echo $this->htmlLink($sitereview->getOwner()->getHref(), $sitereview->getOwner()->getTitle()) ?><?php endif; ?>
                    </div>
                    <div class='sr_browse_list_info_stat seaocore_txt_light'>
                      <a href="<?php echo $this->url(array('category_id' => $sitereview->category_id, 'categoryname' => $sitereview->getCategory()->getCategorySlug()), "sitereview_general_category_listtype_" . $sitereview->listingtype_id); ?>"> 
                        <?php echo $this->translate($sitereview->getCategory()->getTitle(true)) ?>
                      </a>
                    </div>
                    <?php $priceEnabled = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id)->price; ?>
                    <?php if($sitereview->price > 0 && $priceEnabled && !empty($this->showContent) && in_array('price', $this->showContent)  && !empty($this->showContent) && in_array('price', $this->showContent)): ?>
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
             <?php endif; ?>
            <?php endforeach; ?>
              <?php else:?>
              <div class="tip">
                <span>
                  <?php if($this->listingtype_id > 0):?>
                  <?php $listingtypeArray = Zend_Registry::get('listingtypeArray' . $this->listingtype_id);?>
                  <?php echo $this->translate('No '.strtolower($listingtypeArray->title_plural).' have been posted yet.'); ?>
                  <?php else: ?>
                   <?php echo $this->translate('No listings have been posted yet.'); ?>
                  <?php endif; ?>
                </span>
              </div>
              <?php endif; ?>
          </ul>
        </div>
      <?php endif; ?>
      <?php if (in_array('grid_view', $this->layouts_views)): ?> 
        <div class="sr_container" id="grid_view_sr_<?php echo $this->listingtype_id; ?>" style="<?php echo $this->defaultLayout !== 'grid_view' ? 'display: none;' : '' ?>">
          <ul class="sitereview_thumb_view sr_grid_view o_hidden">
             <?php if($this->totalCount):?>
            <?php $isLarge = ($this->columnWidth>170); ?>
            <?php foreach ($this->paginator as $sitereview): ?>
              <li class="b_medium" style="width: <?php echo $this->columnWidth; ?>px;">
                <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)):?>
                  <?php if($sitereview->newlabel):?>
                    <i class="sr_list_new_label" title="<?php echo $this->translate('New'); ?>"></i>
                  <?php endif;?>
                <?php endif;?>
                <div class="sr_product_details" style="height:<?php echo $this->columnHeight; ?>px;">
                  <a href="<?php echo $sitereview->getHref(array('profile_link' => 1)) ?>" class ="sr_thumb">
                     <?php
                    $url = $sitereview->getPhotoUrl($isLarge ?'thumb.midum' :'thumb.normal');
                    if (empty($url)):  $url = $this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/nophoto_listing_thumb_normal.png';
                    endif;
                    ?>
                    <span style="background-image: url(<?php echo $url; ?>); <?php if($isLarge): ?> height:160px; <?php endif;?> "></span>
                  </a>
                  <div class="sr_title">
                    <?php echo $this->htmlLink($sitereview->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($sitereview->getTitle(), $this->title_truncationGrid), array('title' => $sitereview->getTitle())) ?>
                  </div>

                  <?php if(!empty($this->showContent) && in_array('endDate', $this->showContent)): ?>
                    <?php

                    $reviewApi = Engine_Api::_()->sitereview();
                    $expirySettings = $reviewApi->expirySettings($sitereview->listingtype_id);
                    $approveDate = null;
                    if ($expirySettings == 2):
                      $approveDate = $reviewApi->adminExpiryDuration($sitereview->listingtype_id);
                    endif;

                    ?>
                    <?php if ($expirySettings == 2): $exp=$sitereview->getExpiryTime();
                      $b = $exp ? $this->translate("Expiry On: %s",$this->locale()->toDate($exp, array('size'=>'medium'))) :'';
                      echo "<div class='sr_date seaocore_txt_light'>$b</div>";
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
                  <?php endif; ?>                

                  <div class="sr_category clr">
                    <?php if(!empty($sitereview->price) && $sitereview->price > 0 && Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id)->price && !empty($this->showContent) && in_array('price', $this->showContent)): ?>
                      <span class="fright">
                        <b><?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($sitereview->price); ?></b>
                      </span>
                    <?php endif; ?>
                    <a href="<?php echo $this->url(array('category_id' => $sitereview->category_id, 'categoryname' => $sitereview->getCategory()->getCategorySlug()), "sitereview_general_category_listtype_" . $sitereview->listingtype_id); ?>"> <?php echo $this->translate($sitereview->getCategory()->getTitle(true)) ?> </a>
                  </div>

                  <div class="sr_ratingbar seaocore_txt_light">
                    <?php $listingtypeArray = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id); ?>   
                    <?php if($this->statistics && in_array('reviewCount', $this->statistics) && ($listingtypeArray->reviews == 3 || $listingtypeArray->reviews == 2)):  ?>  
                        <span class="fright">
                          <?php echo $this->htmlLink($sitereview->getHref(), $this->partial(
                          '_showReview.tpl', 'sitereview', array('sitereview'=>$sitereview))); ?>
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
                      <?php if ($sitereview->sponsored == 1 ): ?>
                        <i title="<?php echo $this->translate('Sponsored');?>" class="sr_icon seaocore_icon_sponsored"></i>
                      <?php endif; ?>
                      <?php if ($sitereview->featured == 1 ): ?>
                        <i title="<?php echo $this->translate('Featured');?>" class="sr_icon seaocore_icon_featured"></i>
                      <?php endif; ?>
                      <?php echo $this->addToWishlist($sitereview, array('classIcon' => 'icon_wishlist_add', 'classLink' => 'sr_wishlist_link', 'text' => ''));?>
                    </span>
                  </div>
                </div>
              </li>
            <?php endforeach; ?>
             <?php else:?>
            <div class="tip">
                <span>
                  <?php if($this->listingtype_id > 0):?>
                  <?php $listingtypeArray = Zend_Registry::get('listingtypeArray' . $this->listingtype_id);?>
                  <?php echo $this->translate('No '.strtolower($listingtypeArray->title_plural).' have been posted yet.'); ?>
                  <?php else: ?>
                   <?php echo $this->translate("No listings have been posted yet."); ?>
                  <?php endif; ?>
                </span>
            </div>
            <?php endif; ?>
          </ul>
        </div>
      <?php endif; ?>
      <?php if ($this->enableLocation): ?>
        <div class="sr_container sr_map_view o_hidden" id="map_view_sr_<?php echo $this->listingtype_id; ?>" style="<?php echo $this->defaultLayout !== 'map_view' ? 'display: none;' : '' ?>">
        <div class="seaocore_map clr" style="overflow:hidden;">
          <div id="rmap_canvas_<?php echo $this->identity ?>" class="sr_list_map"> </div>
          <?php $siteTitle = Engine_Api::_()->getApi('settings', 'core')->core_general_site_title; ?>
          <?php if (!empty($siteTitle)) : ?>
          <div class="seaocore_map_info"><?php echo $this->translate("Locations on %s","<a href='' target='_blank'>$siteTitle</a>");?></div>
          <?php endif; ?>
        </div>	
          <a  href="javascript:void(0);" onclick="srToggleBounce(<?php echo $this->identity ?>)" class="fleft sr_list_map_bounce_link" style="<?php echo $this->flagSponsored ? '' : 'display:none' ?>"> <?php echo $this->translate('Stop Bounce'); ?></a>
        </div>
      <?php endif; ?>
      <div class="seaocore_view_more mtop10">
        <?php
        echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array(
            'id' => '',
            'class' => 'buttonlink icon_viewmore'
        ))
        ?>
      </div>
      <div class="seaocore_loading" id="" style="display: none;">
        <img src='<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/core/loading.gif' style='margin-right: 5px;' />
        <?php echo $this->translate("Loading ...") ?>
      </div>
      <?php if (empty($this->is_ajax)): ?>

      </div>
    </div>
    <script type="text/javascript">   
//      window.addEvent('load', function() {
//        var request = new Request.JSON({
//          url : en4.core.baseUrl + 'sitereview/index/get-listing-type',
//          data : {
//            format: 'json',
//            isAjax: 1,
//            type: 'layout_sitereview'
//          },
//          'onSuccess' : function(responseJSON) {
//            if( !responseJSON.getListingType ) {
//              document.getElement("." + responseJSON.getClassName + "recently_popular_random_sitereview").empty();
//            }
//          }
//        });
//        request.send();
//      });
      function sendAjaxRequestSitereview(params){
        var url = en4.core.baseUrl+'widget';

        if(params.requestUrl)
          url= params.requestUrl;

        var request = new Request.HTML({
          url : url,
          data : $merge(params.requestParams,{
            format : 'html',
            subject: en4.core.subject.guid,
            is_ajax:true
          }),
          evalScripts : true,
          onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
            if(params.requestParams.page ==1){
              params.responseContainer.empty();
              Elements.from(responseHTML).inject(params.responseContainer);
              <?php if ($this->enableLocation): ?>
              srInitializeMap(params.requestParams.content_id);
              <?php endif; ?>
            }else{
              var element= new Element('div', {      
                'html' : responseHTML  
              });
              params.responseContainer.getElements('.seaocore_loading').setStyle('display','none');

              if($$('.sr_list_view') && element.getElement('.sr_list_view'))    
                Elements.from(element.getElement('.sr_list_view').innerHTML).inject(params.responseContainer.getElement('.sr_list_view'));

              if($$('.sr_grid_view') && element.getElement('.sr_grid_view'))
                Elements.from(element.getElement('.sr_grid_view').innerHTML).inject(params.responseContainer.getElement('.sr_grid_view'));
            }
            en4.core.runonce.trigger();
            Smoothbox.bind(params.responseContainer);                                      
          } 
        });
        en4.core.request.send(request);
      }

      en4.core.runonce.add(function(){
        <?php if (count($this->tabs) > 1): ?>
          $$('.tab_li_<?php echo $this->identity ?>').addEvent('click',function(event){
            if( en4.core.request.isRequestActive() ) return;
            var element = $(event.target);
            if( element.tagName.toLowerCase() == 'a' ) {
              element = element.getParent('li');
            }
            var type=element.get('rel');                     
            var listingtype_id =element.getParent('ul').get('rel');
            element.getParent('ul').getElements('li').removeClass("active")
            element.addClass("active");
            var params={
              requestParams :<?php echo json_encode($this->params) ?>,
              responseContainer :$('dynamic_app_info_sr_'+'<?php echo $this->identity ?>')  
            }
            params.requestParams.listingtype_id = listingtype_id;
            params.requestParams.content_type = type;
            params.requestParams.page=1;
            params.requestParams.content_id='<?php echo $this->identity ?>';
            params.responseContainer.empty();
            new Element('div', {      
              'class' : 'seaocore_content_loader'      
            }).inject(params.responseContainer);
            sendAjaxRequestSitereview(params);
          });
        <?php endif; ?>
      });

      function srTabSwitchview(element){
        if( element.tagName.toLowerCase() == 'span' ) {
          element = element.getParent('li');
        }
        var type=element.get('rel');
        var listingtype_id =element.getParent('ul').get('rel');
        var identity =element.getParent('ul').get('identity');
        $('dynamic_app_info_sr_'+identity).getElements('.sr_container').setStyle('display','none');
        $('dynamic_app_info_sr_'+identity).getElement("#"+type+"_sr_"+listingtype_id).style.display='block';                                                                              
      }
    </script>

    <?php if ($this->enableLocation): ?>
      <?php $latitude = $this->settings->getSetting('sitereview.map.latitude', 0); ?>
      <?php $longitude = $this->settings->getSetting('sitereview.map.longitude', 0); ?>
      <?php $defaultZoom = $this->settings->getSetting('sitereview.map.zoom', 1); ?>
      <script type="text/javascript">
        // var rgmarkers = [];

        function srInitializeMap(element_id) {  
          en4.sitereview.maps[element_id]=[];
          en4.sitereview.maps[element_id]['markers']=[];
          // create the map
          var myOptions = {
            zoom: <?php echo $defaultZoom ?>,
            center: new google.maps.LatLng(<?php echo $latitude ?>,<?php echo $longitude ?>),
            navigationControl: true,
            mapTypeId: google.maps.MapTypeId.ROADMAP
          }

          en4.sitereview.maps[element_id]['map'] = new google.maps.Map(document.getElementById("rmap_canvas_"+element_id),myOptions);

          google.maps.event.addListener(en4.sitereview.maps[element_id]['map'], 'click', function() {
            en4.sitereview.maps[element_id]['infowindow'].close();
            google.maps.event.trigger(en4.sitereview.maps[element_id]['map'], 'resize');
            en4.sitereview.maps[element_id]['map'].setCenter(new google.maps.LatLng(<?php echo $latitude ?>,<?php echo $longitude ?>)); 
          });
          if($("map_view_"+element_id)) {
            $("map_view_"+element_id).addEvent('click',function(){
              en4.sitereview.maps[element_id]['infowindow'].close();
              google.maps.event.trigger(en4.sitereview.maps[element_id]['map'], 'resize');
              en4.sitereview.maps[element_id]['map'].setCenter(new google.maps.LatLng(<?php echo $latitude ?>,<?php echo $longitude ?>)); 
            });
          }
          if($("rmap_canvas_"+element_id)){
             $$("li.tab_"+element_id).addEvent('click',function(){
              google.maps.event.trigger(en4.sitereview.maps[element_id]['map'], 'resize');
              en4.sitereview.maps[element_id]['map'].setZoom( <?php echo $defaultZoom ?>);
              en4.sitereview.maps[element_id]['map'].setCenter(new google.maps.LatLng(<?php echo $latitude ?>,<?php echo $longitude ?>)); 
            });
          
       /*     $("rmap_canvas_"+element_id).addEvent('click',function(){
              google.maps.event.trigger(en4.sitereview.maps[element_id]['map'], 'resize');
              en4.sitereview.maps[element_id]['map'].setZoom( <?php echo $defaultZoom ?>);
              en4.sitereview.maps[element_id]['map'].setCenter(new google.maps.LatLng(<?php echo $latitude ?>,<?php echo $longitude ?>)); 
            });*/
          }

          en4.sitereview.maps[element_id]['infowindow']  = new google.maps.InfoWindow(
          {
            size: new google.maps.Size(250,50)
          });

        }

        function setSRMarker(element_id,latlng, bounce, html,title_list){
          var contentString = html;
          if(bounce ==0){
            var marker = new google.maps.Marker({
              position: latlng,
              map: en4.sitereview.maps[element_id]['map'],
              title:title_list,
              animation: google.maps.Animation.DROP,
              zIndex: Math.round(latlng.lat()*-100000)<<5
            });
          }
          else{
            var marker =new google.maps.Marker({
              position: latlng,
              map: en4.sitereview.maps[element_id]['map'],
              title:title_list,
              draggable: false,
              animation: google.maps.Animation.BOUNCE
            });
          }
          en4.sitereview.maps[element_id]['markers'].push(marker);  

          google.maps.event.addListener(marker, 'click', function() {
            en4.sitereview.maps[element_id]['infowindow'].setContent(contentString);
            google.maps.event.trigger(en4.sitereview.maps[element_id]['map'], 'resize');

            en4.sitereview.maps[element_id]['infowindow'].open(en4.sitereview.maps[element_id]['map'],marker);

          });
        }
        function srToggleBounce(element_id) {
          var markers= en4.sitereview.maps[element_id]['markers'];
          for(var i=0; i<markers.length;i++){
            if (markers[i].getAnimation() != null) {
              markers[i].setAnimation(null);
            }
          }
        }
        en4.core.runonce.add(function(){
          srInitializeMap("<?php echo $this->identity ?>");
        });
      </script>
    <?php endif; ?>
  <?php endif; ?>

  <script type="text/javascript">
    en4.core.runonce.add(function(){
      var view_more_content_sitereview=$('dynamic_app_info_sr_<?php echo $this->identity ?>').getElements('.seaocore_view_more');
      view_more_content_sitereview.setStyle('display', '<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() || $this->totalCount == 0 ? 'none' : '' ) ?>');

      view_more_content_sitereview.removeEvents('click');
      view_more_content_sitereview.addEvent('click',function(){
        if( en4.core.request.isRequestActive() ) return;
        var params={
          requestParams :<?php echo json_encode($this->params) ?>,
          responseContainer :$('dynamic_app_info_sr_'+<?php echo sprintf('%d', $this->identity) ?>)  
        }
        params.requestParams.listingtype_id = <?php echo sprintf('%d', $this->listingtype_id) ?>;
        params.requestParams.content_type = "<?php echo $this->content_type ?>";
        params.requestParams.page=<?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() + 1) ?>;
        params.requestParams.content_id='<?php echo $this->identity ?>';
        view_more_content_sitereview.setStyle('display','none');
        params.responseContainer.getElements('.seaocore_loading').setStyle('display','');

        sendAjaxRequestSitereview(params);
      });

    <?php if ($this->enableLocation): ?>
      <?php foreach ($this->locations as $location) : ?>
            <?php $listingtype_id = $this->locationsListing[$location->listing_id]->listingtype_id; ?>
            <?php $listing_singular_upper = strtoupper(Zend_Registry::get('listingtypeArray' . $listingtype_id)->title_singular); ?>  
            var point = new google.maps.LatLng(<?php echo $location->latitude ?>,<?php echo $location->longitude ?>);
            var contentString= "<?php
            echo $this->string()->escapeJavascript($this->partial('application/modules/Sitereview/views/scripts/_mapInfoWindowContent.tpl', array(
                  'sitereview' => $this->locationsListing[$location->listing_id],
                  'ratingValue' => $ratingValue,
                  'ratingType' => $ratingType,
                  'postedby' => $this->postedby,
                  'statistics' => $this->statistics,
                  'content_type' => $this->content_type,
                  'postedbytext' => $listing_singular_upper,
                  'showContent' => $this->showContent,
                  'ratingShow' => $ratingShow)), false);
      ?>";

        setSRMarker(<?php echo $this->identity ?>,point,<?php echo!empty($this->flagSponsored) ? $this->locationsListing[$location->listing_id]->sponsored : 0 ?>,contentString, "<?php echo $this->string()->escapeJavascript($this->locationsListing[$location->listing_id]->getTitle()) ?>");
      <?php endforeach; ?>
    <?php endif; ?>
    });
  </script>

<?php else: ?>

  <div id="layout_sitereview_recently_popular_random_listings_<?php echo $this->identity;?>">
<!--    <div class="seaocore_content_loader"></div>-->
  </div>

  <?php if($this->detactLocation): ?>
  <script type="text/javascript">
    var requestParams = $merge(<?php echo json_encode($this->paramsLocation);?>, {'content_id': '<?php echo $this->identity;?>'})
    var params = {
      'detactLocation': <?php echo $this->detactLocation; ?>,
      'responseContainer' : 'layout_sitereview_recently_popular_random_listings_<?php echo $this->identity;?>',
       requestParams: requestParams      
    };

    en4.seaocore.locationBased.startReq(params);
  </script>  
  <?php else: ?>
     <script type="text/javascript">
     window.addEvent('domready',function(){
         en4.sitereview.ajaxTab.sendReq({
            loading:true,
            requestParams:$merge(<?php echo json_encode($this->paramsLocation); ?>, {'content_id': '<?php echo $this->identity; ?>'}),
            responseContainer: [$('layout_sitereview_recently_popular_random_listings_<?php echo $this->identity; ?>')]
        });
        });
    </script>
  <?php endif; ?>

<?php endif; ?>
