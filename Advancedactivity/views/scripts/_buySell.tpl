<?php
/**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: buysell.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>


<?php
//GET API KEY
$apiKey = Engine_Api::_()->seaocore()->getGoogleMapApiKey();
//$this->headScript()->appendFile("https://maps.googleapis.com/maps/api/js?libraries=places&key=$apiKey");
?>


<script type="text/javascript">
SmoothboxSEAO.addScriptFiles.push("https://maps.googleapis.com/maps/api/js?libraries=places&key=<?php echo $apiKey ?>");
 en4.core.runonce.add( function () {
     $('currency-element').inject($('price-element'),'top')
     $('currency-wrapper').destroy();
  //  new google.maps.places.Autocomplete(document.getElementById('place'));
    $('fade').addEvent('click', function(){
    $('advancedactivity_post_buysell_options').style.display= 'none';
   });
  });
   var showHideBuySellOptions = function () {
      var content = $('advancedactivity_post_buysell_options').innerHTML;
      $('advancedactivity_post_buysell_options').style.display= 'block';
    //  SmoothboxSEAO.open(content);
    }
     var changeFields = function () {
      
    }
 var showPreviewBuySellOptions = function () {
      var ulImage = $('file-element').getElementsByTagName("ul");
      console.log(ulImage);
      $('compose-sell-body').innerHTML = "Product <strong>"+$('title').value+"</strong> is available in <a href='javascript:void(0)'> "+$('place').value
                                          +"</a> at only <br />"+
                                        $('uploaded-images').innerHTML  
                                        ;
  $('advancedactivity_post_buysell_options').style.display= 'none';
      
      
    }
</script>

<span class="advancedactivity_post_buysell_options" id="advancedactivity_post_buysell_options" style="display:none" >
 
  <?php
  
  foreach ($this->form->getElements() as $key => $value) {
    echo  $value;
  }
  foreach ($this->form->getSubForm('fields')->getElements() as $key => $value) {
    if(!in_array($value->getType(),array('Engine_Form_Element_Radio','Engine_Form_Element_Select')) ) {
      $value->setAttrib('placeholder',$value->getLabel());
      $value->setLabel('');
    }
    echo $value;
  }
  
//echo $this->form->render($this); 

  ?>

</span>




