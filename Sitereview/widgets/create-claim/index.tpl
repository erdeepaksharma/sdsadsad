<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2010-2011 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 2011-05-05 9:40:21Z SocialEngineAddOns $
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
    var contentAutocomplete = new Autocompleter.Request.JSON('title', '<?php echo $this->url(array('action' => 'get-listings'), 'sitereview_claim_listtype_'.$this->listingtype_id, true) ?>', {
      'postVar' : 'text',
      'minLength': 1,
      'selectMode': 'pick',
      'autocompleteType': 'tag',
      'className': 'searchbox_autosuggest seaocore-autosuggest tag-autosuggest',
      'customChoices' : true,
      'filterSubset' : true,
      'multiple' : false,
      'injectChoice': function(token){
        var choice = new Element('li', {'class': 'autocompleter-choices', 'html': token.photo, 'id':token.label});
        new Element('div', {'html': this.markQueryValue(token.label),'class': 'autocompleter-choice1'}).inject(choice);
        this.addChoiceEvents(choice).inject(this.choices);
        choice.store('autocompleteChoice', token);

      }
    });

    contentAutocomplete.addEvent('onSelection', function(element, selected, value, input) {
      $('listing_id').value = selected.retrieve('autocompleteChoice').id;
    });

  });
</script>

<div class="layout_middle">
  <?php if ($this->successmessage): ?>
    <ul class="form-notices" >
      <li>
        <?php echo $this->translate('Your request has been sent successfully. The site administrator will act on your request and you will receive an email correspondingly. You can also track your claims over %1$shere%2$s.', '<a href="' . $this->url(array('action' => 'my-listings'), 'sitereview_claim_listtype_'.$this->listingtype_id) . '">', '</a>'); ?>
      </li>
    </ul>
  <?php else: ?>
    <?php if ($this->showtip) : ?>
      <?php
      echo '<div class="tip"><span>' . sprintf(Zend_Registry::get('Zend_Translate')->_("There are no $this->listing_plural_lc to be claimed yet.")) . '</span></div>';
      ?>
    <?php else: ?>
      <div>
        <?php echo $this->form->render($this); ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
