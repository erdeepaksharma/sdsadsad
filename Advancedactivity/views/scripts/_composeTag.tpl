<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: _composeTag.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>
<?php
$settingsApi = Engine_Api::_()->getApi('settings', 'core');
if ( !empty($settingsApi->aaf_composer_value) && (($settingsApi->aaf_list_view_value + $settingsApi->aaf_publish_str_value) != $settingsApi->aaf_composer_value)) :
?>
<script type="text/javascript">
  en4.core.runonce.add(function() {
    composeInstance='undefined';
   });
</script>
<?php  endif; ?>
 <?php if(empty ($this->isAFFWIDGET)):
   return;
 endif ?>
<?php  if(Engine_Api::_()->getApi('settings', 'core')->getSetting('aaf.feed.tagging',true)): ?>
<?php $this->headScript()
    ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Observer.js')
    ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.js')
    ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Local.js')
    ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Request.js')
    ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/composer_tag.js') ?>

<script type="text/javascript">
  en4.core.runonce.add(function() {
      var plugin = new Composer.Plugin.Aaftag({
      enabled:true,
      suggestOptions : {
        'url' : en4.core.baseUrl+'advancedactivity/friends/suggest-tag/includeSelf/1',
        'postData' : {
          'format' : 'json',
           'subject' : en4.core.subject.guid
        },
        'maxChoices':'<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('aaf.feed.suggest.limit',10); ?>'
      },
      'suggestProto' : 'request.json'
    });
    <?php if($this->forEdit) : ?>
      <?php $tags = array(); ?>
      <?php if(!empty($this->action->params['tags'])):?>
        <?php  foreach($this->action->params['tags'] as $guid => $label): ?>
        <?php    $tags [] = array('guid' => $guid, 'label' => $label); ?>
        <?php  endforeach; ?>
      <?php endif; ?>
    document.retrieve('editComposeInstance<?php echo $this->forEdit ?>').addPlugin(plugin);
    plugin.mapTags(<?php echo $this->jsonInline($tags) ?>);
    document.retrieve('editComposeInstance<?php echo $this->forEdit ?>').setHighlighterContent();
    <?php else: ?>
    composeInstance.addPlugin(plugin);
    <?php endif; ?>
  });
</script>
<?php endif; ?>