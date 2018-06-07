<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _composerFeeling.tpl 6590 2012-08-20 9:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php if( empty($this->isAFFWIDGET) ) : return;
endif; ?>
<?php
if( !Engine_Api::_()->authorization()->isAllowed('advancedactivity_feed', $this->viewer(), 'aaf_add_feeling_enable') ) {
  return;
}
?>
<?php
if( (empty($this->isAFFWIDGET) && empty($this->isAAFWIDGETMobile) ) ):
  return;
endif;

$this->headScript()
  ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Observer.js')
  ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.js')
  ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Local.js')
  ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/composer_feeling.js');
?>
<script type="text/javascript">
  en4.core.runonce.add(function () {
    composeInstance.addPlugin(new Composer.Plugin.AafFeeling({
      title: '<?php echo $this->string()->escapeJavascript($this->translate("Feeling/Activity")) ?>',
      enabled: true,
      allowEmpty: true,
      lang: {
        'What are you doing?': '<?php echo $this->string()->escapeJavascript($this->translate("What are you doing?")) ?>',
        'Feeling/Activity': '<?php echo $this->string()->escapeJavascript($this->translate("Feeling/Activity")) ?>'
      },
      suggestOptions: {
      }
    }));
  });

</script>