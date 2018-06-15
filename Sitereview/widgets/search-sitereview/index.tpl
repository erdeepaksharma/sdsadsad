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
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/styles/styles.css');
?>
<?php
//GET API KEY
$apiKey = Engine_Api::_()->seaocore()->getGoogleMapApiKey();
$this->headScript()->appendFile("https://maps.googleapis.com/maps/api/js?libraries=places&key=$apiKey");
?>
<script type="text/javascript">
  
  en4.core.runonce.add(function(){
		new google.maps.places.Autocomplete(document.getElementById('location'));
  
  
  var pageAction =function(page){
    $('page').value = page;
    $('filter_form').submit();
  }


  });
  
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
        //f.set('class', 'integer_field_unselected');
      }
      if (f.value == '' && f.id.match(/\max$/)) {
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
            if (nextEl.get('class') == 'browse-separator-wrapper') {
                lastSep = nextEl;
                nextEl = false;
            } else {
                allHidden = allHidden && (nextEl.getStyle('display') == 'none');
            }
        } while (nextEl);
        if (lastSep) {
            lastSep.setStyle('display', (allHidden ? 'none' : ''));
        }
    });
</script>

<?php
	//if(empty($this->sitereview_post)){return;}
  /* Include the common user-end field switching javascript */
  echo $this->partial('_jsSwitch.tpl', 'fields', array(
    //'topLevelId' => (int) @$this->topLevelId,
    //'topLevelValue' => (int) @$this->topLevelValue
  ))
?>

<?php if($this->viewType == 'horizontal'): ?>
  <div class="seaocore_searchform_criteria seaocore_searchform_criteria_horizontal">
    <?php  if($this->sitereview_post == 'enabled') { echo $this->form->render($this); }else { return; } ?>
  </div>
<?php else: ?>
  <div class="seaocore_searchform_criteria">
    <?php  if($this->sitereview_post == 'enabled') { echo $this->form->render($this); }else { return; } ?>
  </div>
<?php endif; ?>

<script type="text/javascript">
  en4.core.runonce.add(function(){
		$('global_content').getElement('.browsesitereviews_criteria').addEvent('keypress', function(e){   
			if( e.key != 'enter' ) return;
				searchSitereviews();
		});
  });
</script>

<script type="text/javascript">
  
  var profile_type = 0;
  var previous_mapped_level = 0;  
  var sitereview_categories_slug = <?php echo json_encode($this->categories_slug); ?>;
  function showFields(cat_value, cat_level) {
       
    if(cat_level == 1 || (previous_mapped_level >= cat_level && previous_mapped_level != 1) || (profile_type == null || profile_type == '' || profile_type == 0)) {
      profile_type = getProfileType(cat_value); 
      if(profile_type == 0) { profile_type = ''; } else { previous_mapped_level = cat_level; }
      $('filter_form').getElementById('profile_type').value = profile_type;
      changeFields($('filter_form').getElementById('profile_type'));      
    }
  }

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
          $(element_updated+'-wrapper').style.display = 'inline-block';
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
  
  <?php if(!empty($this->categoryInSearchForm) && !empty($this->categoryInSearchForm->display)): ?>
    var search_category_id,search_subcategory_id,search_subsubcategory_id;
    en4.core.runonce.add(function(){

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
  <?php endif; ?>
  
  function show_subcat(cat_id) 
  {		
    if(document.getElementById('subcat_' + cat_id)) {
      if(document.getElementById('subcat_' + cat_id).style.display == 'block') {		
        document.getElementById('subcat_' + cat_id).style.display = 'none';
        document.getElementById('img_' + cat_id).src = '<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/bullet-right.png';
      } 
      else if(document.getElementById('subcat_' + cat_id).style.display == '') {			
        document.getElementById('subcat_' + cat_id).style.display = 'none';
        document.getElementById('img_' + cat_id).src = '<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/bullet-right.png';
      }
      else {			
        document.getElementById('subcat_' + cat_id).style.display = 'block';
        document.getElementById('img_' + cat_id).src = '<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/bullet-bottom.png';
      }		
    }
  }
  
  en4.core.runonce.add(function(){
    <?php if ($this->profileType): ?>		
      $('filter_form').getElementById('profile_type').value = '<?php echo $this->profileType;?>';
      changeFields($('filter_form').getElementById('profile_type'));   
    <?php endif; ?>
  });  

</script>

<script>
  en4.core.runonce.add(function() {
    if ($('location')) {
      var params = {
        'detactLocation': <?php echo $this->locationDetection; ?>,
        'fieldName': 'location',
        'noSendReq': 1,
        'locationmilesFieldName': 'locationmiles',
        'locationmiles': <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('seaocore.locationdefaultmiles', 1000); ?>,
        'reloadPage': 1,
      };
      en4.seaocore.locationBased.startReq(params);
    }
  });

  locationAutoSuggest('<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('seaocore.countrycities'); ?>', 'location', '');
</script>



<script>
    en4.core.runonce.add(function()
  {
    var item_count = 0;
    var contentAutocomplete = new Autocompleter.Request.JSON('search', '<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'index', 'action' => 'ajax-search'), "default", true) ?>', {
      'postVar' : 'text',
      'minLength': 1,
      'selectMode': 'pick',
     
      'autocompleteType': 'tag',
      'className': 'seaocore-autosuggest tag-autosuggest',
      'customChoices' : true,
      'filterSubset' : true,
      'multiple' : false,
      postData : {
        listingtype_id: <?php echo $this->listingtype_id ?>,
      },
      'injectChoice': function(token) {
	      if(typeof token.label != 'undefined' ) {
          if (token.sitereview_url != 'seeMoreLink') {
            var choice = new Element('li', {'class': 'autocompleter-choices1', 'html': token.photo, 'id':token.label, 'sitereview_url':token.sitereview_url, onclick:'javascript:getPageResults("'+token.sitereview_url+'")'});
            new Element('div', {'html': this.markQueryValue(token.label),'class': 'autocompleter-choice'}).inject(choice);
            this.addChoiceEvents(choice).inject(this.choices);
            choice.store('autocompleteChoice', token);
          }
          if(token.sitereview_url == 'seeMoreLink' && <?php echo $this->listingtype_id ?> > 0) {
            var title = $('title').value;
            var choice = new Element('li', {'class': 'autocompleter-choices1', 'html': '', 'id':'stopevent', 'sitereview_url':''});
            new Element('div', {'html': 'See More Results for '+title ,'class': 'autocompleter-choicess', onclick:'javascript:Seemore()'}).inject(choice);
            this.addChoiceEvents(choice).inject(this.choices);
            choice.store('autocompleteChoice', token);
          }
         }
       }
    });

    contentAutocomplete.addEvent('onSelection', function(element, selected, value, input) {
      window.addEvent('keyup', function(e) {
        if(e.key == 'enter') {
          if(selected.retrieve('autocompleteChoice') != 'null' ) {
            var url = selected.retrieve('autocompleteChoice').sitereview_url;
            if (url == 'seeMoreLink') {
              Seemore();
            }
            else {
              window.location.href=url;
            }
          }
        }
      });      
    });
  });
  
  function Seemore() {
    $('stopevent').removeEvents('click');
    <?php if($this->listingtype_id > 0): ?>
    var url = '<?php echo $this->url(array('action' => 'index'), "sitereview_general_listtype_$this->listingtype_id", true); ?>';
  	window.location.href= url + "?search=" + encodeURIComponent($('title').value);
    <?php endif; ?>
  }

  function getPageResults(url) {
    var listingtype_id = <?php echo $this->listingtype_id ?>;
    if(url != 'null' ) {
      if (url == 'seeMoreLink' && listingtype_id > 0) {
        Seemore();
      }
      else {
        window.location.href=url;
      }
    }
  }
  
</script>
