<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: add-review-day-item.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
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

<script type="text/javascript">

	function getUrlParam(name) {
    var regexS;
    var regexl;
    var results;

    name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
    regexS = "[\\?&]"+name+"=([^&#]*)";
    regex = new RegExp(regexS);
    results = regex.exec (parent.window.location.href);

    if ( results == null ) {
        return "";
    } else {
        return results[1];
    }
	}

  en4.core.runonce.add(function()	{
    $('review_id-wrapper').style.display = 'none';
		var pageId = getUrlParam('page');
    var contentAutocomplete = new Autocompleter.Request.JSON('review_title', '<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'admin-settings', 'action' => 'get-reviews'), 'default', true) ?>', {
      'postVar' : 'text',
      'minLength': 1,
      'selectMode': 'pick',
      'autocompleteType': 'tag',
      'className': 'seaocore-autosuggest',
      'customChoices' : true,
      'filterSubset' : true,
      'multiple' : false,
      'injectChoice': function(token){
        var choice = new Element('li', {'class': 'autocompleter-choices1', 'html': token.photo, 'id':token.label});
        new Element('div', {'html': this.markQueryValue(token.label),'class': 'autocompleter-choice1'}).inject(choice);
        this.addChoiceEvents(choice).inject(this.choices);
        choice.store('autocompleteChoice', token);
      }
    });
    contentAutocomplete.addEvent('onSelection', function(element, selected, value, input) {
      document.getElementById('review_id').value = selected.retrieve('autocompleteChoice').id;
    });
  });
</script>

<div class="form-wrapper">
	<div class="form-label"></div>
	<div id="review_title-element" class="form-element">
	  <?php echo "Start typing the name of the Review."; ?>
		<input type="text" style="width:300px;" class="text" value="" id="review_title" name="review_title">
	</div>
</div>