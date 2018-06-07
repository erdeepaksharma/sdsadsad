<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitetagcheckin
 * @copyright  Copyright 2009-2010 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2010-11-04 9:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php
$this->headTranslate(array('Add People', 'with :'));

    $this->headScript()
            ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/feed-tags-mobile.js');
 
?>
<script type="text/javascript">
  en4.core.runonce.add(function() {
   //new Asset.javascript('');
    composeInstance.addPlugin(new Composer.Plugin.AddFriendTag({
      title : '<?php echo $this->string()->escapeJavascript($this->translate('Add People')) ?>',
      enabled: true,    
      lang : {
        'Add People' : '<?php echo $this->string()->escapeJavascript($this->translate('Add People')) ?>',
        'with :' : '<?php echo $this->string()->escapeJavascript($this->translate('with :')) ?>',
        'Enter the user name': '<?php echo $this->string()->escapeJavascript($this->translate('Enter the user name')) ?>'
          
      }
     
    }));
  });
  function initCheckinSitetag() {}
</script>