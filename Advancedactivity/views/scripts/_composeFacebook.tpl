<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: _composeFacebook.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>

<?php 
    $settings = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.facebook');
    $facebook_apikey = @$settings['appid'];
    $facebook_secret = @$settings['secret'];
  // Disabled
  
  if((!Engine_Api::_()->getApi('settings', 'core')->getSetting('facebook.enable', Engine_Api::_()->getApi('settings', 'core')->core_facebook_enable == 'publish'? 1:0) || empty ($this->isAFFWIDGET)|| empty($facebook_secret) || empty($facebook_apikey))) {
    return;
  }
  
  
  //THIS IS THE SPECIAL CASE IF THE ADVANCED ACTIVITY UPDATE WIDGET ON THE CONTENT PROFILE PAGE THEN WE WILL CHECK EITHER ADMIN IS ALLOWED TO PUBLISH ON BOTH PLACE OF FACEBOOK PAGE AND FACEBOOK USER PROFILE OR NOT.
$fbpublishconfirmbox = '0-0-0';
$fblinkedpage = '';
if (Engine_Api::_()->core()->hasSubject()) {  
  $subject = Engine_Api::_()->core()->getSubject(); 
  $subjectType = Engine_Api::_()->core()->getSubject()->getType();
  $subjectPostFBArray = array('sitepage_page', 'sitebusiness_business', 'sitegroup_group', 'sitestore_store');
  if (in_array($subjectType, $subjectPostFBArray) && isset($subject->fbpage_url) && !empty($subject->fbpage_url)) {    
    $fblinkedpage = $subject->fbpage_url;
    //explode the subject type
    $subject_explode = explode("_", $subjectType);
    $subjectFbPostSettingVar = $subject_explode[0] . '.post' . $subject_explode[1];
    $fbLinking = Engine_Api::_()->getApi('settings', 'core')->getSetting($subjectFbPostSettingVar, 1);
    $publish_fb_array = array('0' => 1, '1' => 2);
    if ($fbLinking) {
      $fb_publish = Engine_Api::_()->getApi('settings', 'core')->getSetting($subject_explode[0] . '.publish.facebook', serialize($publish_fb_array));
      if (!empty($fb_publish)) {
        if(!is_array($fb_publish))
          $fb_publish = unserialize($fb_publish);
        $fbpublishconfirmbox = '1-' . @$fb_publish[0] . '-' . @$fb_publish[1];
      }
    }
  }
}
  // Add script
  $this->headScript()
      ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/composer_facebook.js');
?>

<script type="text/javascript">
  en4.core.runonce.add(function() {
    composeInstance.addPlugin(new Composer.Plugin.AdvFacebook({
      lang : {
        'Publish this on Facebook' : '<?php echo $this->string()->escapeJavascript($this->translate('Publish this on Facebook')) ?>',
        'Do not publish this on Facebook' : '<?php echo $this->string()->escapeJavascript($this->translate('Do not publish this on Facebook')) ?>'
      }
    }));    
  });
  var fbpublishconfirmbox = '<?php echo $fbpublishconfirmbox;?>';
  var fblinkedpage = '<?php echo $fblinkedpage;?>';
</script>
<style type="text/css">
.composer_facebook_toggle {
background-position:right !important;
padding-right:15px;

}
</style>
