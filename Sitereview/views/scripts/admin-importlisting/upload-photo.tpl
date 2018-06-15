<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: upload-photo.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
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
 
 <h2>
  <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) { echo $this->translate('Reviews & Ratings - Multiple Listing Types Plugin'); } else { echo $this->translate('Reviews & Ratings Plugin'); }?>
</h2>

<?php if (count($this->navigation)): ?>
  <div class='seaocore_admin_tabs'> <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?> </div>
<?php endif; ?>

<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'importlisting','action' => 'manage'), $this->translate('Back to Manage Import Files'), array('class' => 'buttonlink seaocore_icon_back')) ?>
<br /><br />
 
<script type="text/javascript">

  var importfile_id = '<?php echo $this->importfile_id; ?>';
  var up;
  var swfPath = '<?php echo $this->layout()->staticBaseUrl . 'externals/fancyupload/Swiff.Uploader.swf' ?>';
  var extraData = {
    format : 'json',
    path : '<?php echo $this->relPath ?>'
  };
  var successCount = 0;
  var failureCount = 0;
  window.addEvent('domready', function() {
    up = new FancyUpload2($('demo-status'), $('demo-list'), {
      verbose: true,
      appendCookieData: true,
      // set cross-domain policy file
      policyFile : '<?php echo (_ENGINE_SSL ? 'https://' : 'http://') 
          . $_SERVER['HTTP_HOST'] . $this->url(array(
            'controller' => 'cross-domain'), 
            'default', true) ?>',
      url: $('form-upload').action + '?ul=1' + '?importfile_id/' + importfile_id,
      path: swfPath,
      typeFilter: {

      },
      target: 'demo-browse',
      data: extraData,
      onLoad : function() {
        $('demo-status').setStyle('display', '');
        $('demo-fallback').destroy();
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
        $('demo-clear').addEvent('click', function() {
          up.remove(); // remove all files
          return false;
        });
      },
      onSelectFail : function(files) {
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
      onFileStart : function() {
        // @todo
      },
      onFileRemove : function(file) {
        // @todo
      },
      onSelectSuccess : function() {
        $('uploader-container').setStyle('display', '');
        $('demo-list').setStyle('display', '');
        $('demo-status-current').setStyle('display', '');
        $('demo-status-overall').setStyle('display', '');
        up.start();
      },
      onFileSuccess : function(file, response) {
        var json = new Hash(JSON.decode(response, true) || {});
        if (json.get('status') == '1') {
          successCount++;
          file.element.addClass('file-success');
          file.info.set('html', '<span><?php echo $this->translate("Upload complete.") ?></span>');
        } else {
          failureCount++;
          file.element.addClass('file-failed');
          file.info.set('html', '<span>' + (json.get('error') ? (json.get('error')) : '<?php $this->string()->escapeJavascript($this->translate('An unknown error has occurred.')) ?>')) + '</span>';
        }
      },
      onFail : function(error) {
        switch( error ) {
          case 'hidden': // works after enabling the movie and clicking refresh
            alert("<?php echo $this->string()->escapeJavascript($this->translate("To enable the embedded uploader, unblock it in your browser and refresh (see Adblock).")) ?>");
            break;
          case 'blocked': // This no *full* fail, it works after the user clicks the button
            alert("<?php echo $this->string()->escapeJavascript($this->translate("To enable the embedded uploader, enable the blocked Flash movie (see Flashblock).")) ?>");
            break;
          case 'empty': // Oh oh, wrong path
            alert("<?php echo $this->string()->escapeJavascript($this->translate("A required file was not found, please be patient and we'll fix this.")) ?>");
            break;
          case 'flash': // no flash 9+
            alert("<?php echo $this->string()->escapeJavascript($this->translate("To enable the embedded uploader, install the latest Adobe Flash plugin.")) ?>");
        }
      }
    });
  });

  var showFallbackUploader = function()
  {
    $('uploader-container').setStyle('display', 'block');
  }

	var importfile_id;
	function startImporting(file_id) {
	  
		Smoothbox.open($('startImporting').innerHTML);
		importfile_id = file_id;
		en4.core.request.send(new Request({
			url : en4.core.baseUrl+'admin/sitereview/importlisting/data-import?importfile_id='+importfile_id,
			method: 'get',
			data : {
				//'format' : 'json',
			},

			onSuccess : function(responseJSON) {
				window.location.href= en4.core.baseUrl+'admin/sitereview/importlisting/manage';
				parent.Smoothbox.close();
					
				}
		}))
	}
</script>

<div id="startImporting" style="display:none;">
	<center class="bold">
		<?php echo $this->translate("Import listings..."); ?>
	</center>
	<center class="mtop10">
		<img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/loader.gif" alt="Importing sitereviews" />
	</center>
	<br />
	<center><button name="submit" id="submit" type="submit" onclick='stopImoprt();'><?php echo $this->translate("Stop");?></button></center>
</div>

<div class="importlisting_form">
	<div>
		<h3><?php echo $this->translate('Upload photos');?></h3>
		<p>
		 <?php echo $this->translate("Below, you can upload a zip folder containing all the profile photos of listings which you want to import by using the \"Upload Photos\" link below. After successfully uploading the folder, you can start importing the csv file by using the \”Start Import\” button. Before starting to use this tool, please read the following points carefully.
<br /> <b>Note</b>: Start Import button will be shown only when the status of file to be imported is pending.");?>
		</p>
		<ul class="importlisting_form_sitereview">
			<li>
				<?php echo $this->translate("First, make the zip folder of photos which you want to upload.");?>
			</li>
			<li>
				<?php echo $this->translate("Please make sure that photos which you have included in the zip folder are of the extensions: .gif, .jpg, .png, .jpeg.");?>
			</li>
			<li>
				<?php echo $this->translate("Please make sure that the name of photos which you have included in the zip folder and csv file are same.");?>
			</li><br />
<!-- 			<li> -->
				<?php //echo $this->translate("If you enter image URL instead of image name in the csv file, then this importing process will take more time.");?>
<!-- 			</li><br /> -->
			<?php echo $this->htmlLink('javascript:void(0);', $this->translate('Upload Photos'), array('id' => 'demo-browse', 'class' => 'buttonlink admin_files_upload', 'onclick' => 'showFallbackUploader();')) ?>
		</ul>
	</div>
</div>

<?php if($this->importFile->status != 'Running' && $this->importFile->status != 'Completed' && empty($this->runningSomeImport)): ?>
		<button onclick='startImporting("<?php echo $this->importFile->importfile_id; ?>");'><?php echo $this->translate('Start Import') ?></button>&nbsp;&nbsp;&nbsp;<b><?php echo $this->translate('After uploading the photos you can start the import.') ?></b>
<?php endif;?>

<div id="uploader-container" class="uploader admin_files_uploader" style="display: none;">
  <div id="demo-fallback">
    <form action="<?php echo $this->url(array('action' => 'upload')) ?>" method="post" id="form-upload" enctype="multipart/form-data">
      <input type="file" name="Filedata" />
      <br />
      <br />
      <button type="submit"><?php echo $this->translate('Upload') ?></button>
      <input type="hidden" name="ul" value="1" />
      <input type="hidden" name="ut" value="standard" />
    </form>
  </div>
  <div id="demo-status" style="display: none;">
    <div style="display: none;">
      <a href="javascript:void(0);" id="demo-clear" style='display: none;'><?php $this->translate('Clear List') ?></a>
    </div>
    <div class="demo-status-overall" id="demo-status-overall" style="display:none">
      <div class="overall-title"></div>
      <img alt="" src="<?php echo $this->layout()->staticBaseUrl . 'externals/fancyupload/assets/progress-bar/bar.gif' ?>" class="progress overall-progress" />
    </div>
    <div class="demo-status-current" id="demo-status-current" style="display: none">
      <div class="current-title"></div>
      <img alt="" src="<?php echo $this->layout()->staticBaseUrl . 'externals/fancyupload/assets/progress-bar/bar.gif' ?>" class="progress current-progress" />
    </div>
    <div class="current-text"></div>
  </div>
  <ul id="demo-list">
  </ul>
</div>