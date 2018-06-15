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
	$apiKey = Engine_Api::_()->seaocore()->getGoogleMapApiKey();
$this->headScript()->appendFile("https://maps.googleapis.com/maps/api/js?libraries=places&key=$apiKey");
	$this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');
?>

<script type="text/javascript">

  var pageAction =function(page) {
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
      }
      if (f.value == '' && f.id.match(/\max$/)) {
        new OverText(f, {'textOverride':'max','element':'span'});
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
echo $this->partial('_jsSwitch.tpl', 'fields', array());
?>

<?php if( $this->form ): ?>
  <div class="global_form_box sitereview_advanced_search_form">
    <?php echo $this->form->render($this) ?>
  </div>
  <div class="" id="page_location_pops_loding_image" style="display: none;">
    <img src='<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/core/loading.gif' />
    <?php //echo $this->translate("Loading ...") ?>
  </div>
<?php endif ?>

<script type="text/javascript">
  var flag = '<?php echo $this->advanced_search; ?>';
  var mapGetDirection;
  var myLatlng;
  
	window.addEvent('domready', function() {
	
	  if(document.getElementById('location').value == '') {
			submiForm();
		}
		
		if ($$('.browse-separator-wrapper')) {
			$$('.browse-separator-wrapper').setStyle("display",'none');
		}
	
	  $('page_location_pops_loding_image').injectAfter($('done-element'));
		new google.maps.places.Autocomplete(document.getElementById('location'));
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function(position){
			var lat = position.coords.latitude;
			var lng = position.coords.longitude;
			
			var myLatlng = new google.maps.LatLng(lat,lng);
			
			var myOptions = {
				zoom: 8 ,
				center: myLatlng,
				navigationControl: true,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			}

			mapGetDirection = new google.maps.Map(document.getElementById("sitereview_location_map_none"), myOptions);
    
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
              var my_distance = 1000; 
              for (var i = 0; i < results.length; i++){
              var R = 6371; // km
              var lat2 = results[i].geometry.location.lat();
              var lon2 = results[i].geometry.location.lng(); 
              var dLat = (lat2-lat) * radian;
              var dLon = (lon2-lng) * radian;
              var lat1 = lat * radian;
              var lat2 = lat2 * radian;

              var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.sin(dLon/2) * Math.sin(dLon/2) * Math.cos(lat1) * Math.cos(lat2); 
              var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
              var d = R * c;
              if(d < my_distance) {
                index = i;
                my_distance = d;
              }
            }      
           
              document.getElementById('location').value = (results[index].vicinity) ? results[index].vicinity :'';
              document.getElementById('Latitude').value = lat;
              document.getElementById('Longitude').value = lng;
              document.getElementById('locationmiles').value = 1000;
              
              //form submit by ajax
              submiForm();
            } 
          });
        } else {
          var delimiter = (position.address && position.address.street !=  '' && position.address.city !=  '') ? ', ' : '';
          var location = (position.address) ? (position.address.street + delimiter + position.address.city) : '';
          document.getElementById('location').value = location;
					document.getElementById('Latitude').value = lat;
					document.getElementById('Longitude').value = lng;
					document.getElementById('locationmiles').value = 1000;
          //form submit by ajax
          submiForm();
        }
      });
    } else {
			submiForm();
		}

		advancedSearchLists(flag);
		
	});

	function submiForm() {
	
		if ($('category_id')) {
			if ($('category_id').options[$('category_id').selectedIndex].value == 0) { 
				$('category_id').value = 0;
			}
		}
		var listingtype_id = '<?php echo $this->listingtype_id; ?>';
		var  formElements = document.getElementById('filter_form');
		var url = en4.core.baseUrl + 'widget/index/mod/sitereview/name/browselocation-sitereview'; 
		var parms = formElements.toQueryString(); 

		var param = (parms ? parms + '&' : '') + 'is_ajax=1&format=html&listingtype_id=' + listingtype_id;
		document.getElementById('page_location_pops_loding_image').style.display ='';
    var parent_list_location_map_anchor = document.getElementById('list_location_map_anchor').getParent();    
		en4.core.request.send(new Request.HTML({
			method : 'post',
			'url' : url,
			'data' : param,
			onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
				document.getElementById('page_location_pops_loding_image').style.display ='none';
				$('list_map_container_topbar').style.display ='block';
				parent_list_location_map_anchor.innerHTML = responseHTML;
				setMarker();
			  en4.core.runonce.trigger();
				$('list_map_container').style.visibility = 'visible'; 
				if ($('seaocore_browse_list')) {
					var elementStartY = $('listlocation_map').getPosition().x ;
					var offsetWidth = $('list_map_container').offsetWidth;
					var actualRightPostion = window.getSize().x - (elementStartY + offsetWidth);
				}
			}
		}), {
         "force":true
    });
	}

	function locationPage() {
		var  list_location = document.getElementById('location');
			  
		if (document.getElementById('Latitude').value) {
			document.getElementById('Latitude').value = 0;
		}
		
		if(document.getElementById('Longitude').value) {
			document.getElementById('Longitude').value = 0;
		}
	}
	
	function locationSearch() {

	  var  formElements = document.getElementById('filter_form');
    formElements.addEvent('submit', function(event) { 
      event.stop();
      submiForm();
    });
  }

	function advancedSearchLists() {
	
		if (flag == 0) {
		  if ($('fieldset-grp2'))
				$('fieldset-grp2').style.display = 'none';
				
			if ($('fieldset-grp1'))
				$('fieldset-grp1').style.display = 'none';
				
			flag = 1;
			$('advanced_search').value = 0;
			if ($('sitereview_street'))
				$('sitereview_street').value = '';
			if ($('sitereview_country'))
				$('sitereview_country').value = '';
			if ($('sitereview_state'))
				$('sitereview_state').value = '';
			if ($('sitereview_city'))
				$('sitereview_city').value = '';
			if ($('profile_type'))
				$('profile_type').value = '';
				changeFields($('profile_type'));
			if ($('orderby'))
				$('orderby').value = '';
			if ($('category_id'))
				$('category_id').value = 0;

		} 
		else {
		  if ($('fieldset-grp2'))
				$('fieldset-grp2').style.display = 'block';
			if ($('fieldset-grp1'))
				$('fieldset-grp1').style.display = 'block';
			flag = 0;
			$('advanced_search').value = 1;
		}
  }
</script>

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
</script>
<div id="sitereview_location_map_none" style="display: none;"></div>