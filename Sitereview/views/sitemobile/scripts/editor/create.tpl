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
$breadcrumb = array(
    array("href"=>$this->sitereview->getHref(),"title"=>$this->sitereview->getTitle(),"icon"=>"arrow-r"),
    array("href"=>$this->sitereview->getHref(),"title"=>"Editor Review","icon"=>"arrow-d")
     );
echo $this->breadcrumb($breadcrumb);
?>


<script type="text/javascript">
	function doRating(element_id, reviewcat_id, classstar) {
		$('#'+element_id + '_' + reviewcat_id).parent().parent().removeClass().addClass('sr-rating-box sr-es-box ' + classstar);
		$('#review_rate_' + reviewcat_id).val($('#'+element_id + '_' + reviewcat_id).parent().attr("id"));
	}

	function doDefaultRating(element_id, reviewcat_id, classstar) {
		$('#'+element_id + '_' + reviewcat_id).parent().parent().removeClass().addClass('sr_eg_rating ' + classstar);
		$('#review_rate_' + reviewcat_id).val($('#'+element_id + '_' + reviewcat_id).parent().attr("id"));
	}
</script>

<div class='layout_middle'>
	<?php echo $this->form->setAttrib('class', 'sr_review_form global_form')->render($this); ?>
	<div id="addPageLink" class="form-wrapper">
		<div class="form-label">&nbsp;</div>
		<div class="form-element">
			<a href="javascript: void(0);" onclick="return addAnotherPage('');"><b><?php echo $this->translate("Add another page") ?></b>      </a>
		</div>
	</div>

</div>

<script type="text/javascript">

	var optionParent = $('#body').parent().parent();
	$('#addPageLink').inject(optionParent, 'before');
  var first_time_load = '<?php echo $this->first_time_load; ?>';

	addPageLink = $('#addPageLink');

	var count = 1;

	<?php foreach($this->bodyElementValue as $value):?>
    addAnotherPage(<?php echo Zend_Json_Encoder::encode($value) ?>);
	<?php endforeach;?>
    
  if(first_time_load == 1) {  
    addAnotherPage('');
  }

	function addAnotherPage(pre_field_value) {

		//START CATEGORY CODE
		var textarea = document.createElement("textarea");
		textarea.id = 'body_'+count;
		textarea.name = 'body_'+count;
    textarea.setAttribute("class", "ui-input-text ui-body-c ui-corner-all ui-shadow-inset");
		if(pre_field_value != '') {
			textarea.value = pre_field_value;
		}
 
		var wrapperDiv = document.createElement("div");
		wrapperDiv.id = 'body_'+count+'-wrapper';
		wrapperDiv.setAttribute("class", "form-wrapper");
 
		var labelDiv = document.createElement("div");
		labelDiv.id = 'body_'+count+'-label';
		labelDiv.setAttribute("class", "form-label");
 
		var labelChieldDiv = document.createElement("label");
		labelChieldDiv.innerHTML = '<?php echo $this->translate("Page ")?>' + count + '<?php echo $this->translate(" Summary");?>'
    labelChieldDiv.setAttribute("for", "body_1");
    labelChieldDiv.setAttribute("class", "optional ui-input-text");
		var elementDiv = document.createElement("div");
		elementDiv.id = 'body_'+count+'-element';
		elementDiv.setAttribute("class", "form-element");
    $(elementDiv).append(textarea);
    $(wrapperDiv).append(labelDiv);
    $(labelDiv).append(labelChieldDiv);
    $(wrapperDiv).append(elementDiv);
    $(addPageLink).before(wrapperDiv);
		count = count+1;
	}

</script>

<?php include APPLICATION_PATH . '/application/modules/Sitereview/views/sitemobile/scripts/editorReviewElements.tpl'; ?>