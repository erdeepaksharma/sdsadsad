<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: upload.tpl 6590 2016-07-07 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>

<?php
$this->headScript()
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
<style type="text/css">
  #demo-list{
    max-height: none;
    width: auto;
    text-align: center;
  }
  #demo-list li {
    width: 160px;
    height: 170px;
    margin: 10px;
    float:left;
    border: 1px solid #ccc;
  }
  #demo-list li img{
      margin-top:48px;
  }
  #demo-list li.file, #demo-list li.file-success {
    background-image: none;
    padding: 10px;
  }
  #demo-list li.file-success img{
    width: 150px;
    height: 150px;
    margin-top:0;
  }
</style>
<script type="text/javascript">
  var uploadCount = 0;
  var extraData = <?php echo $this->jsonInline($this->data); ?>;
  window.addEvent('domready', function() { // wait for the content
    // our uploader instance 
    var up = new FancyUpload2($('demo-status'), $('demo-list'), {// options object
      // we console.log infos, remove that in production!!
      verbose: (en4 in window && en4.core.environment == 'development' ? true : false),
      //verbose: true,
      appendCookieData: true,
      timeLimit: 0,
      // set cross-domain policy file
      policyFile: '<?php
echo (_ENGINE_SSL ? 'https://' : 'http://')
 . $_SERVER['HTTP_HOST'] . $this->url(array(
  'controller' => 'cross-domain'), 'default', true)
?>',
      // url is read from the form, so you just have to change one place
      url: $('form-upload').action + '?ul=1',
      // path to the SWF file
      path: '<?php echo $this->layout()->staticBaseUrl . 'externals/fancyupload/Swiff.Uploader.swf'; ?>',
      // remove that line to select all files, or edit it, add more items
      typeFilter: {
        'Images (*.jpg, *.jpeg, *.gif, *.png)': '*.jpg; *.jpeg; *.gif; *.png'
      },
      // this is our browse button, *target* is overlayed with the Flash movie
      target: 'demo-browse',
      data: extraData,
      // graceful degradation, onLoad is only called if all went well with Flash
      onLoad: function() {
        $('demo-status').removeClass('hide'); // we show the actual UI  
        $('demo-fallback').destroy(); // ... and hide the plain form
        // We relay the interactions with the overlayed flash to the link
        this.target.addEvents({
          click: function() {
            return false;
          },
          mouseenter: function() {
            this.addClass('hover');
          },
          mouseleave: function() {
            this.removeClass('hover');
            this.blur();
          },
          mousedown: function() {
            this.focus();
          }
        });
        // Interactions for the 2 other buttons
        $('demo-clear').addEvent('click', function() {
          up.remove(); // remove all files
          var fileids = document.getElementById('fancyuploadfileids');
          fileids.value = "";
          return false;
        });
      },
      // Edit the following lines, it is your custom event handling
      /**
       * Is called when files were not added, "files" is an array of invalid File classes.
       * 
       * This example creates a list of error elements directly in the file list, which
       * hide on click.
       */
      onSelectFail: function(files) {
        files.each(function(file) {
          new Element('li', {
            'class': 'validation-error',
            html: file.validationErrorMessage || file.validationError,
            title: MooTools.lang.get('FancyUpload', 'removeTitle'),
            events: {
              click: function() {
                this.destroy();
              }
            }
          }).inject(this.list, 'top');
        }, this);
      },
      onComplete: function hideProgress() {
        var demostatuscurrent = document.getElementById("demo-status-current");
        var demostatusoverall = document.getElementById("demo-status-overall");
        var demosubmit = document.getElementById("submit-wrapper");
        demostatuscurrent.style.display = "none";
        demostatusoverall.style.display = "none";
        demosubmit.style.display = "block";
      },
      onFileStart: function(file) {
        uploadCount += 1;
      },
      onFileRemove: function(file) {
        uploadCount -= 1;
        feeling_id = file.feeling_id;
        request = new Request.JSON({
          'format': 'json',
          'url': '<?php echo $this->url(array('controller' => 'feeling', 'action' => 'delete')) ?>',
          'data': {
            'feeling_id': feeling_id
          },
          'onSuccess': function(responseJSON) {
            return false;
          }
        });
        request.send();
        var fileids = document.getElementById('fancyuploadfileids');
        if (uploadCount == 0)
        {
          var democlear = document.getElementById("demo-clear");
          var demolist = document.getElementById("demo-list");
          var demosubmit = document.getElementById("submit-wrapper");
          democlear.style.display = "none";
          demolist.style.display = "none";
          demosubmit.style.display = "none";
        }
        fileids.value = fileids.value.replace(feeling_id, "");
      },
      onSelectSuccess: function(files) {
        $('demo-list').style.display = 'inline-block';
        var democlear = document.getElementById("demo-clear");
        var demostatuscurrent = document.getElementById("demo-status-current");
        var demostatusoverall = document.getElementById("demo-status-overall");
        democlear.style.display = "inline";
        demostatuscurrent.style.display = "block";
        demostatusoverall.style.display = "block";
        files.each(function(file) {
          file.element.getElement('.file-remove').set('html', 'X');
          file.img = document.createElement('img');
          file.img.src = 'application/modules/Seaocore/externals/images/loading.gif';
          file.img.inject(file.info, 'before');
        });
        up.start();
      },
      /**
       * This one was directly in FancyUpload2 before, the event makes it
       * easier for you, to add your own response handling (you probably want
       * to send something else than JSON or different items).
       */
      onFileSuccess: function(file, response) {
        var json = new Hash(JSON.decode(response, true) || {});
        if (json.get('status') == '1') {
          file.element.addClass('file-success');
          file.element.getElement('.file-remove').set('html', 'X');
          file.element.getElement('.file-name').set('html', json.name);
          // console.log(file);
          //var newimg = document.createElement('img');
          file.img.src = json.imgSrc;
          //newimg.inject(file.info, 'before');
          file.info.set('html', '<span><?php echo $this->string()->escapeJavascript($this->translate('Upload complete.')) ?></span>');
          var fileids = document.getElementById('fancyuploadfileids');
          fileids.value = fileids.value + json.get('feeling_id') + " ";
          file.feeling_id = json.get('feeling_id');
        } else {
          file.element.addClass('file-failed');
          file.info.set('html', '<span><?php echo $this->string()->escapeJavascript($this->translate('An error occurred:')) ?></span> ' + (json.get('error') ? (json.get('error')) : response));
        }
      },
      /**
       * onFail is called when the Flash movie got bashed by some browser plugin
       * like Adblock or Flashblock.
       */
      onFail: function(error) {
        switch (error) {
          case 'hidden': // works after enabling the movie and clicking refresh
            alert(<?php echo Zend_Json::encode($this->translate('To enable the embedded uploader, unblock it in your browser and refresh (see Adblock).')) ?>);
            break;
          case 'blocked': // This no *full* fail, it works after the user clicks the button
            alert(<?php echo Zend_Json::encode($this->translate('To enable the embedded uploader, enable the blocked Flash movie (see Flashblock).')) ?>);
            break;
          case 'empty': // Oh oh, wrong path
            alert(<?php echo Zend_Json::encode($this->translate('A required file was not found, please be patient and we will fix this.')) ?>);
            break;
          case 'flash': // no flash 9+ :(
            //alert(<?php echo Zend_Json::encode($this->translate('To enable the embedded uploader, install the latest Adobe Flash plugin.')) ?>)
        }
      }
    });
  });
</script>
<input type="hidden" name="<?php echo $this->name; ?>" id="fancyuploadfileids" value ="" />
<fieldset id="demo-fallback">
  <legend><?php echo $this->translate('File Upload') ?></legend>
  <p>
    <?php echo $this->translate('Click "Browse..." to select the file you would like to upload.') ?>
  </p>
  <label for="demo-photoupload">
    <?php echo $this->translate('Upload a Photo:') ?>
    <input id="fallback" type="file" name="Filedata" />
  </label>
</fieldset>
<div id="demo-status" class="hide">
  <div>
    <?php echo $this->translate('Click "Add Feelings" to select one or more feelings from your computer. After you have selected the feelings, they will begin to upload right away. When your upload is finished, click the button below your feeling list to save them to your feelingtype.') ?>
  </div>
  <div>
    <a class="buttonlink seaocore_icon_upload" href="javascript:void(0);" id="demo-browse"><?php echo $this->translate('Add Feelings') ?></a>
    <a class="buttonlink icon_clearlist" href="javascript:void(0);" id="demo-clear" style='display: none;'><?php echo $this->translate('Clear List') ?></a>
  </div>
  <div class="demo-status-overall" id="demo-status-overall" style="display:none">
    <div class="overall-title"></div>
    <img src="<?php echo $this->layout()->staticBaseUrl . 'externals/fancyupload/assets/progress-bar/bar.gif'; ?>" class="progress overall-progress" />
  </div>
  <div class="demo-status-current" id="demo-status-current" style="display:none">
    <div class="current-title"></div>
    <img src="<?php echo $this->layout()->staticBaseUrl . 'externals/fancyupload/assets/progress-bar/bar.gif'; ?>" class="progress current-progress" />
  </div>
  <div class="current-text"></div>
</div>
<ul id="demo-list"></ul>
