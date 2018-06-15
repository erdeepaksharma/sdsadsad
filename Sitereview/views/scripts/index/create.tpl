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
<?php $apiKey = Engine_Api::_()->seaocore()->getGoogleMapApiKey();
$this->headScript()->appendFile("https://maps.googleapis.com/maps/api/js?libraries=places&key=$apiKey");
?>
<?php
$this->headScript()
        ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Observer.js')
        ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.js')
        ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Local.js')
        ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Request.js');
?> 

<?php if (Engine_Api::_()->sitereview()->hasPackageEnable()):?>
  <?php 
   $this->headLink()
          ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereviewpaidlisting/externals/styles/style_sitereview_package.css');
  ?>
  <?php $this->PackageCount = Engine_Api::_()->getDbTable('packages', 'sitereviewpaidlisting')->getPackageCount($this->listingtype_id);?>
<?php endif;?>

<!--WE ARE NOT USING STATIC BASE URL BECAUSE SOCIAL ENGINE ALSO NOT USE FOR THIS JS-->
<!--CHECK HERE Engine_View_Helper_TinyMce => protected function _renderScript()-->
<?php $this->tinyMCESEAO()->addJS();?>

<script type="text/javascript">
  en4.core.runonce.add(function()
  {
    new Autocompleter.Request.JSON('tags', '<?php echo $this->url(array('module' => 'seaocore', 'controller' => 'index', 'action' => 'tag-suggest', 'resourceType' => 'sitereview_listing'), 'default', true) ?>', {
      'postVar' : 'text',
      'minLength': 1,
      'selectMode': 'pick',
      'autocompleteType': 'tag',
      'className': 'tag-autosuggest',
      'customChoices' : true,
      'filterSubset' : true,
      'multiple' : true,
      'injectChoice': function(token){
        var choice = new Element('li', {'class': 'autocompleter-choices', 'value':token.label, 'id':token.id});
        new Element('div', {'html': this.markQueryValue(token.label),'class': 'autocompleter-choice'}).inject(choice);
        choice.inputValue = token;
        this.addChoiceEvents(choice).inject(this.choices);
        choice.store('autocompleteChoice', token);
      }
    });
  });

	window.addEvent('domready', function() { 
  
		if(document.getElementById('location')  && (('<?php echo !Engine_Api::_()->getApi('settings', 'core')->getSetting('seaocore.locationspecific', 0);?>') || ('<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('seaocore.locationspecific', 0);?>' && '<?php echo !Engine_Api::_()->getApi('settings', 'core')->getSetting('seaocore.locationspecificcontent', 0); ?>'))) {
			var autocompleteSECreateLocation = new google.maps.places.Autocomplete(document.getElementById('location'));
			<?php include APPLICATION_PATH . '/application/modules/Seaocore/views/scripts/location.tpl'; ?>
		}
    
		checkDraft();
	});

	function checkDraft(){
		if($('draft')){
			if($('draft').value==1) {
				$("search-wrapper").style.display="none";
				$("search").checked= false;
        
        if($("creation_date-wrapper")) {
            $("creation_date-wrapper").style.display="none";   
        }    
        
			} else{
				$("search-wrapper").style.display="block";
				$("search").checked= true;
        
        if($("creation_date-wrapper")) {
            $("creation_date-wrapper").style.display="block";   
        }            
			}
		}
	}

  en4.core.runonce.add(function(){
     if('<?php echo $this->expiry_setting; ?>' !=1){
       document.getElementById("end_date_enable-wrapper").style.display = "none";
     }
    if($('end_date-date')){
      // check end date and make it the same date if it's too
      cal_end_date.calendars[0].start = new Date( $('end_date-date').value );
      // redraw calendar
      cal_end_date.navigate(cal_end_date.calendars[0], 'm', 1);
      cal_end_date.navigate(cal_end_date.calendars[0], 'm', -1);
    }

  });
  var updateTextFields = function(endsettings)
  {
    var endtime_element = document.getElementById("end_date-wrapper");
    endtime_element.style.display = "none";

    if (endsettings.value == 0)
    {
      endtime_element.style.display = "none";
      return;
    }

    if (endsettings.value == 1)
    {
      endtime_element.style.display = "block";
      return;
    }
  }

  en4.core.runonce.add(updateTextFields);
</script>
<?php
/* Include the common user-end field switching javascript */
echo $this->partial('_jsSwitch.tpl', 'fields', array(
        //'topLevelId' => (int) @$this->topLevelId,
        //'topLevelValue' => (int) @$this->topLevelValue
))
?>
 <?php //include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/navigation_views.tpl'; ?>
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
  <?php // CUSTOM WORK  ?>
  <?php if ( (!empty($this->packageQuota) && $this->packageListingCount >= $this->packageQuota ) || ($this->current_count >= $this->quota && !empty($this->quota) )): ?>
    <div class="tip"> 
      <span>
        <?php echo $this->translate("You have already created the maximum number of $this->listing_singular_lc allowed."); ?>
      </span>
    </div>
    <br/>
  <?php elseif($this->category_count > 0): ?>
    <?php if ($this->sitereview_render == 'sitereview_form'):?>
  <?php if (Engine_Api::_()->sitereview()->hasPackageEnable() && $this->PackageCount > 0):?>
	<h3><?php echo $this->translate("Post New $this->listing_singular_uc") ?></h3>
	<p><?php echo $this->translate("Create a $this->listing_singular_lc using these quick, easy steps and get going.");?></p>	
    <h4 class="sitereview_create_step"><?php echo $this->translate("2. Configure your $this->listing_singular_lc based on the package you have chosen."); ?></h4>
	  <div class='sitereviewpage_layout_right'>      
    	<div class="sitereview_package_page p5">          
        <ul class="sitereview_package_list">
        	<li class="p5">
          	<div class="sitereview_package_list_title">
              <h3><?php echo $this->translate('Package Details'); ?>: <?php echo $this->translate(ucfirst($this->package->title)); ?></h3>
            </div>           
            <div class="sitereview_package_stat"> 
              <span>
								<b><?php echo $this->translate("Price"). ": "; ?> </b>
        <?php if(isset ($this->package->price)):?>
          <?php if($this->package->price > 0):echo Engine_Api::_()->sitereview()->getPriceWithCurrency($this->package->price); else: echo $this->translate('FREE'); endif; ?>
        <?php endif;?>
             	</span>
             	<span>
                <b><?php echo $this->translate("Billing Cycle"). ": "; ?> </b>
                <?php echo $this->package->getBillingCycle() ?>
              </span>
              <span style="width: auto;">
              	<b><?php echo ($this->package->price > 0 && $this->package->recurrence > 0 && $this->package->recurrence_type != 'forever' ) ? $this->translate("Billing Duration"). ": ": $this->translate("Duration"). ": "; ?> </b>
               	<?php echo $this->package->getPackageQuantity() ; ?>
             	</span>
              <br />
              <span>
              	<b><?php echo $this->translate("Featured"). ": "; ?> </b>
               	<?php
                	if ($this->package->featured == 1)
                		echo $this->translate("Yes");
                	else
                  	echo $this->translate("No");
                ?>
             	</span>
              <span>
              	<b><?php echo $this->translate("Sponsored"). ": "; ?> </b>
               	<?php
                	if ($this->package->sponsored == 1)
                  	echo $this->translate("Yes");
                	else
                  	echo $this->translate("No");
             	 	?>
             	</span>
              <?php if($this->overview && Engine_Api::_()->authorization()->getPermission($this->viewer->level_id, 'sitereview_listing', "overview_listtype_"."$this->listingtype_id")):?>
                <span>
                 <b><?php echo $this->translate("Rich Overview"). ": "; ?> </b>
                 <?php
                  if ($this->package->overview == 1)
                    echo $this->translate("Yes");
                  else
                    echo $this->translate("No");
                 ?>
                </span>
              <?php endif;?>
              <?php if($this->location):?>
                <span>
                 <b><?php echo $this->translate("Map"). ": "; ?> </b>
                  <?php
                  if ($this->package->map == 1)
                    echo $this->translate("Yes");
                  else
                    echo $this->translate("No");
                 ?>
                </span>
              <?php endif;?>
              <?php if(Engine_Api::_()->authorization()->getPermission($this->viewer->level_id, 'sitereview_listing', "video_listtype_"."$this->listingtype_id")):?>
                <span>
                 <b><?php echo $this->translate("Videos"). ": "; ?> </b>
                  <?php
                  if ($this->package->video == 1)
                    if ($this->package->video_count)
                      echo $this->package->video_count;
                    else
                      echo $this->translate("Unlimited");
                  else
                    echo $this->translate("No");
                 ?>
                </span>
              <?php endif;?>
              <?php if(Engine_Api::_()->authorization()->getPermission($this->viewer->level_id, 'sitereview_listing', "photo_listtype_"."$this->listingtype_id")):?>
                <span>
                 <b><?php echo $this->translate("Photos"). ": "; ?> </b>
                  <?php
                  if ($this->package->photo == 1)
                    if ($this->package->photo_count)
                      echo $this->package->photo_count;
                    else
                      echo $this->translate("Unlimited");
                  else
                    echo $this->translate("No");
                 ?>
                </span>
              <?php endif;?>
              <?php if(!empty($this->allow_review) && $this->allow_review != 1 && Engine_Api::_()->authorization()->getPermission($this->viewer->level_id, 'sitereview_listing', "review_create_listtype_"."$this->listingtype_id")):?>
                <span>
                 <b><?php echo $this->translate("User Review"). ": "; ?> </b>
                  <?php
                  if ($this->package->user_review == 1)
                    echo $this->translate("Yes");
                  else
                    echo $this->translate("No");
                 ?>
                </span>
              <?php endif;?>
              <?php if($this->wishlist):?>
                <span>
                 <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite')):?>
      <b><?php echo $this->translate("Favourite"). ": "; ?> </b>
      <?php else:?>
      <b><?php echo $this->translate("Wishlist"). ": "; ?> </b>
      <?php endif;?>
                  <?php
                  if ($this->package->wishlist == 1)
                    echo $this->translate("Yes");
                  else
                    echo $this->translate("No");
                 ?>
                </span>
              <?php endif;?>
						</div>
						<div class="sitereview_list_details">
							<?php echo $this->translate($this->package->description); ?>
		        </div>
           <?php if($this->PackageCount > 1):?>
          	<div class="sitereview_create_link mtop10 clr">
           		<a href="<?php echo $this->url(array('action'=>'index'), "sitereview_package_listtype_$this->listingtype_id", true) ?>">&laquo; <?php echo $this->translate("Choose a different package"); ?></a>
          	</div>
           <?php endif;?>
          </li>
        </ul>
      </div>
    </div>
    <div class="sitereview_layout_left">
  <?php endif; ?>
      <?php echo $this->form->setAttrib('class', 'global_form sr_create_list_form')->render($this);?>
        <?php if (Engine_Api::_()->sitereview()->hasPackageEnable() && $this->PackageCount > 0):?>
					</div>
  	   <?php endif;?>
    <?php  else:?>
      <?php echo $this->translate($this->sitereview_formrender);?>
    <?php endif;?>
  <?php endif; ?>
</div>

<script type="text/javascript">
  if($('subcategory_id'))
    $('subcategory_id').style.display = 'none';
</script>

<script type="text/javascript">

  var getProfileType = function(category_id) {
    var mapping = <?php echo Zend_Json_Encoder::encode(Engine_Api::_()->getDbTable('categories', 'sitereview')->getMapping($this->listingtype_id, 'profile_type')); ?>;
    for(i = 0; i < mapping.length; i++) {
      if(mapping[i].category_id == category_id)
        return mapping[i].profile_type;
    }
    return 0;
  }

  var defaultProfileId = '<?php echo '0_0_' . $this->defaultProfileId ?>'+'-wrapper';
  if($type($(defaultProfileId)) && typeof $(defaultProfileId) != 'undefined') {
    $(defaultProfileId).setStyle('display', 'none');
  }

	if($('overview-wrapper')) {
		<?php
  echo $this->tinyMCESEAO()->render(array('element_id'=>'"overview"',
      'language' => $this->language,
      'upload_url' => $this->upload_url,
      'directionality' => $this->directionality));
  ?>
	}
	var show_editor = '<?php echo $this->show_editor;?>';
  if($('body-wrapper') && show_editor == 1) {
		<?php
  echo $this->tinyMCESEAO()->render(array('element_id'=>'"body"',
      'language' => $this->language,
      'upload_url' => $this->upload_url,
      'directionality' => $this->directionality));
  ?>
	}
</script>