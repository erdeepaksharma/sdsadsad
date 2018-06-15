<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _category.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<div class="form-wrapper" id="category_id-wrapper" style='display:none;'>
	<div class="form-label" id="category_id-label">
		<label class="optional" for="category_id"><?php echo $this->translate('Category');?></label>
	</div>
	<div class="form-element" id="category_id-element">
		<select id="category_id" name="category_id" onchange='addOptions(this.value, "cat_dependency", "subcategory_id", 0); setHiddenValues("category_id")'></select>
	</div>
</div>

<div class="form-wrapper" id="subcategory_id-wrapper" style='display:none;'>
	<div class="form-label" id="subcategory_id-label">
		<label class="optional" for="subcategory_id"><?php echo $this->translate('Sub-Category');?></label>
	</div>
	<div class="form-element" id="subcategory_id-element">
		<select id="subcategory_id" name="subcategory_id" onchange='addOptions(this.value, "subcat_dependency", "subsubcategory_id", 0); setHiddenValues("subcategory_id");'></select>
	</div>
</div>

<div class="form-wrapper" id="subsubcategory_id-wrapper" style='display:none;'>
	<div class="form-label" id="subsubcategory_id-label">
		<label class="optional" for="subsubcategory_id"><?php echo $this->translate('3%s Level Category', "<sup>rd</sup>") ?></label>
	</div>
	<div class="form-element" id="subsubcategory_id-element">
		<select id="subsubcategory_id" name="subsubcategory_id" onchange='setHiddenValues("subsubcategory_id")' ></select>
	</div>
</div>

<script type="text/javascript">
  
  function setHiddenValues(element_id) {
    $('hidden_'+element_id).value = $(element_id).value;
  }
  
	function addOptions(element_value, element_type, element_updated, domready) {

		var element = $(element_updated);
    if(domready == 0){
			switch(element_type){
			case 'listingtype_id':
				$('category_id'+'-wrapper').style.display = 'none';
				clear($('category_id'));
					$('category_id').value = 0;
          $('hidden_category_id').value = 0;
			case 'cat_dependency':
				$('subcategory_id'+'-wrapper').style.display = 'none';
				clear($('subcategory_id'));
					$('subcategory_id').value = 0;
          $('hidden_subcategory_id').value = 0;
			case 'subcat_dependency':
				$('subsubcategory_id'+'-wrapper').style.display = 'none';
				clear($('subsubcategory_id'));
					$('subsubcategory_id').value = 0;
          $('hidden_subsubcategory_id').value = 0;
     }
    }
    
    if(element_value <= 0) return;  
  
  	var url = '<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'general', 'action' => 'categories'), "admin_default", true);?>';
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
				}

				if(categories.length  > 0 )
					$(element_updated+'-wrapper').style.display = 'block';
				else
					$(element_updated+'-wrapper').style.display = 'none';
        if(categories.length<=0)
          $('hidden_'+element_updated).value=0;
        if(domready == 1 && $('hidden_'+element_updated).value){
          $(element_updated).value = $('hidden_'+element_updated).value;
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
  
  window.addEvent('domready', function() {
		$('hidden_category_id-wrapper').style.display = 'none';
    $('hidden_subcategory_id-wrapper').style.display = 'none';
    $('hidden_subsubcategory_id-wrapper').style.display = 'none';
//    if($('global_page_core-admin-content-widget'))
//      $('global_page_core-admin-content-widget').setStyle('max-height',(parent.window.getSize().y)+'px');

    if($("listingtype_id").get('type')=='hidden') {
      $("listingtype_id").value = 1;
    }
    
    addOptions($("listingtype_id").value,'listingtype_id', 'category_id',1);
    
    if($("hidden_category_id").value) {
     addOptions($("hidden_category_id").value,'cat_dependency', 'subcategory_id',1);
    
     if($("hidden_subcategory_id").value) {
      addOptions($("hidden_subcategory_id").value,'subcat_dependency', 'subsubcategory_id',1);
     }
    }   
	});
</script>