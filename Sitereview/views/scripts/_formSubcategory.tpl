<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _formSubcategory.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php
	//GET LISTING TYPE ID
	$listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
  $defaultProfileFieldId = Engine_Api::_()->getDbTable('metas', 'sitereview')->defaultProfileId();
  $defaultProfileFieldId = "0_0_$defaultProfileFieldId";
  $listing_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listing_id', 0);
  $profile_type = 0;
  if(!empty($listing_id)) {
    $listing = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
    $profile_type = Engine_Api::_()->getDbTable('categories', 'sitereview')->getProfileType(null, $listing->category_id, 'profile_type');
  }
 
  $cateDependencyArray = Engine_Api::_()->getDbTable('categories', 'sitereview')->getCatDependancyArray();

?>

<?php
echo "
	<div id='subcategory_backgroundimage' class='form-wrapper'></div>
	<div id='subcategory_id-wrapper' class='form-wrapper' style='display:none;'>
		<div id='subcategory_id-label' class='form-label'>
		 	<label for='subcategory_id' class='optional'>" . $this->translate('Subcategory') . "</label>
		</div>
		<div id='subcategory_id-element' class='form-element'>
			<select name='subcategory_id' id='subcategory_id' onchange='showFields($(this).value, 2); changesubcategory(this.value);'>
			</select>
		</div>
	</div>";
?>
<?php
echo "
	<div id='subsubcategory_backgroundimage' class='form-wrapper'> </div>
	<div id='subsubcategory_id-wrapper' class='form-wrapper' style='display:none;'>
		<div id='subsubcategory_id-label' class='form-label'>
			<label for='subsubcategory_id' class='optional'>" . $this->translate('3%s Level Category', "<sup>rd</sup>") . "</label>
		</div>
		<div id='subsubcategory_id-element' class='form-element'>
			<select name='subsubcategory_id' id='subsubcategory_id' onchange='showFields($(this).value, 3)'>
			</select>
		</div>
	</div>";
?>
<script type="text/javascript">
  var cateDependencyArray = '<?php echo json_encode($cateDependencyArray); ?>';

  var defaultProfileFieldId = '<?php echo $defaultProfileFieldId; ?>';
  var actionType = '<?php echo $listing_id; ?>';
  var profile_type = '<?php echo $profile_type ?>';
  var previous_mapped_level = 0;
  
  function showFields(cat_value, cat_level) {

    if(cat_level == 1 || (previous_mapped_level >= cat_level && previous_mapped_level != 1) || (profile_type == null || profile_type == '' || profile_type == 0)) {
      profile_type = getProfileType(cat_value); 
      if(profile_type == 0) { profile_type = ''; } else { previous_mapped_level = cat_level; }
      if($(defaultProfileFieldId)) {
      $(defaultProfileFieldId).value = profile_type;
      changeFields($(defaultProfileFieldId));
      }
    }
    
    if(actionType != 0) {
      prefieldForm();
    }
  }

  var sub = '';
  var subcatname = '';
  var show_subcat = 1;
	<?php if (!empty($this->sitereview->category_id)) : ?>
    show_subcat = 0;
  <?php endif; ?>

	var subcategories = function(category_id, sub, subcatname)
	{
		$('subcategory_id-wrapper').style.display = 'none';
		$('subsubcategory_id-wrapper').style.display = 'none';

		if(cateDependencyArray.indexOf(category_id) == -1 || category_id == 0) {
		  return;
    }
		$('subcategory_id-wrapper').style.display = 'block';
		if($('buttons-wrapper')) {
			$('buttons-wrapper').style.display = 'none';
		}
		var url = '<?php echo $this->url(array('action' => 'sub-category'), "sitereview_general_listtype_$listingtype_id", true);?>';
		
		$('subcategory_backgroundimage').style.display = 'block';
		$('subcategory_id').style.display = 'none';
		$('subsubcategory_id').style.display = 'none';
		if($('subcategory_id-label'))
			$('subcategory_id-label').style.display = 'none';
	  if($('subsubcategory_id-label'))
			$('subsubcategory_id-label').style.display = 'none';
			$('subcategory_backgroundimage').innerHTML = '<div class="form-label"></div><div class="form-element"><img src="<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Seaocore/externals/images/core/loading.gif" alt="" /></div>';	        

		en4.core.request.send(new Request.JSON({      	
			url : url,
			data : {
				format : 'json', 
				category_id_temp : category_id
			},
			onSuccess : function(responseJSON) {          
				if($('buttons-wrapper')) {
					$('buttons-wrapper').style.display = 'block';
				}
				$('subcategory_backgroundimage').style.display = 'none';
				clear('subcategory_id');
				var  subcatss = responseJSON.subcats;		

				addOption($('subcategory_id')," ", '0');
				for (i=0; i< subcatss.length; i++) {
					addOption($('subcategory_id'), subcatss[i]['category_name'], subcatss[i]['category_id']);
					if(show_subcat == 0) {
						$('subcategory_id').disabled = 'disabled';
						if($('subsubcategory_id'))
						$('subsubcategory_id').disabled = 'disabled';
					}
					$('subcategory_id').value = sub;
				}
			
				if(category_id == 0) {
					clear('subcategory_id');
					$('subcategory_id').style.display = 'none';
					if($('subcategory_id-label'))
						$('subcategory_id-label').style.display = 'none';
					if($('subsubcategory_id-label'))
						$('subsubcategory_id-label').style.display = 'none';
				}
			}
		}));
	};
	
	function clear(ddName)
	{ 
		for (var i = (document.getElementById(ddName).options.length-1); i >= 0; i--) 
		{ 
			document.getElementById(ddName).options[ i ]=null; 
		} 
	}	

	function addOption(selectbox,text,value)
	{
		var optn = document.createElement("OPTION");
		optn.text = text;
		optn.value = value;

		if(optn.text != '' && optn.value != '') {
			$('subcategory_id').style.display = 'block';
			if($('subcategory_id-wrapper'))
				$('subcategory_id-wrapper').style.display = 'block';
			if($('subcategory_id-label'))
				$('subcategory_id-label').style.display = 'block';
			selectbox.options.add(optn);
		} else {
			$('subcategory_id').style.display = 'none';
			if($('subcategory_id-wrapper'))
				$('subcategory_id-wrapper').style.display = 'none';
			if($('subcategory_id-label'))
				$('subcategory_id-label').style.display = 'none';
			selectbox.options.add(optn);
		}
	}

	function addSubOption(selectbox,text,value)
	{
		var optn = document.createElement("OPTION");
		optn.text = text;
		optn.value = value;
		if(optn.text != '' && optn.value != '') {
			$('subsubcategory_id').style.display = 'block';
				if($('subsubcategory_id-wrapper'))
				$('subsubcategory_id-wrapper').style.display = 'block';
				if($('subsubcategory_id-label'))
				$('subsubcategory_id-label').style.display = 'block';
			selectbox.options.add(optn);
		} else {
			$('subsubcategory_id').style.display = 'none';
				if($('subsubcategory_id-wrapper'))
				$('subsubcategory_id-wrapper').style.display = 'none';
				if($('subsubcategory_id-label'))
				$('subsubcategory_id-label').style.display = 'none';
			selectbox.options.add(optn);
		}
	}

	var cat = '<?php echo $this->category_id ?>';
	if(cat != '') {
		sub = '<?php echo $this->subcategory_id; ?>';
		subcatname = '<?php echo $this->subcategory_name; ?>';
		subcategories(cat, sub, subcatname);
	}

	function changesubcategory(subcatid) {

		$('subsubcategory_id-wrapper').style.display = 'none';
		if(cateDependencyArray.indexOf(subcatid) == -1 || subcatid == 0)
			return;
    $('subsubcategory_backgroundimage').style.display = 'block';
	  $('subsubcategory_id-wrapper').style.display = 'none';
		if($('buttons-wrapper')) {
			$('buttons-wrapper').style.display = 'none';
		}
		var url = '<?php echo $this->url(array('action' => 'subsub-category'), "sitereview_general_listtype_$listingtype_id", true);?>';
		
		$('subsubcategory_id').style.display = 'none';
		if($('subsubcategory_id-label'))
			$('subsubcategory_id-label').style.display = 'none';
			$('subsubcategory_backgroundimage').innerHTML = '<div class="form-label"></div><div  class="form-element"><img src="<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Seaocore/externals/images/core/loading.gif" /></center></div>';
			en4.core.request.send(new Request.JSON({
			url : url,
			data : {
				format : 'json',
				subcategory_id_temp : subcatid
			},
			onSuccess : function(responseJSON) {
				if($('buttons-wrapper')) {
					$('buttons-wrapper').style.display = 'block';
				}
				$('subsubcategory_backgroundimage').style.display = 'none';
				clear('subsubcategory_id');
				var  subsubcatss = responseJSON.subsubcats;

				addSubOption($('subsubcategory_id')," ", '0');
				for (i=0; i< subsubcatss.length; i++) {
					addSubOption($('subsubcategory_id'), subsubcatss[i]['category_name'], subsubcatss[i]['category_id']);
				}
			}
		}));
	}
</script>