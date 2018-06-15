<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _formImageraionbowSponsored.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php
	$this->headScript()
    ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/scripts/mooRainbow.js');

  $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/styles/mooRainbow.css');
?>

<?php  
	$listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', 1);
	$sponsored_color = '#FC0505';
	if(!empty($listingtype_id)) {
    $listingType = Engine_Api::_()->getItem('sitereview_listingtype', $listingtype_id);
		$sponsored_color = $listingType->sponsored_color;
	}
?>

<script type="text/javascript">
	window.addEvent('domready', function() { 
		var s = new MooRainbow('myRainbow2', { 
			id: 'myDemo2',
			'startColor': hexcolorTonumbercolor("<?php echo $sponsored_color ?>"),
			'onChange': function(color) {
				$('sponsored_color').value = color.hex;
			}
		});		
	});
</script>

<?php
echo '
	<div id="sponsored_color-wrapper" class="form-wrapper">
		<div id="sponsored_color-label" class="form-label">
			<label for="sponsored_color" class="optional">
				'. $this->translate('Sponsored Label Color').'
			</label>
		</div>
		<div id="sponsored_color-element" class="form-element">
			<p class="description">'.$this->translate('Select the color of the "SPONSORED" labels. (Click on the rainbow below to choose your color.)').'</p>
			<input name="sponsored_color" id="sponsored_color" value=' . $sponsored_color . ' type="text">
			<input name="myRainbow2" id="myRainbow2" src="'. $this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/rainbow.png" link="true" type="image">
		</div>
	</div>
'
?>

<script type="text/javascript">
	function showsponsored(option) {
		if(option == 1) {
			$('sponsored_color-wrapper').style.display = 'block';
		}
		else {
			$('sponsored_color-wrapper').style.display = 'none';
		}
	}
  
  window.addEvent('domready', function() {
    showsponsored('<?php echo $listingType->sponsored;?>');
  });   
</script>