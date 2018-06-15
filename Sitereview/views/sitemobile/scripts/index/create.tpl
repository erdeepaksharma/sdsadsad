<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: create.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php 
  $defaultProfileFieldId = Engine_Api::_()->getDbTable('metas', 'sitereview')->defaultProfileId();
  $defaultProfileFieldId = "0_0_$defaultProfileFieldId";
  ?>
<script type="text/javascript">

	sm4.core.runonce.add(function() {
		sm4.core.Module.autoCompleter.attach("tags", '<?php echo $this->url(array('module' => 'seaocore', 'controller' => 'index', 'action' => 'tag-suggest'), 'default', true) ?>', {'singletextbox': true, 'limit':10, 'minLength': 1, 'showPhoto' : false, 'search' : 'text'}, 'toValues'); 

	});

  sm4.core.runonce.add(function(){
     if('<?php echo $this->expiry_setting; ?>' !=1){
       $.mobile.activePage.find("#end_date_enable-wrapper").css("display", "none");
     }

  });
  
  var updateTextFields = function(endsettings)
  {
      
    endsettings = $(endsettings);
    var endtime_element = $.mobile.activePage.find("#end_date-wrapper");
    endtime_element.css("display", "none");

    if (endsettings.val() == 0)
    {
      endtime_element.css("display", "none");
      return;
    }

    if (endsettings.val() == 1)
    {
      endtime_element.css("display", "block");
      return;
    }
  }
  
  sm4.core.runonce.add(updateTextFields);
  
</script>

<?php
/* Include the common user-end field switching javascript */
echo $this->partial('_jsSwitch.tpl', 'fields', array(
        //'topLevelId' => (int) @$this->topLevelId,
        //'topLevelValue' => (int) @$this->topLevelValue
))
?>

<?php if ($this->category_count <= 0): ?>
    <div class="tip"> 
      <span>
				<?php if($this->level_id == 1): ?>
					<?php echo $this->translate("Note: Users will not be able to create listings in '%s', until you have created atleast one category. Please create some categories from the Admin Panel for this listing type to enable users to create listings.", $this->listing_plural_lc); ?>
				<?php else: ?>
					<?php echo $this->translate("Sorry, you can not post a new listing right now. Please try again after sometime or contact us by filling the 'Contact Us' form using the 'Contact' link available in the footer of our site."); ?>
				<?php endif; ?>	
      </span>
    </div>
<?php endif; ?>
<div class='layout_middle sr_create_list_form'>
  <?php if ($this->current_count >= $this->quota && !empty($this->quota)): ?>
    <div class="tip"> 
      <span>
        <?php echo $this->translate("You have already created the maximum number of $this->listing_singular_lc allowed."); ?>
      </span>
    </div>
    <br/>
  <?php elseif($this->category_count > 0): ?>
    <?php if ($this->sitereview_render == 'sitereview_form'):?>
      <?php echo $this->form->setAttrib('class', 'global_form sr_create_list_form')->render($this);?>
    <?php  else:?>
      <?php echo $this->translate($this->sitereview_formrender);?>
    <?php endif;?>
  <?php endif; ?>
</div>

<script type="text/javascript">
  var getProfileType = function(category_id) {
    var mapping = <?php echo Zend_Json_Encoder::encode(Engine_Api::_()->getDbTable('categories', 'sitereview')->getMapping($this->listingtype_id, 'profile_type')); ?>;
    for(i = 0; i < mapping.length; i++) {
      if(mapping[i].category_id == category_id)
        return mapping[i].profile_type;
    }
    return 0;
  }

 sm4.core.runonce.add(function()
  {
      checkDraft();
      var defaultProfileId = '<?php echo $defaultProfileFieldId ?>'  + '-wrapper';
      if($.type($.mobile.activePage.find('#'+defaultProfileId)) && typeof $.mobile.activePage.find('#'+defaultProfileId) != 'undefined') {
        $.mobile.activePage.find('#'+defaultProfileId).css('display', 'none');
      }
  });
	function checkDraft(){
		if($.mobile.activePage.find('#draft')){
			if($.mobile.activePage.find('#draft').val() ==1) {
				$.mobile.activePage.find("#search-wrapper").css('display', 'none');
				$.mobile.activePage.find("#search").attr("checked", false);
        
        if($.mobile.activePage.find("#creation_date-wrapper")) {
            $.mobile.activePage.find("#creation_date-wrapper").css('display', 'none');
        }    
        
			} else{
				$.mobile.activePage.find("#search-wrapper").css('display', 'block');
				$.mobile.activePage.find("#search").attr("checked", true);
        
        if($.mobile.activePage.find("#creation_date-wrapper")) {
            $.mobile.activePage.find("#creation_date-wrapper").css('display', 'block');
        }            
			}
		}
	}

sm4.core.runonce.add(function(){  
  if($.mobile.activePage.find('#location').get(0)) {
      var autocomplete = new google.maps.places.Autocomplete($.mobile.activePage.find('#location').get(0));
      google.maps.event.addListener(autocomplete, 'place_changed', function() {
        var place = autocomplete.getPlace();
        if (!place.geometry) {
          return;
        }

      $.mobile.activePage.find('#latitude').val(place.geometry.location.lat());
      $.mobile.activePage.find('#longitude').val(place.geometry.location.lng());
    
    });  
  }
});
</script>