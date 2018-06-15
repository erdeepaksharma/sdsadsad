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
  $this->headLink()
				->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');
?>

<!--WE ARE NOT USING STATIC BASE URL BECAUSE SOCIAL ENGINE ALSO NOT USE FOR THIS JS-->
<!--CHECK HERE Engine_View_Helper_TinyMce => protected function _renderScript()-->
<?php $this->tinyMCESEAO()->addJS();?>

<script type="text/javascript">
	function doRating(element_id, ratingparam_id, classstar) {
   
		$(element_id + '_' + ratingparam_id).getParent().getParent().className= 'sr-es-box rating-box ' + classstar;
		$('review_rate_' + ratingparam_id).value = $(element_id + '_' + ratingparam_id).getParent().id;
	}

	function doDefaultRating(element_id, ratingparam_id, classstar) {
   
		$(element_id + '_' + ratingparam_id).getParent().getParent().className= 'sr_eg_rating ' + classstar;
		$('review_rate_' + ratingparam_id).value = $(element_id + '_' + ratingparam_id).getParent().id;
	}
</script>

<?php include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/navigation_views.tpl'; ?>



<div class='layout_middle sr_editor_review_form'>
  <div class="sr_view_top">
    <?php echo $this->htmlLink($this->sitereview->getHref(array('profile_link' => 1)), $this->itemPhoto($this->sitereview, 'thumb.icon', '', array('align' => 'left'))) ?>
    <h2>	
      <?php echo $this->sitereview->__toString() ?>	
      <?php echo $this->translate('&raquo; ');?>
      <?php echo $this->translate('Editor Review') ?>
    </h2>
  </div>
	<?php echo $this->form->setAttrib('class', 'sr_review_form global_form')->render($this); ?>

	<div id="addPageLink" class="form-wrapper">
		<div class="form-label">&nbsp;</div>
		<div class="form-element">
			<a href="javascript: void(0);" onclick="return addAnotherPage('');"><b><?php echo $this->translate("Add another page") ?></b>      </a>
		</div>
	</div>

</div>

<script type="text/javascript">

	var optionParent = $('body').getParent().getParent();
	$('addPageLink').inject(optionParent, 'before');
  var first_time_load = '<?php echo $this->first_time_load; ?>';

	addPageLink = $('addPageLink');

	var count = 1;

	<?php foreach($this->bodyElementValue as $value):?>
		//addAnotherPage('<?php //echo $this->string()->escapeJavascript($value, false) ?>'); 
    //addAnotherPage("<?php //echo str_replace('"','\\"',$value) ?>");
    addAnotherPage(<?php echo Zend_Json_Encoder::encode($value) ?>);
	<?php endforeach;?>
    
  if(first_time_load == 1) {  
    addAnotherPage('');
  }

	function addAnotherPage(pre_field_value) {

		// START CATEGORY CODE
		var textarea = document.createElement("textarea");
		textarea.id = 'body_'+count;
		textarea.name = 'body_'+count;

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

		var elementDiv = document.createElement("div");
		elementDiv.id = 'body_'+count+'-element';
		elementDiv.setAttribute("class", "form-element");

		var fleftDiv = document.createElement("div");
		fleftDiv.setAttribute("class", "fleft");

		textarea.inject(fleftDiv);
		fleftDiv.inject(elementDiv);
		labelDiv.inject(wrapperDiv);
		labelChieldDiv.inject(labelDiv);
		elementDiv.inject(wrapperDiv);
		wrapperDiv.inject(addPageLink, 'before');

		addTinyMCE('body_'+count);

		count = count+1;
	}

	function addTinyMCE(element_id) {
      
  <?php
  echo $this->tinyMCESEAO()->render(array('element_id'=>'element_id',
      'language' => $this->language,
      'directionality' => $this->directionality,
      'upload_url' => $this->upload_url));
  ?>
		
	}
</script>

<?php include APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/editorReviewElements.tpl'; ?>
