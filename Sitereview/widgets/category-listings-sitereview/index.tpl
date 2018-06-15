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
	$this->headLink()->appendStylesheet($this->layout()->staticBaseUrl
  	              . 'application/modules/Seaocore/externals/styles/styles.css');
?>

<?php if($this->is_ajax_load): ?>
  <script type="text/javascript">

    function showListingPhoto(ImagePath, category_id, listing_id,href) {
      var elem = document.getElementById('listing_elements_'+category_id).getElementsByTagName('a'); 
      for(var i = 0; i < elem.length; i++)
      { 
        var cat_listingid = elem[i].id;
        $(cat_listingid).erase('class');
      }
      $('listing_link_class_'+listing_id).set('class', 'active');

      $('listingImage_'+category_id).src = ImagePath;
      $('listingImage_'+category_id).getParent('a').set('href',href);
    }

  </script>

  <ul class="seaocore_categories_box">
    <li> 
      <?php $ceil_count = 0; $k = 0; ?>
      <?php for ($i = 0; $i <= count($this->categories); $i++) { ?>
        <?php if($ceil_count == 0) :?>      
          <div>      
        <?php endif;?>  
        <div class="seaocore_categories_list_row">
          <?php $ceil_count++;?>				
          <?php $category = "";
            if (isset($this->categories[$k]) && !empty($this->categories[$k])): 
              $category = $this->categories[$k];
            endif;
            $k++;

            if (empty($category)) {
              break;
            }
          ?>

          <div class="seaocore_categories_list">
            <?php $total_subcat = Count($category['category_listings']); ?>
            <h6>
              <?php echo $this->htmlLink($this->url(array('category_id' => $category['category_id'], 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $category['category_id'])->getCategorySlug()), "sitereview_general_category_listtype_$this->listingtype_id"), $this->translate($category['category_name'])) ?>
            </h6>	
            <div class="sub_cat" id="subcat_<?php echo $category['category_id'] ?>">

              <?php $total_count = 1; ?>

              <?php foreach ($category['category_listings'] as $categoryListings) : ?>

                <?php 
                  $imageSrc = $categoryListings['imageSrc']; 
                  if(empty($imageSrc)) {
                    $imageSrc = $this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/nophoto_listing_thumb_icon.png';
                  }
                  $category_id = $category['category_id'];
                  $listing_id = $categoryListings['listing_id'];
                ?>
                <?php $sitereview = Engine_Api::_()->getItem('sitereview_listing', $categoryListings['listing_id']); ?>
                <?php if($total_count == 1): ?>
                  <div class="seaocore_categories_img" >
                    <a href='<?php echo $sitereview->getHref(array('profile_link' => 1)) ?>' ><img src="<?php echo $imageSrc; ?>" id="listingImage_<?php echo $category['category_id'] ?>" alt="" class="thumb_icon" /></a>
                  </div>
                  <div id='listing_elements_<?php echo $category_id;?>'>
                  <?php $href= $sitereview->getHref(array('profile_link' => 1));?>
                  <?php echo $this->htmlLink($sitereview->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($categoryListings['listing_title'], $this->title_truncation)." (".$categoryListings['populirityCount'].")", array('onmouseover' => "javascript:showListingPhoto('$imageSrc', '$category_id', '$listing_id','$href');",'title' => $categoryListings['listing_title'], 'class' => 'active', 'id' => "listing_link_class_$listing_id"));?>
                <?php else: ?> 
                  <?php $href= $sitereview->getHref(array('profile_link' => 1));?>
                  <?php echo $this->htmlLink($sitereview->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($categoryListings['listing_title'], $this->title_truncation)." (".$categoryListings['populirityCount'].")", array('onmouseover' => "javascript:showListingPhoto('$imageSrc', '$category_id', '$listing_id','$href');",'title' => $categoryListings['listing_title'], 'id' => "listing_link_class_$listing_id"));?>
                <?php endif; ?>

                <?php $total_count++; ?>
              <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
       <?php if($ceil_count %2 == 0) :?>      
       </div>
       <?php $ceil_count=0; ?>
       <?php endif;?>
      <?php } ?> 
    </li>	
  </ul>
<?php else: ?>

  <div id="layout_sitereview_category_listings_<?php echo $this->identity;?>">
<!--    <div class="seaocore_content_loader"></div>-->
  </div>

  <script type="text/javascript">
    var requestParams = $merge(<?php echo json_encode($this->params);?>, {'content_id': '<?php echo $this->identity;?>'})
    var params = {
      'detactLocation': <?php echo $this->detactLocation; ?>,
      'responseContainer' : 'layout_sitereview_category_listings_<?php echo $this->identity;?>',
       requestParams: requestParams      
    };

    en4.seaocore.locationBased.startReq(params);
  </script>  

<?php endif; ?>