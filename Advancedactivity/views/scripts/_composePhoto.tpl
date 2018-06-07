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

<?php
  $this->headScript()
    ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/composer_photo.js')
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

<script type="text/javascript">
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
    composeInstance.addPlugin(new Composer.Plugin.Photo({
      title : '<?php echo $this->string()->escapeJavascript($this->translate('Add Photo')) ?>',
      lang : {
        'Add Photo' : '<?php echo $this->string()->escapeJavascript($this->translate('Add Photo')) ?>',
        'Select File' : '<?php echo $this->string()->escapeJavascript($this->translate('Select File')) ?>',
        'cancel' : '<?php echo $this->string()->escapeJavascript($this->translate('cancel')) ?>',
        'Loading...' : '<?php echo $this->string()->escapeJavascript($this->translate('Loading...')) ?>',
        'Unable to upload photo. Please click cancel and try again': '<?php echo $this->string()->escapeJavascript($this->translate('Unable to upload photo. Please click cancel and try again')) ?>'
      },
      requestOptions : {
        'url'  : requestOptionsURL
      },     
      fancyUploadOptions : {
        'url'  : fancyUploadOptionsURL,
        'path' : en4.core.basePath + 'externals/fancyupload/Swiff.Uploader.swf'
      }
    }));
  });
</script>