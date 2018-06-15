<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: editaddress.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>


<div>
  <?php echo $this->form->render($this); ?>
</div>
<script type="text/javascript">
    //Location related work for siteevent
    sm4.core.runonce.add(function(){  
      var autocomplete = new google.maps.places.Autocomplete($.mobile.activePage.find('#location').get(0));
        google.maps.event.addListener(autocomplete, 'place_changed', function() {
          var place = autocomplete.getPlace();
          if (!place.geometry) {
            return;
          }

      $.mobile.activePage.find('#latitude').val(place.geometry.location.lat());
      $.mobile.activePage.find('#longitude').val(place.geometry.location.lng());
      });  
    });
</script>