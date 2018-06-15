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


<?php 
$breadcrumb = array(
    array("href"=>$this->sitereview->getHref(),"title"=>$this->sitereview->getTitle(),"icon"=>"arrow-r"),
    array("href"=>$this->sitereview->getHref(array('tab' => $this->tab_selected_id)),"title"=>"Videos","icon"=>"arrow-d"));

echo $this->breadcrumb($breadcrumb);
?>
<?php
$this->headScript()
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sitemobile/externals/scripts/core.js');
?>

<script type="text/javascript">
	var tagsUrl = '<?php echo $this->url(array('controller' => 'tag', 'action' => 'suggest'), 'default', true) ?>';
	var validationUrl = '<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'video', 'action' => 'validation'), 'default', true) ?>';
	var validationErrorMessage = "<?php echo $this->translate("We could not find a video there - please check the URL and try again. If you are sure that the URL is valid, please click %s to continue.", "<a class='ui-link' href='javascript://' onclick='sm4.core.Module.sitereview.video.ignoreValidation();'>".$this->translate("here")."</a>"); ?>";
	var checkingUrlMessage = '<?php echo $this->string()->escapeJavascript($this->translate('Checking URL...')) ?>';
</script>

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

<script type="text/javascript">
	$(window).bind('domready', function() {
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