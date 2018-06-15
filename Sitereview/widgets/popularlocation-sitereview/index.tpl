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

<?php $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');?>

<script type="text/javascript">
  var locationAction =function(cityValue)
  {
    if($("tag"))
      $("tag").value='';
    var form;
     if($('filter_form') && $('filter_form').elements['location']) {
       form=document.getElementById('filter_form');
      }else if($('filter_form_location')){
				form=$('filter_form_location');
			}
    form.elements['location'].value = cityValue;
    
		form.submit();
  }
</script>

<ul class="sr_popular_locations">
  <form id='filter_form_location' class='global_form_box' method='get' action='<?php echo $this->url(array('action' => 'index'), "sitereview_general_listtype_$this->listingtype_id", true) ?>' style='display: none;'>
    <input type="hidden" id="location" name="location"  value=""/>
  </form>
  <?php foreach ($this->sitereviewLocation as $sitereviewLocation): ?>
    <?php if (!empty($sitereviewLocation->city) || !empty($sitereviewLocation->state)): ?>
      <li <?php if (!empty($this->searchLocation) && ( $this->searchLocation == $sitereviewLocation->city ||  $this->searchLocation == $sitereviewLocation->state ) ): ?>style="font-weight: bold;" <?php endif; ?>>
          <a href="javascript:void(0);" onclick="locationAction('<?php if(!empty($sitereviewLocation->city))echo $sitereviewLocation->city; else echo $sitereviewLocation->state;  ?>')" ><?php echo ucfirst($sitereviewLocation->city) ?><?php $state=null;if(!empty($sitereviewLocation->city)&& !empty($sitereviewLocation->state))$state.=" [";$state.=ucfirst($sitereviewLocation->state);if(!empty($sitereviewLocation->city)&& !empty($sitereviewLocation->state))$state.="] ";echo $state;?></a>
          <?php if(!empty($sitereviewLocation->city)): echo "(" . $sitereviewLocation->count_location . ")"; else: echo "(" . $sitereviewLocation->count_location_state . ")"; endif;?>
      </li>
    <?php endif; ?>
  <?php endforeach; ?>
</ul>