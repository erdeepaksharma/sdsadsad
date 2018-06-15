<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: edit.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php
$this->headScript()
        ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Observer.js')
        ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.js')
        ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Local.js')
        ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Request.js');
?>

<?php $this->tinyMCESEAO()->addJS();?>

<script type="text/javascript">
  en4.core.runonce.add(function()
  {
    checkDraft();
    new Autocompleter.Request.JSON('tags', '<?php echo $this->url(array('module' => 'seaocore', 'controller' => 'index', 'action' => 'tag-suggest', 'resourceType' => 'sitereview_listing'), 'default', true) ?>', {
      'postVar' : 'text',
      'minLength': 1,
      'selectMode': 'pick',
      'autocompleteType': 'tag',
      'className': 'tag-autosuggest',
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
  en4.core.runonce.add(function(){
    var endtime_element = document.getElementById("end_date-wrapper");
    if('<?php echo $this->expiry_setting; ?>' !='1'){
      document.getElementById("end_date_enable-wrapper").style.display = "none";
      endtime_element.style.display = "none";
    }else{
      if($("end_date_enable-1").checked){
        endtime_element.style.display = "block";
      }else{
        endtime_element.style.display = "none";
      }
    }
    if($('end_date-date')){
      // check end date and make it the same date if it's too
      cal_end_date.calendars[0].start = new Date( "<?php echo (string) date('Y-m-d').' 00:00:00'; ?>" );
      // redraw calendar
      cal_end_date.navigate(cal_end_date.calendars[0], 'm', 1);
      cal_end_date.navigate(cal_end_date.calendars[0], 'm', -1);
    }
  });
 
</script>

<?php
/* Include the common user-end field switching javascript */
echo $this->partial('_jsSwitch.tpl', 'fields', array(
        //'topLevelId' => (int) @$this->topLevelId,
        //'topLevelValue' => (int) @$this->topLevelValue
))
?>

<?php include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/_DashboardNavigation.tpl'; ?>
<div class="sr_dashboard_content">

	<?php echo $this->partial('application/modules/Sitereview/views/scripts/dashboard/header.tpl', array('sitereview'=>$this->sitereview));?>
	<div class="sr_create_list_form">
  	<?php echo $this->form->render(); ?>
  </div>	
</div>
</div>
<script type="text/javascript">
  
var category_edit = '<?php echo $this->category_edit ?>';  

var prefieldForm = function() {
<?php
  $defaultProfileId = "0_0_" . $this->defaultProfileId;
  foreach ($this->form->getSubForms() as $subForm) {
    foreach ($subForm->getElements() as $element) {

      $elementGetName = $element->getName();
      $elementGetValue = $element->getValue();
      $elementGetType = $element->getType();

      if ($elementGetName != $defaultProfileId && $elementGetName != '' && $elementGetName != null && $elementGetValue != '' && $elementGetValue != null) {

      if (!is_array($elementGetValue) && $elementGetType == 'Engine_Form_Element_Radio') {
        ?>
                        $('<?php echo $elementGetName . "-" . $elementGetValue ?>').checked = 1; 
      <?php } elseif (!is_array($elementGetValue) && $elementGetType == 'Engine_Form_Element_Checkbox') { ?>
                        $('<?php echo $elementGetName ?>').checked = <?php echo $elementGetValue ?>;
          <?php
          } elseif (is_array($elementGetValue) && ($elementGetType == 'Engine_Form_Element_MultiCheckbox' || $elementGetType == 'Fields_Form_Element_Ethnicity' || $elementGetType == 'Fields_Form_Element_LookingFor' || $elementGetType == Fields_Form_Element_PartnerGender)) {
            foreach ($elementGetValue as $key => $value) {
              ?>
                                $('<?php echo $elementGetName . "-" . $value ?>').checked = 1;
            <?php
            }
          } elseif (is_array($elementGetValue) && $elementGetType == 'Engine_Form_Element_Multiselect') {
            foreach ($elementGetValue as $key => $value) {
              $key_temp = array_search($value, array_keys($element->options));
              if ($key !== FALSE) {
                ?>
                                    $('<?php echo $elementGetName ?>').options['<?php echo $key_temp ?>'].selected = 1;
              <?php
              }
            }
          } elseif (!is_array($elementGetValue) && ($elementGetType == 'Engine_Form_Element_Text' || $elementGetType == 'Engine_Form_Element_Textarea' || $elementGetType == 'Fields_Form_Element_AboutMe' || $elementGetType == 'Fields_Form_Element_Aim' || $elementGetType == 'Fields_Form_Element_City' || $elementGetType == 'Fields_Form_Element_Facebook' || $elementGetType == 'Fields_Form_Element_FirstName' || $elementGetType == 'Fields_Form_Element_Interests' || $elementGetType == 'Fields_Form_Element_LastName' || $elementGetType == 'Fields_Form_Element_Location' || $elementGetType == 'Fields_Form_Element_Twitter' || $elementGetType == 'Fields_Form_Element_Website' || $elementGetType == 'Fields_Form_Element_ZipCode')) {
            ?>
                            $('<?php echo $elementGetName ?>').value = "<?php echo $this->string()->escapeJavascript($elementGetValue, false) ?>";
          <?php } elseif (!is_array($elementGetValue) && $elementGetType != 'Engine_Form_Element_Date' && $elementGetType != 'Fields_Form_Element_Birthdate' && $elementGetType != 'Engine_Form_Element_Heading') { ?>
                            $('<?php echo $elementGetName ?>').value = "<?php echo $this->string()->escapeJavascript($elementGetValue, false) ?>";
          <?php
          }
        }
      }
    }
?>
  }

    window.addEvent('domready', function() {
      <?php if ($this->profileType): ?>			
        $('<?php echo '0_0_' . $this->defaultProfileId ?>').value= <?php echo $this->profileType ?>;
        changeFields($('<?php echo '0_0_' . $this->defaultProfileId ?>'));
      <?php endif; ?>
    });

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

    if(category_edit == 1) {
    if($('subcategory_id-wrapper'))
      $('subcategory_id-wrapper').style.display = 'block';
    if($('subcategory_id-label'))
      $('subcategory_id-label').style.display = 'block';
    if($('subsubcategory_id-wrapper'))
      $('subsubcategory_id-wrapper').style.display = 'block';
    if($('subsubcategory_id-label'))
      $('subsubcategory_id-label').style.display = 'block';

    var subcatid = '<?php echo $this->sitereview->subcategory_id; ?>';

    var show_subcat = 1;

    var subcategory = function(category_id, subcatid, subcatname,subsubcatid)
    {
      changesubcategory(subcatid, subsubcatid);
      if($('buttons-wrapper')) {
        $('buttons-wrapper').style.display = 'none';
      }
      if(subcatid == '')
        if($('subcategory_id-wrapper'))
          $('subcategory_id-wrapper').style.display = 'block';

      var url = '<?php echo $this->url(array('action' => 'sub-category'), "sitereview_general_listtype_$this->listingtype_id", true); ?>';
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
          clear('subcategory_id');
          var  subcatss = responseJSON.subcats;
          addOption($('subcategory_id')," ", '0');
          for (i=0; i< subcatss.length; i++) {
            addOption($('subcategory_id'), subcatss[i]['category_name'], subcatss[i]['category_id']);
            if(show_subcat == 0) {
              if($('subcategory_id'))
                $('subcategory_id').disabled = 'disabled';
              if($('subsubcategory_id'))
                $('subsubcategory_id').disabled = 'disabled';
            }
            if($('subcategory_id')) {
              $('subcategory_id').value = subcatid;
            }
          }

          if(category_id == 0) {
            clear('subcategory_id');
            if($('subcategory_id'))
              $('subcategory_id').style.display = 'none';
            if($('subcategory_id-label'))
              $('subcategory_id-label').style.display = 'none';
          }
        }
      }), {
          "force":true
        });
    };

    var changesubcategory = function(subcatid, subsubcatid)
    {

      $('subsubcategory_id-wrapper').style.display = 'none';
      if(cateDependencyArray.indexOf(subcatid) == -1 || subsubcatid == 0)
        return;

      if($('buttons-wrapper')) {
        $('buttons-wrapper').style.display = 'none';
      }
      $('subsubcategory_backgroundimage').innerHTML = '<div class="form-label"></div><div  class="form-element"><img src="<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Seaocore/externals/images/core/loading.gif" /></center></div>';

      $('subsubcategory_backgroundimage').style.display = 'block';
      if(subsubcatid == '') {
        if($('subsubcategory_id-wrapper'))
          $('subsubcategory_id-wrapper').style.display = 'none';
      }
      var url = '<?php echo $this->url(array('action' => 'subsub-category'), "sitereview_general_listtype_$this->listingtype_id", true); ?>';
      var request = new Request.JSON({
        url : url,
        data : {
          format : 'json',
          subcategory_id_temp : subcatid
        },
        onSuccess : function(responseJSON) {
          $('subsubcategory_backgroundimage').style.display = 'none';
          if($('buttons-wrapper')) {
            $('buttons-wrapper').style.display = 'block';
          }
          clear('subsubcategory_id');
          var  subsubcatss = responseJSON.subsubcats;          
          addSubOption($('subsubcategory_id')," ", '0');
          for (i=0; i< subsubcatss.length; i++) {

            addSubOption($('subsubcategory_id'), subsubcatss[i]['category_name'], subsubcatss[i]['category_id']);
            if($('subsubcategory_id')) {
              $('subsubcategory_id').value = subsubcatid;
            }
          }
        }
      });
      request.send();
    };

    var cat = '<?php echo $this->sitereview->category_id ?>';
    if(cat != '') {
      subsubcatid = '<?php echo $this->sitereview->subsubcategory_id; ?>';
      var subcatname = '<?php echo $this->subcategory_name; ?>';			 
      subcategory(cat, subcatid, subcatname,subsubcatid);
    }
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