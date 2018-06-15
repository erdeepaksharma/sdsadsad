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
          ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');

  $this->headScript()
          ->appendFile($this->layout()->staticBaseUrl."application/modules/Sitereview/externals/scripts/_class.noobSlide.packed.js");
  ?>

<?php if($this->is_ajax_load): ?>


  <?php
  $ratingValue = $this->ratingType;
  $ratingShow = 'small-star';
    if ($this->ratingType == 'rating_editor') {$ratingType = 'editor';} elseif ($this->ratingType == 'rating_avg') {$ratingType = 'overall';} else { $ratingType = 'user';}
  ?>

  <script type="text/javascript">
    en4.core.runonce.add(function() {
      if (document.getElementsByClassName == undefined) {
        document.getElementsByClassName = function(className)
        {
          var hasClassName = new RegExp("(?:^|\\s)" + className + "(?:$|\\s)");
          var allElements = document.getElementsByTagName("*");
          var results = [];

          var element;
          for (var i = 0; (element = allElements[i]) != null; i++) {
            var elementClass = element.className;
            if (elementClass && elementClass.indexOf(className) != -1 && hasClassName.test(elementClass))
              results.push(element);
          }

          return results;
        }
      }

      var width=$("featured_slideshow_wrapper<?php echo $this->identity ?>").clientWidth;
      $("featured_slideshow_mask<?php echo $this->identity ?>").style.width= (width-10)+"px";
      var divElements=$("featured_slideshow_mask<?php echo $this->identity ?>").getElements('.featured_slidebox');   
      for(var i=0;i < divElements.length;i++)
        divElements[i].style.width= (width-10)+"px";

      var handles8_more = $$('.handles8_more span');
      var num_of_slidehsow = "<?php echo $this->num_of_slideshow; ?>";
      var nS8 = new noobSlide({
        box: $('sitereview_featured_<?php echo $this->identity ?>_im_te_advanced_box'),
        items: $$('#sitereview_featured_<?php echo $this->identity ?>_im_te_advanced_box h3'),
        size: (width-10),
        handles: $$('#handles8 span'),
        addButtons: {previous: $('sitereview_featured_<?php echo $this->identity ?>_prev8'), stop: $('sitereview_featured_<?php echo $this->identity ?>_stop8'), play: $('sitereview_featured_<?php echo $this->identity ?>_play8'), next: $('sitereview_featured_<?php echo $this->identity ?>_next8') },
        interval: 5000,
        fxOptions: {
          duration: 500,
          transition: '',
          wait: false
        },
        autoPlay: true,
        mode: 'horizontal',
        onWalk: function(currentItem,currentHandle){

          // Finding the current number of index.
          var current_index = this.items[this.currentIndex].innerHTML;
          var current_start_title_index = current_index.indexOf(">");
          var current_last_title_index = current_index.indexOf("</span>");
          // This variable containe "Index number" and "Title" and we are finding index.
          var current_title = current_index.slice(current_start_title_index + 1, current_last_title_index);
          // Find out the current index id.
          var current_index = current_title.indexOf("_");
          // "current_index" is the current index.
          current_index = current_title.substr(0, current_index);

          // Find out the caption title.
          var current_caption_title = current_title.indexOf("_caption_title:") + 15;
          var current_caption_link = current_title.indexOf("_caption_link:");
          // "current_caption_title" is the caption title.
          current_caption_title = current_title.slice(current_caption_title, current_caption_link);
          var caption_title = current_caption_title;
          // "current_caption_link" is the caption title.
          current_caption_link = current_title.slice(current_caption_link + 14);

          var caption_title_lenght = current_caption_title.length;
          if( caption_title_lenght > 30 )
          {
            current_caption_title = current_caption_title.substr(0, 30) + '..';
          }

          if( current_caption_title != null && current_caption_link!= null )
          {
            $('sitereview_featured_<?php echo $this->identity ?>_caption').innerHTML =   current_caption_link;
          }
          else {
            $('sitereview_featured_<?php echo $this->identity ?>_caption').innerHTML =  '';
          }
          $('sitereview_featured_<?php echo $this->identity ?>_current_numbering').innerHTML =  current_index + '/' + "<?php echo $this->num_of_slideshow; ?>" ;
        }
      });

      //more handle buttons
      nS8.addHandleButtons(handles8_more);
      //walk to item 3 witouth fx
      nS8.walk(0,false,true);
    });
  </script>

  <div class="featured_slideshow_wrapper" id="featured_slideshow_wrapper<?php echo $this->identity ?>">
    <div class="featured_slideshow_mask" id="featured_slideshow_mask<?php echo $this->identity ?>" style="height:200px;">
      <div id="sitereview_featured_<?php echo $this->identity ?>_im_te_advanced_box" class="featured_slideshow_advanced_box">

        <?php $image_count = 1; ?>
        <?php foreach ($this->show_slideshow_object as $type => $item): ?>

          <?php Engine_Api::_()->sitereview()->setListingTypeInRegistry($item->listingtype_id);
              $listingType = Zend_Registry::get('listingtypeArray' . $item->listingtype_id);?>

          <?php $listing_singular_uc = ucfirst(Zend_Registry::get('listingtypeArray' . $item->listingtype_id)->title_singular)?>
          <div class='featured_slidebox' style="height:200px;">
            <div class='featured_slidshow_img'> 
              <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)):?>             
                <?php if (!empty($item->featured) && !empty($this->featuredIcon)): ?> 
                  <i class="sr_list_featured_label" title="<?php echo $this->translate('Featured'); ?>"></i>
                <?php endif; ?>
                <?php if (!empty($item->newlabel) && !empty($this->newIcon)): ?> 
                  <i class="sr_list_new_label" title="<?php echo $this->translate('New'); ?>"></i>
                <?php endif; ?>
              <?php endif; ?>

              <?php echo $this->htmlLink($item->getHref(array('profile_link' => 1)), $this->itemPhoto($item, 'thumb.profile')); ?>  

              <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)):?>
                <?php if (  !empty($item->sponsored) && !empty($this->sponsoredIcon)): ?>
                  <div class="sr_list_sponsored_label" style="background: <?php echo $listingType->sponsored_color; ?>">
                    <?php echo $this->translate('SPONSORED'); ?>                 
                  </div>
                <?php endif; ?>
              <?php endif; ?>

            </div>
            <div class='featured_slidshow_content'>
              <?php $tmpBody = strip_tags($item->title);
              $title = ( Engine_String::strlen($tmpBody) > $this->title_truncation ? Engine_String::substr($tmpBody, 0, $this->title_truncation) . '..' : $tmpBody ); ?>
              <?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)) :?>
                <span class="fright mtop5">              
                  <?php if (!empty($item->sponsored) && !empty($this->sponsoredIcon)): ?>
                    <i title="<?php echo $this->translate('Sponsored');?>" class="sr_icon seaocore_icon_sponsored"></i>
                  <?php endif; ?>
                  <?php if ( !empty($item->featured) && !empty($this->featuredIcon)): ?> 
                    <i title="<?php echo $this->translate('Featured');?>" class="sr_icon seaocore_icon_featured"></i>
                  <?php endif; ?>
                </span>    
              <?php endif; ?> 
              <h5 class="o_hidden"> <?php echo $this->htmlLink($item->getHref(), $title, array('title' => $item->getTitle())) ?></h5>
              <h3 style='display:none'><span><?php echo $image_count++ . '_caption_title:' . $item->title . '_caption_link:' . $this->htmlLink($item->getHref(), $this->translate("View $listing_singular_uc &raquo;"), array('class' => 'featured_slideshow_view_link','title' => $item->getTitle())) . '</span>' ?></h3>

              <span class='featured_slidshow_info'>
                 <?php if (empty($this->category_id)): ?>
                  <p>
                  <a href="<?php echo $this->url(array('category_id' => $item->category_id, 'categoryname' => $item->getCategory()->getCategorySlug()), "sitereview_general_category_listtype_" . $item->listingtype_id); ?>"> 
                      <?php echo $this->translate($item->getCategory()->getTitle(true)) ?>
                    </a>
                  </p>
                <?php endif; ?>
                  
                  <div class="sr_browse_list_info_footer o_hidden mtop5 clr">
                <?php echo $this->compareButton($item); ?>
                <?php echo $this->addToWishlist($item, array('classIcon' => 'sr_wishlist_href_link', 'text' => 'Add To Wishlist'));?>
              </div>
                <?php if(!empty($this->statistics)): ?>  
                  <p>

                    <?php 

                      $statistics = '';

                      if(in_array('commentCount', $this->statistics)) {
                        $statistics .= $this->translate(array('%s comment', '%s comments', $item->comment_count), $this->locale()->toNumber($item->comment_count)).', ';
                      }

                      $listingtypeArray = Zend_Registry::get('listingtypeArray' . $item->listingtype_id);
                      if(in_array('reviewCount', $this->statistics) && ($listingtypeArray->reviews == 3 || $listingtypeArray->reviews == 2) && (!empty($listingtypeArray->allow_review) || (isset($item->rating_editor) && $item->rating_editor))) {
                        $statistics .= $this->partial(
                        '_showReview.tpl', 'sitereview', array('sitereview'=>$item)).', ';
                      }

                      if(in_array('viewCount', $this->statistics)) {
                        $statistics .= $this->translate(array('%s view', '%s views', $item->view_count), $this->locale()->toNumber($item->view_count)).', ';
                      }

                      if(in_array('likeCount', $this->statistics)) {
                        $statistics .= $this->translate(array('%s like', '%s likes', $item->like_count), $this->locale()->toNumber($item->like_count)).', ';
                      }                 

                      $statistics = trim($statistics);
                      $statistics = rtrim($statistics, ',');

                    ?>

                    <?php echo $statistics; ?>
                  </p>
                <?php endif; ?>
                  
                <?php

                $reviewApi = Engine_Api::_()->sitereview();
                $expirySettings = $reviewApi->expirySettings($item->listingtype_id);
                $approveDate = null;
                if ($expirySettings == 2):
                  $approveDate = $reviewApi->adminExpiryDuration($item->listingtype_id);
                endif;

                ?>  
                  
                <?php if($this->showExpiry):?>
                  <?php if ($expirySettings == 2): $exp=$item->getExpiryTime(); 
                    echo '<p>' . $exp ? $this->translate("Expiry On: %s",$this->locale()->toDate($exp, array('size'=>'medium'))) :'' . '</p>';
                    $now = new DateTime(date("Y-m-d H:i:s"));
                    $ref = new DateTime($this->locale()->toDate($exp));
                    $diff = $now->diff($ref);
                    echo '<p>';
                    echo $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i);
                    echo '</p>';

                  elseif ($expirySettings == 1 && $item->end_date && $item->end_date !='0000-00-00 00:00:00'):
                    echo '<p>' . $this->translate("Ending On: %s",$this->locale()->toDate(strtotime($item->end_date), array('size'=>'medium'))) . '</p>';
                        $now = new DateTime(date("Y-m-d H:i:s"));
                        $ref = new DateTime($item->end_date);
                        $diff = $now->diff($ref);
                        echo '<p>';
                        echo $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i);
                        echo '</p>';

                  endif;?>
                <?php endif; ?>                  
                  
                <?php if ($ratingValue == "rating_both") : ?>
                  <?php echo $this->showRatingStar($item->rating_editor, 'editor', $ratingShow, $item->listingtype_id); ?>
                  <br/>
                  <?php echo $this->showRatingStar($item->rating_users, 'user', $ratingShow, $item->listingtype_id); ?>

                  <?php
                else:
                  echo $this->showRatingStar($item->$ratingValue, $ratingType, $ratingShow, $item->listingtype_id);
                endif;
                ?>
              </span>
              <?php if (!empty($item->body)) : ?>
                <?php $item->body = strip_tags($item->body);?>
                <p class="clr"> <?php echo ( Engine_String::strlen($item->body) > 253 ? Engine_String::substr($item->body, 0, 150) . '...' : $item->body ) . ' ' . $this->htmlLink($item->getHref(), $this->translate('More &raquo;')) ?></p>

              <?php endif; ?>
              
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="featured_slideshow_option_bar">
      <div>
        <p class="buttons" style="<?php if($image_count<=2): echo "display:none;"; endif;?>">
          <span id="sitereview_featured_<?php echo $this->identity ?>_prev8" class="featured_slideshow_controllers-prev featured_slideshow_controllers prev" title="Previous" ></span>
          <span id="sitereview_featured_<?php echo $this->identity ?>_stop8" class="featured_slideshow_controllers-stop featured_slideshow_controllers" title="Stop"></span>
          <span id="sitereview_featured_<?php echo $this->identity ?>_play8" class="featured_slideshow_controllers-play featured_slideshow_controllers" title="Play"></span>
          <span id="sitereview_featured_<?php echo $this->identity ?>_next8" class="featured_slideshow_controllers-next featured_slideshow_controllers" title="Next" ></span>
        </p>
      </div>
      <span id="sitereview_featured_<?php echo $this->identity ?>_caption"></span>
      <span id="sitereview_featured_<?php echo $this->identity ?>_current_numbering" class="featured_slideshow_pagination" style="<?php if($image_count<=2): echo "display:none;"; endif;?>"></span>
    </div>
  </div>
  <?php else: ?>

  <div id="layout_sitereview_slideshow_sitereview_<?php echo $this->identity;?>">
<!--    <div class="seaocore_content_loader"></div>-->
  </div>

  <script type="text/javascript">
    var requestParams = $merge(<?php echo json_encode($this->params);?>, {'content_id': '<?php echo $this->identity;?>'})
    var params = {
      'detactLocation': <?php echo $this->detactLocation; ?>,
      'responseContainer' : 'layout_sitereview_slideshow_sitereview_<?php echo $this->identity;?>',
       requestParams: requestParams      
    };

    en4.seaocore.locationBased.startReq(params);
  </script>  

<?php endif; ?>
