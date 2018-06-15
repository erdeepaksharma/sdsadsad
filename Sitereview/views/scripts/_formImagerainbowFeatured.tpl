<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _formImagerainbowFeatured.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php  
	$listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', 1);
	$featured_color = '#30a7ff';
	if(!empty($listingtype_id)) {
    $listingType = Engine_Api::_()->getItem('sitereview_listingtype', $listingtype_id);
		$sponsored_color = $listingType->featured_color;
	}
?>

<script type="text/javascript">
  function hexcolorTonumbercolor(hexcolor) {
		var hexcolorAlphabets = "0123456789ABCDEF";
		var valueNumber = new Array(3);
		var j = 0;
		if(hexcolor.charAt(0) == "#")
		hexcolor = hexcolor.slice(1);
		hexcolor = hexcolor.toUpperCase();
		for(var i=0;i<6;i+=2) {
			valueNumber[j] = (hexcolorAlphabets.indexOf(hexcolor.charAt(i)) * 16) + hexcolorAlphabets.indexOf(hexcolor.charAt(i+1));
			j++;
		}
		return(valueNumber);
	}

	window.addEvent('domready', function() {

		var r = new MooRainbow('myRainbow1', {
    
			id: 'myDemo1',
			'startColor':hexcolorTonumbercolor("<?php echo $featured_color ?>"),
			'onChange': function(color) {
				$('featured_color').value = color.hex;
			}
		});
		//showfeatured("<?php //echo $settings->getSetting('sitereview.feature.image',1)?>")
	});	
</script>

<?php
echo '
	<div id="featured_color-wrapper" class="form-wrapper">
		<div id="featured_color-label" class="form-label">
			<label for="featured_color" class="optional">
				'.$this->translate('Featured Label Color').'
			</label>
		</div>
		<div id="featured_color-element" class="form-element">
			<p class="description">'.$this->translate('Select the color of the "FEATURED" labels. (Click on the rainbow below to choose your color.)').'</p>
			<input name="featured_color" id="featured_color" value=' . $featured_color . ' type="text">
			<input name="myRainbow1" id="myRainbow1" src="'. $this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/images/rainbow.png" link="true" type="image">
		</div>
	</div>
'
?>

<script type="text/javascript">
	function showfeatured(option) {
   $('featured_color-wrapper').style.display = 'none';
   return; //Not Currently Used Show display none, Not remove this
		if(option == 1) {
			$('featured_color-wrapper').style.display = 'block';
		}
		else {
			$('featured_color-wrapper').style.display = 'none';
		}
	}
  
  window.addEvent('domready', function() {
    showfeatured('<?php echo $listingType->featured;?>');
  });    
</script>