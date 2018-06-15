<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: manage.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>
<?php
//GET API KEY
$apiKey = Engine_Api::_()->seaocore()->getGoogleMapApiKey();
$this->headScript()->appendFile("http://maps.googleapis.com/maps/api/js?libraries=places&key=$apiKey");
?>
<script type="text/javascript">
  
  window.addEvent('domready', function() {
		new google.maps.places.Autocomplete(document.getElementById('location'));
  });
</script>

<?php $listing_title_plural = $this->listingtypeArray->title_plural; ?>
<?php $listing_title_singular = $this->listingtypeArray->title_singular; ?>
<?php $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css'); ?>
<?php	$this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/styles/styles.css');?>
<?php
$reviewApi = Engine_Api::_()->sitereview();
$expirySettings = $reviewApi->expirySettings($this->listingtype_id);
$approveDate = null;
if ($expirySettings == 2):
  $approveDate = $reviewApi->adminExpiryDuration($this->listingtype_id);
endif;
?>
<style type="text/css">
  .sitereview_browse_list_info_expiry{
    color:red;
  }
</style>
<script type="text/javascript">
  var pageAction =function(page){
    $('page').value = page;
    $('filter_form').submit();
  }
  var searchSitereviews = function() {

    var formElements = $('filter_form').getElements('li');
    formElements.each( function(el) {
      var field_style = el.style.display;
      if(field_style == 'none') {
        el.destroy();
      }
    });

    if( Browser.Engine.trident ) {
      document.getElementById('filter_form').submit();
    } else {
      $('filter_form').submit();
    }
  }
  en4.core.runonce.add(function(){
    $$('#filter_form input[type=text]').each(function(f) {
      if (f.value == '' && f.id.match(/\min$/)) {
        new OverText(f, {'textOverride':'min','element':'span'});
        //f.set('class', 'integer_field_unselected');
      }
      if (f.value == '' && f.id.match(/\max$/)) {
        new OverText(f, {'textOverride':'max','element':'span'});
        //f.set('class', 'integer_field_unselected');
      }
    });
  });

  window.addEvent('onChangeFields', function() {
    var firstSep = $$('li.browse-separator-wrapper')[0];
    var lastSep;
    var nextEl = firstSep;
    var allHidden = true;
    do {
      nextEl = nextEl.getNext();
      if(nextEl.get('class') == 'browse-separator-wrapper' || nextEl.get('class') == 'browse-range-wrapper') {
        lastSep = nextEl;
        nextEl = false;
      } else {
        allHidden = allHidden && ( nextEl.getStyle('display') == 'none' );
      }
    } while( nextEl );
    if( lastSep ) {
      lastSep.setStyle('display', (allHidden ? 'none' : ''));
    }
  });
</script>
<?php
/* Include the common user-end field switching javascript */
echo $this->partial('_jsSwitch.tpl', 'fields', array(
        //'topLevelId' => (int) @$this->topLevelId,
        //'topLevelValue' => (int) @$this->topLevelValue
))
?>

<?php //include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/navigation_views.tpl'; ?>

<div class='layout_right'>
 <?php if((!empty($this->claimLink)) && ($this->claimListing->getTotalItemCount() > 0)): ?>
   <div class="quicklinks sitereview_claim_listing_link">
    <ul>
      <li>
       <a href='<?php echo $this->url(array('action' => 'my-listings'), 'sitereview_claim_listtype_'.$this->listingtype_id, true) ?>' class="icon_sitereviews_claim buttonlink"> <?php echo $this->translate("$this->listing_plural_uc I Have Claimed") ?></a>
      </li>				
    </ul>		
   </div>	
  <?php endif; ?>
	<div class="seaocore_searchform_criteria">
	  <?php echo $this->form->render($this) ?>
	</div>  

  <div class="quicklinks">
    <ul class="navigation">
      <li>
        <?php if (Engine_Api::_()->sitereview()->hasPackageEnable()):?>
					<a href='<?php echo $this->url(array('action' => 'index'), "sitereview_package_listtype_$this->listingtype_id", true) ?>' class="buttonlink seaocore_icon_add icon_sitereview_add_listtype_<?php echo $this->listingtype_id?>"><?php echo $this->translate("Post a New ". $this->listing_singular_uc); ?></a> 
        <?php else:?>
          <a href='<?php echo $this->url(array('action' => 'create'), "sitereview_general_listtype_$this->listingtype_id", true) ?>' class="buttonlink seaocore_icon_add icon_sitereview_add_listtype_<?php echo $this->listingtype_id?>"><?php echo $this->translate("Post a New ". $this->listing_singular_uc); ?></a> 
        <?php endif;?>
      </li>
    </ul>
  </div>
</div>

<div class='layout_middle'>
  <?php $sitereview_approved = true;
  $renew_date = date('Y-m-d', mktime(0, 0, 0, date("m"), date('d', time()) + (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.renew.email', 2)))); ?>
  <?php if ($this->current_count >= $this->quota && !empty($this->quota)): ?>
    <div class="tip"> 
      <span><?php echo $this->translate("You have already created the maximum number of $this->listing_plural_lc allowed. If you would like to create a new $this->listing_singular_lc, please delete an old one first."); ?> </span> 
    </div>
    <br/>
  <?php endif; ?>

  <?php if ($this->paginator->getTotalItemCount() > 0): ?>
    <ul class="sr_browse_list">
      <?php foreach ($this->paginator as $item): ?>
        <?php Engine_Api::_()->sitereview()->setListingTypeInRegistry($item->listingtype_id);
                $listingType = Zend_Registry::get('listingtypeArray' . $item->listingtype_id);?>
        <li class="b_medium">
          <div class='sr_browse_list_photo b_medium'>
          	<?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)):?>
							<?php if($item->featured):?>
								<i class="sr_list_featured_label" title="<?php echo $this->translate('Featured'); ?>"></i>
              <?php endif;?>
							<?php if($item->newlabel):?>
								<i class="sr_list_new_label" title="<?php echo $this->translate('New'); ?>"></i>
							<?php endif;?>
						<?php endif;?>
						
            <?php echo $this->htmlLink($item->getHref(array('profile_link' => 1)), $this->itemPhoto($item, 'thumb.normal', '', array('align' => 'center'))) ?>
            
						<?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)):?>
							<?php if (!empty($item->sponsored)): ?>
									<div class="sr_list_sponsored_label" style="background: <?php echo $listingType->sponsored_color; ?>">
										<?php echo $this->translate('SPONSORED'); ?>                 
									</div>
							<?php endif; ?>
						<?php endif; ?>
          </div>
          <div class='sr_browse_list_options'>

            <?php if ($this->can_edit): ?>
              <a href='<?php echo $this->url(array('action' => 'edit', 'listing_id' => $item->listing_id), "sitereview_specific_listtype_$this->listingtype_id", true) ?>' class='buttonlink seaocore_icon_edit'><?php if (!empty($sitereview_approved)) {
              echo $this->translate("Dashboard");
              } else {
              echo $this->translate($this->listing_manage);
              } ?></a>
            <?php endif; ?>
              
            <?php
                $uploadPhoto = Engine_Api::_()->authorization()->isAllowed($item, $this->viewer, "photo_listtype_$item->listingtype_id");
                if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
                    $package = Engine_Api::_()->getItem('sitereviewpaidlisting_package', $item->package_id);
                    $album = $item->getSingletonAlbum();
                    $paginator = $album->getCollectiblesPaginator();
                    $total_images = $paginator->getTotalItemCount();        
                  if (empty($package->photo_count))
                    $allowed_upload_photo = $uploadPhoto;
                  elseif ($package->photo_count > $total_images)
                    $allowed_upload_photo = $uploadPhoto;
                  else
                    $allowed_upload_photo = 0;
                }
                else {
                  $allowed_upload_photo = $uploadPhoto;            
                }
            ?>

            <?php if ($allowed_upload_photo): ?>
              <a href='<?php echo $this->url(array('listing_id' => $item->listing_id), "sitereview_albumspecific_listtype_$this->listingtype_id", true) ?>' class='buttonlink icon_sitereviews_photo_new'><?php echo $this->translate('Add Photos'); ?></a>
            <?php endif; ?>

            <?php if ($this->allowed_upload_video): ?>
              <a href='<?php echo $this->url(array('listing_id' => $item->listing_id), "sitereview_videospecific_listtype_$this->listingtype_id", true) ?>' class='buttonlink icon_sitereviews_video_new'><?php if (!empty($sitereview_approved)) {
              echo $this->translate('Add Videos');
              } else {
              echo $this->translate($this->listing_manage);
              } ?></a>
            <?php endif; ?>

            <?php if ($item->draft == 1 && $this->can_edit)
              echo $this->htmlLink(array('route' => "sitereview_specific_listtype_$this->listingtype_id", 'action' => 'publish', 'listing_id' => $item->listing_id), $this->translate("Publish $this->listing_singular_uc"), array(
                  'class' => 'buttonlink smoothbox icon_sitereview_publish')) ?> 

            <?php if (!$item->closed && $this->can_edit): ?>
              <a href='<?php echo $this->url(array('action' => 'close', 'listing_id' => $item->listing_id), "sitereview_specific_listtype_$this->listingtype_id", true) ?>' class='buttonlink icon_sitereviews_close'><?php echo $this->translate("Close $this->listing_singular_uc"); ?></a>
            <?php elseif ($this->can_edit): ?>
              <a href='<?php echo $this->url(array('action' => 'close', 'listing_id' => $item->listing_id), "sitereview_specific_listtype_$this->listingtype_id", true) ?>' class='buttonlink icon_sitereviews_open'><?php echo $this->translate("Open $this->listing_singular_uc"); ?></a>
            <?php endif; ?>

            <?php if ($this->can_delete): ?>
              <a href='<?php echo $this->url(array('action' => 'delete', 'listing_id' => $item->listing_id), "sitereview_specific_listtype_$this->listingtype_id", true) ?>' class='buttonlink seaocore_icon_delete'><?php echo $this->translate("Delete $this->listing_singular_uc"); ?></a>
            <?php endif; ?>
            
            <?php if(Engine_Api::_()->sitereview()->hasPackageEnable()):?>
							<?php if (Engine_Api::_()->sitereviewpaidlisting()->canShowPaymentLink($item->listing_id)): ?>
								<div class="tip">
									<span>
										<a href='javascript:void(0);' onclick="submitSession(<?php echo $item->listing_id ?>)"><?php echo $this->translate('Make Payment'); ?></a>
									</span>
								</div>
							<?php endif; ?>

							<?php if (Engine_Api::_()->sitereviewpaidlisting()->canShowRenewLink($item->listing_id)): ?>
								<div class="tip">
									<span>
										<a href='javascript:void(0);' onclick="submitSession(<?php echo $item->listing_id ?>)"><?php echo $this->translate("Renew $this->listing_singular_uc"); ?></a>
									</span>
								</div>
							<?php endif; ?>
						<?php endif;?>
          </div>

          <div class='sr_browse_list_info'>
            <div class='sr_browse_list_info_header o_hidden'>
              <div class="sr_list_title"> 
              	<?php if (empty($item->approved)): ?>
                	<i title="<?php echo $this->translate('Not approved');?>" class="sr_icon seaocore_icon_disapproved fright"></i>
								<?php endif; ?>
              	<?php echo $this->htmlLink($item->getHref(), $item->getTitle()) ?>
              </div>	
            </div>

            <div class='sr_browse_list_info_stat seaocore_txt_light'>
              <?php echo $this->timestamp(strtotime($item->creation_date)) ?> - <?php echo $this->translate(strtoupper($this->listingtypeArray->title_singular). '_posted_by'); ?>
              <?php echo $this->htmlLink($item->getOwner()->getHref(), $item->getOwner()->getTitle()) ?>,
            <?php echo $this->translate(array('%s comment', '%s comments', $item->comment_count), $this->locale()->toNumber($item->comment_count)) ?>,
            
            <?php if($this->listingtypeArray->reviews == 3 || $this->listingtypeArray->reviews == 2): ?>
              <?php echo $this->partial('_showReview.tpl', 'sitereview', array('sitereview' => $item)) ?>,
            <?php endif; ?>        
                    
            <?php echo $this->translate(array('%s view', '%s views', $item->view_count), $this->locale()->toNumber($item->view_count)) ?>,
            <?php echo $this->translate(array('%s like', '%s likes', $item->like_count), $this->locale()->toNumber($item->like_count)) ?>
            </div>
            <?php if($approveDate && $approveDate > $item->approved_date):?>
            <div class="sitereview_browse_list_info_expiry">
              <?php echo $this->translate('Expired');?>
            </div>
            <?php elseif($expirySettings == 2 && $approveDate && $approveDate < $item->approved_date):?>
              <?php $exp = $item->getExpiryTime();?>
							<div class='seaocore_browse_list_info_date clear'>
							<?php echo $exp ? $this->translate("Expiry On: %s", $this->locale()->toDate($exp, array('size' => 'medium'))) : ''; ?>
							</div>
            <?php elseif($expirySettings == 1):?> 
              <div class="seaocore_browse_list_info_date clear">
								<?php $current_date = date("Y-m-d i:s:m", time());?>
               <?php if(!empty($item->end_date)  && $item->end_date !='0000-00-00 00:00:00'):?>
								<?php if($item->end_date >= $current_date):?>
									 <?php echo $this->translate("Ending On: %s", $this->locale()->toDate(strtotime($item->end_date), array('size' => 'medium'))); ?>
								<?php else:?>
									<?php echo $this->translate("Ending On: %s", 'Expired', array('size' => 'medium')); ?>
									<?php echo $this->translate('(You can edit the end date to make the '.$this->listing_singular_lc.' live again.)');?>
								<?php endif;?>
                <?php endif;?>
              </div>
            <?php endif; ?>
            
            <?php if (!empty($item->location)): ?>
              <div class='sr_browse_list_info_stat seaocore_txt_light'>
                <?php echo $this->translate($item->location); ?>
                - <b><?php echo  $this->htmlLink(array('route' => 'seaocore_viewmap', "id" => $item->listing_id, 'resouce_type' => 'sitereview_listing'), $this->translate("Get Directions"), array('class' => 'smoothbox')) ; ?></b>
              </div>
            <?php endif; ?>

						<?php if(Engine_Api::_()->sitereview()->hasPackageEnable()):?>
							<div class='seaocore_browse_list_info_date clr'>
								<?php echo $this->translate('Package: ') ?>           
								<a href='<?php echo $this->url(array("action"=>"detail" ,'id' => $item->package_id), "sitereview_package_listtype_$item->listingtype_id", true) ?>' class='smoothbox' onclick="owner(this);return false;" title="<?php echo $this->translate(ucfirst($item->getPackage()->title)) ?>"><?php echo $this->translate(ucfirst($item->getPackage()->title)); ?>
								</a>
							</div>
						<?php endif; ?>
						<div class='seaocore_browse_list_info_date'>
						<?php if(Engine_Api::_()->sitereview()->hasPackageEnable()):?>
							<?php if(!$item->getPackage()->isFree()):  ?>
								<span>
									<?php echo $this->translate('Payment: ')?>
									<?php if($item->status=="initial"):
										echo $this->translate("Not made");
									elseif($item->status=="active"):
										echo $this->translate("Yes");
									else:
										echo $this->translate(ucfirst($item->status));
									endif;
									?>
								</span>
								<?php if(!empty($item->approved_date)): ?>
								|
								<?php endif; ?>
							<?php endif; ?>
						<?php endif;?>
      <?php if(Engine_Api::_()->sitereview()->hasPackageEnable()):?>
        <?php if(!empty($item->approved_date)): ?>
         <span style="color: chocolate;"><?php echo $this->translate('First Approved on '). $this->timestamp(strtotime($item->approved_date)) ?></span>
         |
         <span style="color: green;">
         <?php $expiry= $item->getExpiryDate();
         if($expiry !=="Expired" && $expiry !== $this->translate('Never Expires'))
         echo $this->translate("Expiration Date: ");
         echo $expiry;
         ?>
         </span>
        <?php endif;?>
						<?php endif ?>
						</div>
            
            <div class='sr_browse_list_info_blurb'>
			        <?php
			        echo substr(strip_tags($item->body), 0, 350);
			        if (strlen($item->body) > 349)
			          echo "...";
			        ?>
            </div>
            <div class="sr_browse_list_info_footer clr o_hidden">
            	<span class="sr_browse_list_info_footer_icons">
								<?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)) :?>   
									<?php if (!empty($item->sponsored)): ?>
										<i title="<?php echo $this->translate('Sponsored');?>" class="sr_icon seaocore_icon_sponsored"></i>
									<?php endif; ?>
									
									<?php if (!empty($item->featured)): ?>
										<i title="<?php echo $this->translate('Featured');?>" class="sr_icon seaocore_icon_featured"></i>
									<?php endif; ?>
								<?php endif; ?>	
								
								<?php if ($item->closed): ?>
									<i title="<?php echo $this->translate('Closed');?>" class="sr_icon icon_sitereviews_close"></i>
								<?php endif; ?>
            	</span>
            </div>
          </div>
        </li>
    <?php endforeach; ?>
    </ul>
      <?php elseif ($this->search): ?>
    <div class="tip"> 
      <span>
  <?php if (!empty($sitereview_approved)) {
    echo $this->translate('You do not have any '.strtolower($listing_title_singular).' that match your search criteria.');
  } else {
    echo $this->translate($this->listing_manage_msg);
  } ?> 
      </span> 
    </div>
<?php else: ?>
    <div class="tip">
      <span> 
  <?php if (!empty($sitereview_approved)) {
    echo $this->translate('You do not have any '.strtolower($listing_title_plural).'.');
  } else {
    echo $this->translate($this->listing_manage_msg);
  } ?>
    <?php if (Engine_Api::_()->sitereview()->hasPackageEnable()):?>
			<?php echo $this->translate('Get started by %1$sposting%2$s a new '.strtolower($listing_title_singular).'.', '<a href="' . $this->url(array('action' => 'index'), "sitereview_package_listtype_$this->listingtype_id") . '">', '</a>'); ?>
		<?php else:?>
		  <?php echo $this->translate('Get started by %1$sposting%2$s a new '.strtolower($listing_title_singular).'.', '<a href="' . $this->url(array('action' => 'create'), "sitereview_general_listtype_$this->listingtype_id") . '">', '</a>'); ?>
		<?php endif;?>
      </span> 
    </div>
<?php endif; ?>
<?php echo $this->paginationControl($this->paginator, null, null, array('query' => $this->formValues,'pageAsQuery' => true,)); ?>  

<?php if(Engine_Api::_()->sitereview()->hasPackageEnable()):?>
	<form name="setSession_form" method="post" id="setSession_form" action="<?php echo $this->url(array(), "sitereview_session_payment_$this->listingtype_id", true) ?>">
			<input type="hidden" name="listing_id_session" id="listing_id_session" />
	</form>
<?php endif;?>
</div>

<script type="text/javascript">
  
  var profile_type = 0;
  var previous_mapped_level = 0;  
  var sitereview_categories_slug = <?php echo json_encode($this->categories_slug); ?>;
  function showFields(cat_value, cat_level) {
       
    if(cat_level == 1 || (previous_mapped_level >= cat_level && previous_mapped_level != 1) || (profile_type == null || profile_type == '' || profile_type == 0)) {
      profile_type = getProfileType(cat_value); 
      if(profile_type == 0) { profile_type = ''; } else { previous_mapped_level = cat_level; }
      $('profile_type').value = profile_type;
      changeFields($('profile_type'));      
    }
  }
  
  $('filter_form').getElement('.browsesitereviews_criteria').addEvent('keypress', function(e){   
    if( e.key != 'enter' ) return;
    searchSitereviews();
  });

  var getProfileType = function(category_id) {
    var mapping = <?php echo Zend_Json_Encoder::encode(Engine_Api::_()->getDbTable('categories', 'sitereview')->getMapping($this->listingtype_id, 'profile_type')); ?>;
    for(i = 0; i < mapping.length; i++) {
      if(mapping[i].category_id == category_id)
        return mapping[i].profile_type;
    }
    return 0;
  }

 function addOptions(element_value, element_type, element_updated, domready) {

    var element = $(element_updated);
    if(domready == 0){
      switch(element_type){    
        case 'cat_dependency':
          $('subcategory_id'+'-wrapper').style.display = 'none';
          clear($('subcategory_id'));
          $('subcategory_id').value = 0;
          $('categoryname').value = sitereview_categories_slug[element_value];
  
        case 'subcat_dependency':
          $('subsubcategory_id'+'-wrapper').style.display = 'none';
          clear($('subsubcategory_id'));
          $('subsubcategory_id').value = 0;
          $('subsubcategoryname').value = '';
          if(element_type=='subcat_dependency')
            $('subcategoryname').value = sitereview_categories_slug[element_value];
          else
            $('subcategoryname').value='';
      }
    }
    
    if(element_value <= 0) return;  
   
    var url = '<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'review', 'action' => 'categories'), "default", true); ?>';
    en4.core.request.send(new Request.JSON({      	
      url : url,
      data : {
        format : 'json',
        element_value : element_value,
        element_type : element_type
      },

      onSuccess : function(responseJSON) {
        var categories = responseJSON.categories;
        var option = document.createElement("OPTION");
        option.text = "";
        option.value = 0;
        element.options.add(option);
        for (i = 0; i < categories.length; i++) {
          var option = document.createElement("OPTION");
          option.text = categories[i]['category_name'];
          option.value = categories[i]['category_id'];
          element.options.add(option);
          sitereview_categories_slug[categories[i]['category_id']]=categories[i]['category_slug'];
        }

        if(categories.length  > 0 )
          $(element_updated+'-wrapper').style.display = 'block';
        else
          $(element_updated+'-wrapper').style.display = 'none';
        
        if(domready == 1){
          var value=0;
          if(element_updated=='category_id'){
            value = search_category_id;
          }else if(element_updated=='subcategory_id'){
            value = search_subcategory_id;
          }else{
            value =search_subsubcategory_id;
          }
          $(element_updated).value = value;
        }
      }

    }),{'force':true});
  }

  function clear(element)
  { 
    for (var i = (element.options.length-1); i >= 0; i--)	{
      element.options[ i ] = null;
    }
  }
  
  var search_category_id,search_subcategory_id,search_subsubcategory_id;
  window.addEvent('domready', function() {
    
    search_category_id='<?php echo $this->category_id ?>';
   
    if(search_category_id !=0) {
      
      addOptions(search_category_id,'cat_dependency', 'subcategory_id',1);
      
      search_subcategory_id='<?php echo $this->subcategory_id ?>';      
      
      if(search_subcategory_id !=0) {
        search_subsubcategory_id='<?php echo $this->subsubcategory_id ?>';
        addOptions(search_subcategory_id,'subcat_dependency', 'subsubcategory_id',1);
      }
    }   
  });
  
  function submitSession(id){
    
    document.getElementById("listing_id_session").value=id;
    document.getElementById("setSession_form").submit();
  }
</script>