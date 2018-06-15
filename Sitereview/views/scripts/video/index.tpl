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
$this->headLink()
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');
$this->headLink()
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview_dashboard.css');
?>

<script type='text/javascript'>
  function toggles(switchElement) { 
    if (switchElement.value == '1'){
      document.getElementById('upload').style.display = 'none';
      document.getElementById('search').style.display = 'block';
    }
    else {
      document.getElementById('upload').style.display = 'block';
      document.getElementById('search').style.display = 'none';    
		}       
  }
</script>

<?php if ($this->canEdit): ?>
  <?php include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/_DashboardNavigation.tpl'; ?>
  <div class="sr_dashboard_content">
  <?php else: ?>
		<div class="sr_view_top">
		<?php echo $this->htmlLink($this->sitereview->getHref(array('profile_link' => 1)), $this->itemPhoto($this->sitereview, 'thumb.icon', '', array('align' => 'left'))) ?>
		<h2>	
			<?php echo $this->sitereview->__toString() ?>	
			<?php echo $this->translate('&raquo; '); ?>
			<?php echo $this->htmlLink($this->sitereview->getHref(array('tab'=>$this->content_id)), $this->translate('Videos')) ?>
		</h2>
	</div>
<?php endif; ?>

	<div id="box">
		<h3><?php echo $this->translate("%s Videos", $this->listing_singular_uc); ?></h3>
		<div class="sr_video_add_options" >
			<p><?php echo $this->translate('You may add a video to your listing either by choosing from your existing videos, or by posting a new video:') ?></p>
			<div>
				<?php if(  $this->display==0):?>
				<input type="radio"  name="video" value="1"  onClick="toggles(this);"  checked/><?php echo $this->translate('Choose from your existing videos') ?>
				<?php else: ?>
				<input type="radio"  name="video" value="1"  onClick="toggles(this);"  /> <?php echo $this->translate('Search your video') ?>
				<?php endif; ?>
			</div>	
			<div>
				<?php if(  $this->display==0):?>
					<input type="radio"  name="video" value="0"  onClick="toggles(this);" /> <?php echo $this->translate('Post New Video') ?>   
				<?php else: ?>
					<input type="radio"  name="video" value="0"  onClick="toggles(this);" checked /> <?php echo $this->translate('Upload New video') ?>
				<?php endif; ?>
			</div>
		</div>
		<div class="sr_video_add_source">
			<div id='search'>
				<form id='video_selected' method='post' action='<?php echo $this->url(array('action' => 'load', 'content_id' => $this->content_id, 'listing_id' => $this->listing_id), "sitereview_video_upload_listtype_$this->listingtype_id") ?>' class="global_form">
					<div>
						<div>
							<?php if (!empty($this->message)): ?>
								<div class="tip"> 
									<span>
										<?php echo $this->message; ?>
									</span>
								</div>
							<?php  endif;?>
						<h3> <?php echo $this->translate('Choose your Video') ?></h3>
						<div class="form-elements">
							<div class="form-wrapper">
								<div class="form-label">
									<label class="optional"><?php echo $this->translate('Enter Title') ?></label>
								</div>
								<div class="form-element">
									<input type="text" id="searchtext" name="searchtext" value=""/>
								<p class="description"><?php echo $this->translate('Search and select from your videos using this auto-suggest field.') ?></p>
								</div>
							</div>
							<div class="form-wrapper" style="display: block;">
								<div class="form-label">&nbsp;</div>
								<div class="form-element">
									<input type="hidden" id="video_id" name="video_id" />
									<button type="submit" name="submit" class="mtop10"><?php echo $this->translate('Continue &raquo;') ?></button>
								</div>
							</div>
						</div>
					</div>
				</div>    
			</form>
		</div>
	</div>
</div>   
 
	<div id='upload' style="display: none;" >
			<?php if (($this->current_count >= $this->quota) && !empty($this->quota)): ?>
			<div class="tip">
				<span>
          <?php $url = $this->url(array('action' => 'manage'), 'video_general');?>
					<?php echo $this->translate('You have already uploaded the maximum number of videos allowed.'); ?>
					<?php echo $this->translate('If you would like to upload a new video, please %1$s an old one first.', "<a href='$url'>delete</a>"); ?>
				</span>
			</div>
		<?php else: ?>
			<?php echo $this->form->render($this); ?>
		<?php endif; ?>
	</div>
<?php if($this->canEdit): ?>
	</div>
</div>
<?php endif; ?>

<script type="text/javascript">
	window.addEvent('domready', function() {
		<?php if(  $this->display==1):?>
      document.getElementById('upload').style.display = 'block';
      document.getElementById('search').style.display = 'none'; 
    <?php endif;?>
        
		<?php if(empty(  $this->videoCount)):?>
      document.getElementById('upload').style.display = 'block';
      document.getElementById('box').style.display = 'none';
      document.getElementById('search').style.display = 'none';
    <?php endif;?>
	});
</script>

<?php
      $this->headScript()
          ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Observer.js')
          ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.js')
          ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Local.js')
          ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Request.js');
?>

<script type="text/javascript">
	en4.core.runonce.add(function()
	{
		var contentAutocomplete = new Autocompleter.Request.JSON('searchtext', '<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'video', 'action' => 'suggest','listingtype_id' => $this->listingtype_id), 'default', true) ?>', {
			'postVar' : 'text',
			'minLength': 0,
			'selectMode': 'pick',
			'autocompleteType': 'tag',
			'className': 'tag-autosuggest',
			'customChoices' : true,
			'filterSubset' : true,
			'multiple' : false,
			'injectChoice': function(token){
					var choice = new Element('li', {'class': 'autocompleter-choices1', 'html': token.photo, 'id':token.label});
					new Element('div', {'html': this.markQueryValue(token.label),'class': 'autocompleter-choice1'}).inject(choice);
					this.addChoiceEvents(choice).inject(this.choices);
					choice.store('autocompleteChoice', token);
	
			}
		});

		contentAutocomplete.addEvent('onSelection', function(element, selected, value, input) {
			$('video_id').value = selected.retrieve('autocompleteChoice').id;

		});
	});

	var current_code;

	var ignoreValidation = function(){
		$('upload-wrapper').style.display = "block";
		$('validation').style.display = "none";
		$('code').value = current_code;
		$('ignore').value = true;
	}

	var updateTextFields = function()
	{
		var video_element = document.getElementById("type");
		var url_element = document.getElementById("url-wrapper");
		var file_element = document.getElementById("file-wrapper");
		var submit_element = document.getElementById("upload-wrapper");

		$('upload-wrapper').style.display = "none";

		// If video source is empty
		if (video_element.value == 0)
		{
			$('url').value = "";
			file_element.style.display = "none";
			url_element.style.display = "none";
			return;
		}

		if ($('code').value && $('url').value)
		{
			$('type-wrapper').style.display = "none";
			file_element.style.display = "none";
			$('upload-wrapper').style.display = "block";
			return;
		}

		// If video source is youtube or vimeo
		if (video_element.value == 1 || video_element.value == 2)
		{
			$('url').value = "";
			$('code').value = "";
			file_element.style.display = "none";
			url_element.style.display = "block";
			return;
		}

		// If video source is from computer
		if (video_element.value == 3)
		{
			$('url').value = "";
			$('code').value = "";
			file_element.style.display = "block";
			url_element.style.display = "none";
			return;
		}

		// if there is video_id that means this form is returned from uploading because some other required field
		if ($('id').value)
		{
			$('type-wrapper').style.display = "none";
			file_element.style.display = "none";
			$('upload-wrapper').style.display = "block";
			return;
		}
	}

	var video = {
    active : false,

    debug : false,

    currentUrl : null,

    currentTitle : null,

    currentDescription : null,

    currentImage : 0,

    currentImageSrc : null,

    imagesLoading : 0,

    images : [],

    maxAspect : (10 / 3), //(5 / 2), //3.1,

    minAspect : (3 / 10), //(2 / 5), //(1 / 3.1),

    minSize : 50,

    maxPixels : 500000,

    monitorInterval: null,

    monitorLastActivity : false,

    monitorDelay : 500,

    maxImageLoading : 5000,
    
    attach : function()
    {
      var bind = this;
      $('url').addEvent('keyup', function()
      {
        bind.monitorLastActivity = (new Date).valueOf();
      });

      var url_element = document.getElementById("url-element");
      var myElement = new Element("p");
      myElement.innerHTML = "test";
      myElement.addClass("description");
      myElement.id = "validation";
      myElement.style.display = "none";
      url_element.appendChild(myElement);

      var body = $('url');
      var lastBody = '';
      var lastMatch = '';
      var video_element = $('type');
      (function()
      {
        // Ignore if no change or url matches
        if( body.value == lastBody || bind.currentUrl )
        {
          return;
        }

        // Ignore if delay not met yet
        if( (new Date).valueOf() < bind.monitorLastActivity + bind.monitorDelay )
        {
          return;
        }

        // Check for link
        var m = body.value.match(/https?:\/\/([-\w\.]+)+(:\d+)?(\/([-#:\w/_\.]*(\?\S+)?)?)?/);
        if( $type(m) && $type(m[0]) && lastMatch != m[0] )
        {
          if (video_element.value == 1){
            video.youtube(body.value);
          }
          else video.vimeo(body.value);
        }
        else{
         
        }

        lastBody = body.value;
      }).periodical(250);
    },

    youtube : function(url){
      // extract v from url
      var myURI = new URI(url);
      var youtube_code = myURI.get('data')['v'];
      if( youtube_code === undefined ) {
        youtube_code = myURI.get('file');
      }
      if (youtube_code){
        (new Request.HTML({
          'format': 'html',
          'url' : '<?php echo $this->url(array('module' => 'video', 'controller' => 'index', 'action' => 'validation'), 'default', true) ?>',
          'data' : {
            'ajax' : true,
            'code' : youtube_code,
            'type' : 'youtube'
          },
          'onRequest' : function(){
            $('validation').style.display = "block";
            $('validation').innerHTML = '<?php echo $this->string()->escapeJavascript($this->translate('Checking URL...'));?>';
            $('upload-wrapper').style.display = "none";
          },
          'onSuccess' : function(responseTree, responseElements, responseHTML, responseJavaScript)
          {
            if (valid){
              $('upload-wrapper').style.display = "block";
              $('validation').style.display = "none";
              $('code').value = youtube_code;
            }
            else{
              $('upload-wrapper').style.display = "none";
              current_code = youtube_code;
              <?php $link = "<a href='javascript:void(0);' onclick='javascript:ignoreValidation();'>".$this->translate("here").'</a>'; ?>
                    $('validation').innerHTML = '<?php echo addslashes($this->translate("We could not find a video there - please check the URL and try again. If you are sure that the URL is valid, please click %s to continue.", $link)); ?>';
            }
          }
        })).send();
      }
    },

    vimeo :function(url){
      var myURI = new URI(url);
      var vimeo_code = myURI.get('file');
      if (vimeo_code.length > 0){
        (new Request.HTML({
          'format': 'html',
          'url' : '<?php echo $this->url(array('module' => 'video', 'controller' => 'index', 'action' => 'validation'), 'default', true) ?>',
          'data' : {
            'ajax' : true,
            'code' : vimeo_code,
            'type' : 'vimeo'
          },
          'onRequest' : function(){
            $('validation').style.display = "block";
            $('validation').innerHTML = '<?php echo $this->string()->escapeJavascript($this->translate('Checking URL...'));?>';
            $('upload-wrapper').style.display = "none";
          },
          'onSuccess' : function(responseTree, responseElements, responseHTML, responseJavaScript)
          {
            if (valid){
              $('upload-wrapper').style.display = "block";
              $('validation').style.display = "none";
              $('code').value = vimeo_code;
            }
            else{
              $('upload-wrapper').style.display = "none";
              current_code = vimeo_code;
              <?php $link = "<a href='javascript:void(0);' onclick='javascript:ignoreValidation();'>".$this->translate("here")."</a>"; ?>
              $('validation').innerHTML = "<?php echo $this->translate("We could not find a video there - please check the URL and try again. If you are sure that the URL is valid, please click %s to continue.", $link); ?>";
            }
          }
        })).send();
      }
    }
	}

	en4.core.runonce.add(updateTextFields);
	en4.core.runonce.add(video.attach);
	en4.core.runonce.add(function()
	{
		new Autocompleter.Request.JSON('tags', '<?php echo $this->url(array('controller' => 'tag', 'action' => 'suggest'), 'default', true) ?>', {
			'postVar' : 'text',
			'minLength': 1,
			'selectMode': 'pick',
			'autocompleteType': 'tag',
			'className': 'tag-autosuggest',
			'customChoices' : true,
			'filterSubset' : true,
			'multiple' : true,
			'injectChoice': function(token){
				var choice = new Element('li', {'class': 'autocompleter-choices', 'value':token.label, 'id':token.id});
				new Element('div', {'html': this.markQueryValue(token.label),'class': 'autocompleter-choice'}).inject(choice);
				choice.inputValue = token;
				this.addChoiceEvents(choice).inject(this.choices);
				choice.store('autocompleteChoice', token);
			}
		});
	});

	//en4.core.runonce.add($('url').value = '<?php //echo $this->url?>');

</script>