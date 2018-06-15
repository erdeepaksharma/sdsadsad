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

<?php if( !empty($this->titleLink) ) : ?>
  <span class="fright sitereview_widgets_more_link mright5">
    <?php echo $this->titleLink; ?>
  </span>
<?php endif; ?>

<?php

$this->headLink()
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');

$this->headScript()
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/scripts/slideitmoo-1.1_full_source.js');
?>

<?php if($this->is_ajax_load): ?>
  <?php $settings = Engine_Api::_()->getApi('settings', 'core'); ?>

  <a id="" class="pabsolute"></a>
  <?php $navsPRE = 'Sr_SlideItMoo_' . $this->identity; ?>
  <?php if( !empty($this->showPagination) ) : ?>
  <script language="javascript" type="text/javascript">
    var slideshow;
  en4.core.runonce.add(function() {
        slideshow = new SlideItMoo({
          overallContainer: '<?php echo $navsPRE ?>_outer',
          elementScrolled: '<?php echo $navsPRE ?>_inner',
          thumbsContainer: '<?php echo $navsPRE ?>_items',
          thumbsContainerOuter: '<?php echo $navsPRE ?>_outer',
          itemsVisible:'<?php echo $this->limit; ?>',
          elemsSlide:'<?php echo $this->limit; ?>',
          duration:<?php echo $this->interval; ?>,
          itemsSelector: '<?php echo $this->vertical ? '.sr_carousel_content_item' : '.sr_carousel_content_item'; ?>' ,
          itemsSelectorLoading:'<?php echo $this->vertical ? 'sr_carousel_loader' : 'sr_carousel_loader'; ?>' ,
          itemWidth:<?php echo $this->vertical ? ($this->blockWidth) : ($this->blockWidth + 24); ?>,
          itemHeight:<?php echo ($this->blockHeight + 6) ?>,
          showControls:1,
          slideVertical: <?php echo $this->vertical ?>,
          startIndex:1,
          totalCount:'<?php echo $this->totalCount; ?>',
          contentstartIndex:-1,
          url:en4.core.baseUrl+'sitereview/index/homesponsored',

          params:{
            listingtype_id:'<?php echo $this->listingtype_id; ?>',
            vertical:<?php echo $this->vertical ?>,
            ratingType:'<?php echo $this->ratingType ?>',
            fea_spo:'<?php echo $this->fea_spo ?>',
            popularity:'<?php echo $this->popularity ?>',
            category_id:'<?php echo $this->category_id ?>',
            subcategory_id:'<?php echo $this->subcategory_id ?>',
            subsubcategory_id:'<?php echo $this->subsubcategory_id ?>',
            detactLocation:'<?php echo $this->detactLocation; ?>',
            defaultLocationDistance: '<?php echo $this->defaultLocationDistance; ?>',
            latitude: '<?php echo $this->latitude; ?>',
            longitude: '<?php echo $this->longitude; ?>',
            title_truncation:'<?php echo $this->title_truncation ?>',
            featuredIcon:'<?php echo $this->featuredIcon ?>',
            sponsoredIcon:'<?php echo $this->sponsoredIcon ?>',
            showOptions:<?php if($this->showOptions): echo  json_encode($this->showOptions); else: ?>  {'no':1} <?php endif;?>,
            blockHeight: '<?php echo $this->blockHeight ?>',
            blockWidth: '<?php echo $this->blockWidth ?>',
            newIcon:'<?php echo $this->newIcon ?>',
            showPagination: '<?php echo $this->showPagination ?>'
          },
          navs:{
            fwd:'<?php echo $navsPRE . ($this->vertical ? "_forward" : "_right") ?>',
            bk:'<?php echo $navsPRE . ($this->vertical ? "_back" : "_left") ?>'
          },
          transition: Fx.Transitions.linear, /* transition */
          onChange: function() { 
          }
        });
    });
  </script>
  <?php endif; ?>

  <?php
  $ratingValue = $this->ratingType;
  $ratingShow = 'small-star';
  if ($this->ratingType == 'rating_editor') {
    $ratingType = 'editor';
  } elseif ($this->ratingType == 'rating_avg') {
    $ratingType = 'overall';
  } else {
    $ratingType = 'user';
  }
  ?>

  <?php if ($this->vertical): ?> 
    <ul class="seaocore_sponsored_widget">
      <li>
        <?php $sitereview_advsitereview = true; ?>
        <div id="<?php echo $navsPRE ?>_outer" class="sr_carousel_vertical sr_carousel">
          <div id="<?php echo $navsPRE ?>_inner" class="sr_carousel_content b_medium" style="width:<?php echo $this->blockWidth + 2; ?>px;">
            <ul id="<?php echo $navsPRE ?>_items" class="sr_grid_view">
              <?php foreach ($this->listings as $sitereview): ?>
                <?php
                echo $this->partial(
                        'list_carousel.tpl', 'sitereview', array(
                    'sitereview' => $sitereview,
                    'title_truncation' => $this->title_truncation,
                    'ratingShow' => $ratingShow,
                    'ratingType' => $ratingType,
                    'ratingValue' => $ratingValue,
                    'vertical' => $this->vertical,
                    'featuredIcon' => $this->featuredIcon,
                    'sponsoredIcon' => $this->sponsoredIcon,
                    'showOptions' => $this->showOptions,
                    'blockHeight' => $this->blockHeight,
                    'blockWidth' => $this->blockWidth,
                    'newIcon' => $this->newIcon
                ));
                ?>	     
              <?php endforeach; ?>
            </ul>
          </div>
          <?php if( !empty($this->showPagination) ) : ?>
          <div class="sr_carousel_controller">
            <div class="sr_carousel_button sr_carousel_up" id="<?php echo $navsPRE ?>_back" style="display:none;">
              <i></i>
            </div>
            <div class="sr_carousel_button sr_carousel_up_dis" id="<?php echo $navsPRE ?>_back_dis" style="display:block;">
              <i></i>
            </div>

            <div class="sr_carousel_button sr_carousel_down fright" id ="<?php echo $navsPRE ?>_forward">
              <i></i>
            </div>
            <div class="sr_carousel_button sr_carousel_down_dis fright" id="<?php echo $navsPRE ?>_forward_dis" style="display:none;">
              <i></i>
            </div>
          </div>  
          <?php endif; ?>
          <div class="clr"></div>
        </div>
        <div class="clr"></div>
      </li>
    </ul>
  <?php else: ?>
    <div id="<?php echo $navsPRE ?>_outer" class="sr_carousel sr_carousel_horizontal" style="width: <?php echo (($this->limit <= $this->totalCount ? $this->limit : $this->totalCount) * ($this->blockWidth + 24)) + 60 ?>px; height: <?php echo ($this->blockHeight + 10) ?>px;">
      <?php if( !empty($this->showPagination) ) : ?>
      <div class="sr_carousel_button sr_carousel_left" id="<?php echo $navsPRE ?>_left" style="display:none;">
        <i></i>
      </div>
      <div class="sr_carousel_button sr_carousel_left_dis" id="<?php echo $navsPRE ?>_left_dis" style="display:<?php echo $this->limit < $this->totalCount ? "block;" : "none;" ?>">
        <i></i>
      </div>
      <?php endif; ?>
      <div id="<?php echo $navsPRE ?>_inner" class="sr_carousel_content" style="height: <?php echo ($this->blockHeight + 5) ?>px;">
        <ul id="<?php echo $navsPRE ?>_items" class="sr_grid_view">
          <?php $i = 0; ?>
          <?php foreach ($this->listings as $sitereview): ?>
            <?php
            echo $this->partial(
                    'list_carousel.tpl', 'sitereview', array(
                'sitereview' => $sitereview,
                'title_truncation' => $this->title_truncation,
                'ratingShow' => $ratingShow,
                'ratingType' => $ratingType,
                'ratingValue' => $ratingValue,
                'vertical' => $this->vertical,
                'featuredIcon' => $this->featuredIcon,
                'sponsoredIcon' => $this->sponsoredIcon,
                'showOptions' => $this->showOptions,
                'blockHeight' => $this->blockHeight,
                'blockWidth' => $this->blockWidth,
                'newIcon' => $this->newIcon
            ));
            ?>	
            <?php $i++; ?>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php if( !empty($this->showPagination) ) : ?>
      <div class="sr_carousel_button sr_carousel_right" id ="<?php echo $navsPRE ?>_right" style="display:<?php echo $this->limit < $this->totalCount ? "block;" : "none;" ?>">
        <i></i>
      </div>
      <div class="sr_carousel_button sr_carousel_right_dis" id="<?php echo $navsPRE ?>_right_dis" style="display:none;">
        <i></i>
      </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

<?php else: ?>

  <div id="layout_sitereview_sponsored_listings_<?php echo $this->identity;?>">
<!--    <div class="seaocore_content_loader"></div>-->
  </div>

  <script type="text/javascript">
    var requestParams = $merge(<?php echo json_encode($this->params);?>, {'content_id': '<?php echo $this->identity;?>'})
    var params = {
      'detactLocation': <?php echo $this->detactLocation; ?>,
      'responseContainer' : 'layout_sitereview_sponsored_listings_<?php echo $this->identity;?>',
       requestParams: requestParams      
    };

    en4.seaocore.locationBased.startReq(params);
  </script>  

<?php endif; ?>