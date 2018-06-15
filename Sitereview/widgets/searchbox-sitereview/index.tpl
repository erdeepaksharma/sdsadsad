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
<?php 
	$this->headLink()->appendStylesheet($this->layout()->staticBaseUrl
  	              . 'application/modules/Sitereview/externals/styles/style_sitereview.css');
?>
<ul class="seaocore_sidebar_list">
	<li>
		<?php echo $this->form->setAttrib('class', 'sitereview-search-box')->render($this) ?>
	</li>
</ul>	

<?php
  $this->headScript()
        ->appendFile($this->layout()->staticBaseUrl .  'externals/autocompleter/Observer.js')
        ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.js')
        ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Local.js')
        ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Request.js');
?>

<script type="text/javascript">
  en4.core.runonce.add(function()
  {
    var item_count = 0;
    var contentAutocomplete = new Autocompleter.Request.JSON('title', '<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'index', 'action' => 'ajax-search'), "default", true) ?>', {
      'postVar' : 'text',
      'minLength': 1,
      'selectMode': 'pick',
     
      'autocompleteType': 'tag',
      'className': 'seaocore-autosuggest tag-autosuggest',
      'customChoices' : true,
      'filterSubset' : true,
      'multiple' : false,
      postData : {
        listingtype_id: <?php echo $this->listingtype_id ?>,
      },
      'injectChoice': function(token) {
	      if(typeof token.label != 'undefined' ) {
          if (token.sitereview_url != 'seeMoreLink') {
            var choice = new Element('li', {'class': 'autocompleter-choices1', 'html': token.photo, 'id':token.label, 'sitereview_url':token.sitereview_url, onclick:'javascript:getPageResults("'+token.sitereview_url+'")'});
            new Element('div', {'html': this.markQueryValue(token.label),'class': 'autocompleter-choice'}).inject(choice);
            this.addChoiceEvents(choice).inject(this.choices);
            choice.store('autocompleteChoice', token);
          }
          if(token.sitereview_url == 'seeMoreLink' && <?php echo $this->listingtype_id ?> > 0) {
            var title = $('title').value;
            var choice = new Element('li', {'class': 'autocompleter-choices1', 'html': '', 'id':'stopevent', 'sitereview_url':''});
            new Element('div', {'html': 'See More Results for '+title ,'class': 'autocompleter-choicess', onclick:'javascript:Seemore()'}).inject(choice);
            this.addChoiceEvents(choice).inject(this.choices);
            choice.store('autocompleteChoice', token);
          }
         }
       }
    });

    contentAutocomplete.addEvent('onSelection', function(element, selected, value, input) {
      window.addEvent('keyup', function(e) {
        if(e.key == 'enter') {
          if(selected.retrieve('autocompleteChoice') != 'null' ) {
            var url = selected.retrieve('autocompleteChoice').sitereview_url;
            if (url == 'seeMoreLink') {
              Seemore();
            }
            else {
              window.location.href=url;
            }
          }
        }
      });      
    });
  });
  
  function Seemore() {
    $('stopevent').removeEvents('click');
    <?php if($this->listingtype_id > 0): ?>
    var url = '<?php echo $this->url(array('action' => 'index'), "sitereview_general_listtype_$this->listingtype_id", true); ?>';
  	window.location.href= url + "?search=" + encodeURIComponent($('title').value);
    <?php endif; ?>
  }

  function getPageResults(url) {
    var listingtype_id = <?php echo $this->listingtype_id ?>;
    if(url != 'null' ) {
      if (url == 'seeMoreLink' && listingtype_id > 0) {
        Seemore();
      }
      else {
        window.location.href=url;
      }
    }
  }
</script>