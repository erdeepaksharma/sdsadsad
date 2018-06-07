<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: _composePhoto.tpl 9325 2011-09-27 00:11:15Z john $
 * @author     Sami
 */
?>
<?php if(empty ($this->isAFFWIDGET)) :  return;  endif; ?>
<?php 
    if( !Engine_Api::_()->authorization()->isAllowed('advancedactivity_feed', $this->viewer(), 'aaf_advertise_enable') ) {
      return;
    }
    $allowedModules = Engine_Api::_()->getApi('settings','core')->getSetting('aaf.allowed.buysell.content', array('user'));
    $coreApi = Engine_Api::_()->core();
    if($coreApi->hasSubject()){
        $type = ($coreApi->getSubject()->getType() == 'sitereview_listing') ? 'sitereview_listingtype_' . $coreApi->getSubject()->listingtype_id : $coreApi->getSubject()->getType();
    }else{
        $type = 'user';
    }
    if(!in_array($type,$allowedModules)){
      return;
    }
?>
<?php
//GET API KEY
$apiKey = Engine_Api::_()->seaocore()->getGoogleMapApiKey();
if($apiKey)
$this->headScript()->appendFile("https://maps.googleapis.com/maps/api/js?libraries=places&key=$apiKey");
?>
<?php
  $this->headScript()
    ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/composer_sell.js')
    ->appendFile($this->layout()->staticBaseUrl . 'externals/fancyupload/Swiff.Uploader.js')
    ->appendFile($this->layout()->staticBaseUrl . 'externals/fancyupload/Fx.ProgressBar.js')
    ->appendFile($this->layout()->staticBaseUrl . 'externals/fancyupload/FancyUpload2.js');
  $this->headLink()
    ->appendStylesheet($this->layout()->staticBaseUrl . 'externals/fancyupload/fancyupload.css');
  $this->headTranslate(array(
    'Overall Progress ({total})', 'File Progress', 'Uploading "{name}"',
    'Upload: {bytesLoaded} with {rate}, {timeRemaining} remaining.', '{name}',
    'Remove', 'Click to remove this entry.', 'Upload failed',
    '{name} already added.',
    '{name} ({size}) is too small, the minimal file size is {fileSizeMin}.',
    '{name} ({size}) is too big, the maximal file size is {fileSizeMax}.',
    '{name} could not be added, amount of {fileListMax} files exceeded.',
    '{name} ({size}) is too big, overall filesize of {fileListSizeMax} exceeded.',
    'Server returned HTTP-Status <code>#{code}</code>',
    'Security error occurred ({text})',
    'Error caused a send or load operation to fail ({text})',
  ));
?>
   <?php  
          if(Engine_Api::_()->hasModuleBootstrap('sitemulticurrency')){
                $currency = Engine_Api::_()->sitemulticurrency()->getSelectedCurrency(1);
          }else {
                $currency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD'); 
          }
   ?>
   <?php $url = $this->url(array('module'=>'advancedactivity','controller'=>'buy-sell','action' => 'create'), "default", true) ?>
  
   
   
<script type="text/javascript">
  var labels = new Object();
   <?php $form = new Advancedactivity_Form_BuySell_Create();
         foreach($form->getSubForm('fields')->getElements() as $el) :      ?>
            labels['fields-<?php echo $el->getName() ?>'] = '<?php echo $el->getLabel() ?>'; 
   <?php endforeach; ?>
   
  en4.core.runonce.add(function() {
    var type = 'wall';
    <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitealbum') && Engine_Api::_()->advancedactivity()->checkVersion(Engine_Api::_()->getDbtable('modules', 'core')->getModule('sitealbum')->version, '4.8.5')): ?>
			var requestOptionsURL = en4.core.baseUrl + 'sitealbum/album/compose-upload/type/'+type; 
			var fancyUploadOptionsURL = en4.core.baseUrl + 'sitealbum/album/compose-upload/format/json/type/'+type;
    <?php else: ?>
			var requestOptionsURL = en4.core.baseUrl + 'album/album/compose-upload/type/'+type;
			var fancyUploadOptionsURL = en4.core.baseUrl + 'album/album/compose-upload/format/json/type/'+type;
    <?php endif; ?>
    if (composeInstance.options.type) type = composeInstance.options.type;
    composeInstance.addPlugin(new Composer.Plugin.Sell({
      title : '<?php echo $this->string()->escapeJavascript($this->translate('Sell Something')) ?>',
      lang : {
        'Sell Something' : '<?php echo $this->string()->escapeJavascript($this->translate('Sell Something')) ?>',
        'Select File' : '<?php echo $this->string()->escapeJavascript($this->translate('Select File')) ?>',
        'cancel' : '<?php echo $this->string()->escapeJavascript($this->translate('cancel')) ?>',
        'Loading...' : '<?php echo $this->string()->escapeJavascript($this->translate('Loading...')) ?>',
        'Continue' : '<?php echo $this->string()->escapeJavascript($this->translate('Continue')) ?>',
        'Unable to upload photo. Please click cancel and try again': '<?php echo $this->string()->escapeJavascript($this->translate('Unable to upload photo. Please click cancel and try again')) ?>'
      },
      requestOptions : {
        'url'  : requestOptionsURL,
        'flashEnable' : true,
        'sellURL': '<?php echo $url ?>',
        'currency': '<?php echo $currency ?>',
        'customLabels':labels
      },     
      fancyUploadOptions : {
        'url'  : fancyUploadOptionsURL,
        'path' : en4.core.basePath + 'externals/fancyupload/Swiff.Uploader.swf'
      }
    }));
  });
</script>
<script type="text/javascript">
        function openCloseSell(element,sell_id){
            element.set('class','');
            element.addClass('aaf_buysell_waiting_image');
            url = en4.core.baseUrl + 'advancedactivity/buy-sell/open-close/sell_id/'+sell_id;
            var request = new Request.JSON({
                'url' : url,
                'method':'post',
                'data' : {
                'format' : 'json',
                'isAjax' : true,
                },
                'onSuccess':function(responseJSON){
                    if(responseJSON.closed){
                        element.removeClass('aaf_feed_buysell_product_close');
                        element.addClass('aaf_feed_buysell_product_open');
                        element.set('html','<?php echo $this->translate("Mark As Open") ?>')
                    } else {
                        element.addClass('aaf_feed_buysell_product_close');
                        element.removeClass('aaf_feed_buysell_product_open');
                        element.set('html','<?php echo $this->translate("Mark As Close") ?>')
                    }
                    element.removeClass('aaf_buysell_waiting_image');
                }
            });
             request.send();
        }
</script>


