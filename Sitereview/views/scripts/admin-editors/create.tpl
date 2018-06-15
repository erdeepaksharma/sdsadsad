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
  $this->headScript()
      ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Observer.js')
      ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.js')
      ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Local.js')
      ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Request.js');
?>
<script type="text/javascript">
	en4.core.runonce.add(function()
	{ 
		var contentAutocomplete = new Autocompleter.Request.JSON('title', '<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'admin-editors', 'action' => 'get-member', 'listingtype_id' => 0), 'default', true) ?>', {
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
			document.getElementById('user_id').value = selected.retrieve('autocompleteChoice').id;
		});

	});
</script>

<h2>
  <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) { echo $this->translate('Reviews & Ratings - Multiple Listing Types Plugin'); } else { echo $this->translate('Reviews & Ratings Plugin'); }?>
</h2>

<?php if( count($this->navigation) ): ?>
  <div class='seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
  </div>
<?php endif; ?>

<img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/back.png" class="icon" />
<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'editors', 'action' => 'manage'), $this->translate('Back to Manage Editors'), array('class'=> 'buttonlink', 'style'=> 'padding-left:0px;')) ?>
<br /><br />

<div class='seaocore_settings_form'>
  <div class='settings'> <?php echo $this->form->render($this) ?> </div>
</div>