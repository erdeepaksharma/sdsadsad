<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitetagcheckin
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _composerCheckin.tpl 6590 2012-08-20 9:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php 
if ((empty($this->isAFFWIDGET) &&  empty($this->isAAFWIDGETMobile))): return; endif;

$this->headScript()
        ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Observer.js')
        ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.js')
        ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Local.js')
        ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Request.js')        
				;

    $this->headScript()
            ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/composer_feed_tags.js');
    $subject = $this->subject();
    if (!empty($subject)) {
        $subjectType = $subject->getType();
    }          
    if(!(empty($subject) || (!empty($subject) && $subjectType == 'user'))){
        return;
    }
?>
<script type="text/javascript">
  en4.core.runonce.add(function() {
    composeInstance.addPlugin(new Composer.Plugin.AafFeedTags({
      title : '<?php echo $this->string()->escapeJavascript($this->translate("Tag Friends")) ?>',
      enabled: true,
      allowEmpty : true,
      lang : {
        'Who are you with?' : '<?php echo $this->string()->escapeJavascript($this->translate("Who are you with?")) ?>',
        'Tag Friends':'<?php echo $this->string()->escapeJavascript($this->translate("Tag Friends")) ?>'
      },
      suggestOptions : {
        'url' : en4.core.baseUrl+'advancedactivity/friends/suggest',
        'data' : {
          'format' : 'json'
        }
      }
    }));
  });
 
</script>