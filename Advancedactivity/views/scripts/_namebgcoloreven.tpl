<?php 
	$action = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
  $advancedactivity_feed_highlighted_color = $this->value;
	$template_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('template_id', null);
  if($action == 'edit-template' && is_numeric($template_id)) {
    $templates = Engine_Api::_()->getDbtable('templates','sitesubscription')->find($template_id)->current();
  
  
    if(!empty($templates->$this->name)) {
      $advancedactivity_feed_highlighted_word_color = $templates->advancedactivity_feed_highlighted_word_color;
    }
  }
?>


<script src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/scripts/mooRainbow.js" type="text/javascript"></script>

<?php
	$this->headLink()
			->prependStylesheet($this->layout()->staticBaseUrl.'application/modules/Seaocore/externals/styles/mooRainbow.css');
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
    var s = new MooRainbow('myRainbow<?php echo $this->order ?>', { 
      id: 'myDemo<?php echo $this->order ?>',
      'startColor': hexcolorTonumbercolor('<?php echo $advancedactivity_feed_highlighted_color ?>'),
      'onChange': function(color) {
        $('<?php echo $this->name ?>').value = color.hex;
        if(($('title') || $('preview_banner')) && '<?php echo $this->name ?>' == 'background_color'){
            showPreview(null,color.hex,null);
        }
        if(($('title') || $('preview_banner')) && '<?php echo $this->name ?>' == 'color'){
            showPreview(null,null,color.hex);
        }
      }
    });
  });
</script>

<?php

echo '
<div id="'.$this->name.'-wrapper" class="form-wrapper">
	<div id="'.$this->name.'-label" class="form-label">
		<label for="'.$this->name.'" class="optional">
			' . $this->translate("$this->label") . '
		</label>
	</div>
	<div id="sdsd -element" class="form-element">
		<p class="description">' . $this->translate("$this->description") . '</p>
		<input name="'.$this->name.'" id="'.$this->name.'" value=' . $advancedactivity_feed_highlighted_color . ' type="text">
		<input name="myRainbow'.$this->order.'" id="myRainbow'.$this->order.'" src="'. $this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/images/rainbow.png" link="true" type="image">
	</div>
</div>
'
?>