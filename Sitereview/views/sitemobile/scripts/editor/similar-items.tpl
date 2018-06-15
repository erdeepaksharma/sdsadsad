<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: similar-items.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php
  $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereviewprofile.css');
  $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');
?>

<?php if(empty($this->existWidget)): ?>
  <div class='layout_middle'>
    <div class="tip">
      <span> <?php echo $this->translate('Site administrators have removed the "Add Best Alternatives" block from the profile page of this listing. For more details, please contact our site administrators using the "Contact Us" form.'); ?></span>
    </div>
  </div>
<?php return; endif;?>

<?php if(empty($this->is_ajax)): ?>

  <?php
    $listingType = $listing = Engine_Api::_()->getItem('sitereview_listingtype', $this->listing->listingtype_id);
    $this->title = ucfirst($listingType->title_plural);
    $this->title_singular = strtolower($listingType->title_singular). "'s";
    $this->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation("sitereview_main_listtype_$listingType->listingtype_id");
    include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/navigation_views.tpl'; 
  ?>

<?php echo $this->htmlLink(array('route' => 'sitereview_entry_view_listtype_'.$this->listing->listingtype_id, 'listing_id' => $this->listing->listing_id, 'slug' => $this->listing->getSlug()), $this->translate('Back to Profile Page'), array('class'=> 'buttonlink sr_item_icon_back fright mright5 mtop5')) ?>
<h3><?php echo $this->translate("Add Best Alternatives");?></h3>
<div class="clr mtop5">
	<?php echo $this->translate("Select the best alternatives for %s below. (Selected alternatives will be displayed in 'Best Alternatives' block on this $this->title_singular profile page.)", $this->listing->getTitle());?>
</div>
<div class="sr_add_similar_items mtop10">
	<div class="sr_item_filters_wrapper">
  	<div class="sr_item_filters">
  		<div>
  			<div>
			    <div class="form-wrapper">
			    	<div class="form-label">
			    		<label class="optional"><?php echo $this->translate('Search');?></label>
			    	</div>
			    	<div class="form-element">
			      	<input type="text" onkeyup="viewMorePhoto(1);" id="sitereview_members_search_inputd" class="searchbox" />
			      </div>
			    </div>
			    
    			<div class="form-wrapper" id="category_id-wrapper">
						<div class="form-label" id="category_id-label">
							<label class="optional" for="category_id"><?php echo $this->translate('Category');?></label>
						</div>
						<div class="form-element" id="category_id-element">
							<select id="category_id" name="category_id" onchange='addOptions(this.value, "cat_dependency", "subcategory_id", 0); viewMorePhoto(1);'>
              	<option value="-1"></option>            
		            <?php $categoryArray[0] = ""; ?>
		            <?php foreach ($this->categories as $category): ?>
		              <?php $categoryArray[$category->category_id] = $category->category_name; ?>
		              <option value="<?php echo $category->category_id;?>" <?php if( $this->category_id == $category->category_id) echo "selected";?>><?php echo $this->translate($category->category_name);?>
		              </option>
		            <?php endforeach; ?>
    					</select>
						</div>
					</div>

					<div class="form-wrapper" id="subcategory_id-wrapper" style='display:none;'>
						<div class="form-label" id="subcategory_id-label">
							<label class="optional" for="subcategory_id"><?php echo $this->translate('Sub-Category');?></label>
						</div>
						<div class="form-element" id="subcategory_id-element">
							<select id="subcategory_id" name="subcategory_id" onchange='viewMorePhoto(1);'></select>
						</div>
					</div>
				</div>
			</div>		
    </div> 
  </div>
    
  <div class="sr_add_similar_items_content b_medium mbot10">
  	<div class="sr_add_similar_items_content_head b_medium"><?php echo $this->translate("Selected Alternatives");?><span id='selected_item'></span></div>
    <div class="o_hidden" id="similerContent">
      <div id="tip_similar_items" class="tip" style="display:none;">
        <span><?php echo $this->translate('No alternatives have been added yet!');?></span> 
      </div>      
    
      <?php foreach($this->similarListings as $listing): ?>
      <div class="seaocore_popup_items" id="similar_items_<?php echo $listing->listing_id; ?>" onclick="removeItem('<?php echo $listing->listing_id; ?>')">
        <a href="javascript:void(0);" id="check_<?php echo $listing->listing_id; ?>" class="suggestion_pop_friend selected">
        	<i id="crossIcon_<?php echo $listing->listing_id; ?>" class="removeItem" onclick="removeItem('<?php echo $listing->listing_id; ?>')"></i>
          <?php if($listing->photo_id):?>
            <span style="background-image:url('<?php echo $listing->getPhotoUrl('thumb.icon');?>')"></span>
          <?php else: ?>
            <span style="background-image:url('<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/nophoto_listing_thumb_icon.png')"></span>
          <?php endif; ?>
                
          <p><?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($listing->getTitle(), 999);?></p>
        </a>
      </div>
      <?php endforeach;?>
    </div>
  </div>    
  <br /><br />
  <div class="sr_add_similar_items_content b_medium sr_add_similar_items_content_search">
    <div class="seaocore_popup_content_inner" id="unSelectedItems">	 
      <?php endif; ?>
      <div id="tip_unsimilar_items" class="tip" style="display:<?php if($this->paginator->getTotalItemCount()):?>none;<?php else: ?>block;<?php endif;?>">
        <span><?php echo $this->translate('There are currently no alternatives.');?></span> 
      </div>     
     <div id="tip_unsimilar_items_viewmore" class="tip" style="display:none;">
        <span><?php echo $this->translate("Please click on 'View More' link below to view more entries.");?></span> 
      </div>  
      <?php foreach($this->paginator as $listing): ?>
        <div class="seaocore_popup_items" id="items_<?php echo $listing->listing_id; ?>" onclick="addItem('<?php echo $listing->listing_id; ?>')" >
          <a href="javascript:void(0);" id="check_<?php echo $listing->listing_id; ?>" class="suggestion_pop_friend">
          	<i id="crossIcon_<?php echo $listing->listing_id; ?>" class="removeItem" onclick="removeItem('<?php echo $listing->listing_id; ?>')"></i>
              <?php if($listing->photo_id):?>
                <span style="background-image:url('<?php echo $listing->getPhotoUrl('thumb.icon');?>')">
              <?php else: ?>
                <span style="background-image:url('<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/nophoto_listing_thumb_icon.png')">
              <?php endif; ?>
              </span>
              <p><?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($listing->getTitle(), 999);?></p>
            </a>
          </div>
        <?php endforeach; ?>
      <?php if(empty($this->is_ajax)) :?>
    </div>
  </div>
  
 <div class="clr" id="view_more" onclick="viewMorePhoto(0)">
  <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array(
    'id' => 'feed_viewmore_link',
    'class' => 'buttonlink icon_viewmore'
  )) ?>
  </div>
  <div class="seaocore_loading" id="loding_image" style="display: none;">
    <img src='<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/core/loading.gif' style='margin-right: 5px;' />
    <?php echo $this->translate("Loading ...") ?>
  </div>
  <br/>
  <div class="popup_btm">
    <div id="check_error"></div>
		
      <form method="post" id="form_custom_list" action="<?php echo $this->url(array('action' => 'add-items','listing_id' => $this->listing->listing_id, 'listingtype_id' => $this->listing->listingtype_id),'sitereview_editor_general_listtype_'.$this->listing->listingtype_id, true) ?>">
        <input type="hidden"  name="selected_resources" id='selected_resources' />
          <button type='button' onClick='submitListForm()'><?php echo $this->translate('Save Changes'); ?></button>
            &nbsp;<?php echo $this->translate("or"); ?>&nbsp;
          <a href="<?php echo $this->url(array('listing_id' => $this->listing->listing_id, 'slug' => $this->listing->getSlug()),'sitereview_entry_view_listtype_'.$this->listing->listingtype_id) ?>" ><?php echo $this->translate("cancel") ?></a>
      </form>
    </div>	
  </div>  
<?php endif; ?>

<script type="text/javascript">
  <?php if(empty($this->is_ajax)): ?>
    var subCatValue = '<?php echo $this->subcategory_id ?>';
    var similer_list= new Array();
    en4.core.runonce.add(function(){
  
     <?php foreach($this->similarListings as $listing): ?>
       similer_list.push('<?php echo $listing->listing_id; ?>'); 
     <?php endforeach; ?>
     $('selected_item').innerHTML='('+similer_list.length+')';
     if(similer_list.length == 0) {
       $('tip_similar_items').style.display = 'block';
     }
     else {
       $('tip_similar_items').style.display = 'none';
     }
  });
  
  function addItem(listing_id) {
    var element= new Element("div",{
      'id':'similar_items_'+listing_id,
      'html':$('items_'+listing_id).innerHTML,
      'class':'seaocore_popup_items'
    }).inject($('similerContent'));
    element.getFirst('a').addClass('selected');
    $('similar_items_'+listing_id).addEvent('click', function () {
     removeItem(listing_id);
    });
    
    similer_list.push(listing_id); 
    $('items_'+listing_id).destroy();
    $('selected_item').innerHTML='('+similer_list.length+')';
    if(similer_list.length == 0) {
      $('tip_similar_items').style.display = 'block';
    }
    else {
      $('tip_similar_items').style.display = 'none';
    }
    
    if( $('unSelectedItems').getElements('.seaocore_popup_items').length<1){
      if($('view_more').style.display == 'none' ) {
        $('tip_unsimilar_items').style.display = 'block';  
      }
      else {
        $('tip_unsimilar_items_viewmore').style.display = 'block';
      }
    }
  }
 
  function removeItem(listing_id) {
     $('tip_unsimilar_items').style.display = 'none';
     $('tip_unsimilar_items_viewmore').style.display = 'none';
    var el = new Element("div",{
      'id':'items_'+listing_id,
      'html':$('similar_items_'+listing_id).innerHTML,
      'class':'seaocore_popup_items'
    }).inject($('unSelectedItems'));
    el.getFirst('a').removeClass('selected');
    $('items_'+listing_id).addEvent('click', function () {
      addItem(listing_id);
    });
    
    for(var i = 0; i < similer_list.length;i++ )
    {
      if(similer_list[i]==listing_id) 
        similer_list.splice(i,1); 
    }
   
    $('similar_items_'+listing_id).destroy();
    $('selected_item').innerHTML='('+similer_list.length+')';
    if(similer_list.length == 0) {
      $('tip_similar_items').style.display = 'block';
    }
    else {
      $('tip_similar_items').style.display = 'none';
    }   
  }  
  
  function viewMorePhoto(searched)
  {
    var pageNext = getNextPage();
    if(searched == 1) {
      pageNext = 1;
    }
    $('view_more').style.display ='none';   
    $('loding_image').style.display ='';
    var getItemsUrl = '<?php echo $this->url(array('action' => 'similar-items'), 'sitereview_editor_general_listtype_'.$this->listing->listingtype_id, true) ?>';
    en4.core.request.send(new Request.HTML({
      method : 'post',
      'url' : getItemsUrl,
      'data' : {
        format : 'html',
        listing_id : '<?php echo $this->listing_id ?>',
        textSearch : $('sitereview_members_search_inputd').value,
        category_id : $('category_id').value,
        subcategory_id : $('subcategory_id').value,
        is_ajax : 1,
        page: pageNext
      },
      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
        $('tip_unsimilar_items').style.display = 'none'; 
        $('tip_unsimilar_items_viewmore').style.display = 'none';
        if(searched == 1) {
          $('unSelectedItems').innerHTML = responseHTML;
        }
        else {
          Elements.from(responseHTML).inject($('unSelectedItems'));
        }
        
        $('loding_image').style.display ='none';
        hideViewMoreLink();
      }
    }), {
      "force":true
    });

    return false;
  }  
  
  function submitListForm(){
    document.getElementById("selected_resources").value = similer_list;
    document.getElementById("form_custom_list").submit();  
  }
  
  <?php endif;?>

  function getNextPage(){
    return <?php echo $this->paginator->getCurrentPageNumber()+1; ?>
  }
    
  function hideViewMoreLink(){ 
    $('view_more').style.display = '<?php echo ( $this->paginator->count() > $this->paginator->getCurrentPageNumber() ? '' : 'none' ) ?>';
  }
  
	function addOptions(element_value, element_type, element_updated, domready) {

		var element = $(element_updated);
    if(domready == 0){
			switch(element_type){
			case 'listingtype_id':
				$('category_id'+'-wrapper').style.display = 'none';
				clear($('category_id'));
				$('category_id').value = 0;
			case 'cat_dependency':
				$('subcategory_id'+'-wrapper').style.display = 'none';
				clear($('subcategory_id'));
				$('subcategory_id').value = 0;
     }
    }
   
  	var url = '<?php echo $this->url(array('action' => 'categories'), "sitereview_editor_general_listtype_".$this->listing->listingtype_id, true);?>';
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
					option.value = -1;
					element.options.add(option);
				for (i = 0; i < categories.length; i++) {
					var option = document.createElement("OPTION");
					option.text = categories[i]['category_name'];
					option.value = categories[i]['category_id'];
					element.options.add(option);
				}

				if(categories.length  > 0 )
					$(element_updated+'-wrapper').style.display = 'block';
				else
					$(element_updated+'-wrapper').style.display = 'none';
        
        if(domready ==1 && $('category_id').value != 0 && subCatValue != 0) {
          $('subcategory_id').value = subCatValue;
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
  
  var is_ajax = '<?php echo $this->is_ajax; ?>';
  
  if(is_ajax == 0) {
    window.addEvent('domready', function() { 
      addOptions($('category_id').value, "cat_dependency", "subcategory_id", 1);
    });
  }
  
  window.addEvent('domready', function() { 
    hideViewMoreLink();
  });
  
</script>