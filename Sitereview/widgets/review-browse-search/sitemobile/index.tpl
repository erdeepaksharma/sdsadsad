<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _addToWishlist.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php $this->form=$this->searchForm; ?>
<?php echo $this->render('application/modules/Sitemobile/widgets/sitemobile-advancedsearch/index.tpl');?>

<script type="text/javascript">
  //<![CDATA[
 sm4.core.runonce.add(function() {
    if($.mobile.activePage.find('#type'))
    addReviewTypeOptions($.mobile.activePage.find('#type').value);
  });
  //]]>
  var addReviewTypeOptions= function(value){
    if(!$.mobile.activePage.find('#recommend-wrapper'))
      return;
    if(value=='user'){
      $.mobile.activePage.find('#recommend-wrapper').css('display','block');
    }else{
      $.mobile.activePage.find('#recommend-wrapper').css('display','none');
    }
  }
  
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

	var getProfileType = function(category_id) {
		var mapping = <?php echo Zend_Json_Encoder::encode(Engine_Api::_()->getDbTable('categories', 'sitereview')->getMapping($this->listingtype_id, 'profile_type_review')); ?>;
		for(i = 0; i < mapping.length; i++) {
			if(mapping[i].category_id == category_id)
				return mapping[i].profile_type_review;
		}
		return 0;
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
            value = srreview_search_category_id;
          }else if(element_updated=='subcategory_id'){
            value = srreview_search_subcategory_id;
          }else{
            value =srreview_search_subsubcategory_id;
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
  
  var srreview_search_category_id,srreview_search_subcategory_id,srreview_search_subsubcategory_id;
 sm4.core.runonce.add(function() {
    
    srreview_search_category_id='<?php echo isset($this->requestParams['category_id']) && $this->requestParams['category_id'] ? $this->requestParams['category_id'] : 0 ?>';
    addOptions($("listingtype_id").value,'listingtype_id', 'category_id',1);
   
    if(srreview_search_category_id !=0) {
      srreview_search_subcategory_id='<?php echo isset($this->requestParams['subcategory_id']) && $this->requestParams['subcategory_id'] ? $this->requestParams['subcategory_id'] : 0 ?>';
      
      addOptions(srreview_search_category_id,'cat_dependency', 'subcategory_id',1);
      
      if(srreview_search_subcategory_id !=0) {
        srreview_search_subsubcategory_id='<?php echo isset($this->requestParams['subsubcategory_id']) && $this->requestParams['subsubcategory_id'] ? $this->requestParams['subsubcategory_id'] : 0 ?>';
        addOptions(srreview_search_subcategory_id,'subcat_dependency', 'subsubcategory_id',1);
      }
    }   
  });
</script> 