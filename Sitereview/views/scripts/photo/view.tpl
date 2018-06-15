<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: view.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');?>

<?php
  $this->headScript()
    ->appendFile($this->layout()->staticBaseUrl. 'externals/moolasso/Lasso.js')
    ->appendFile($this->layout()->staticBaseUrl. 'externals/moolasso/Lasso.Crop.js')
    ->appendFile($this->layout()->staticBaseUrl.'externals/autocompleter/Observer.js')
    ->appendFile($this->layout()->staticBaseUrl.'externals/autocompleter/Autocompleter.js')
    ->appendFile($this->layout()->staticBaseUrl.'externals/autocompleter/Autocompleter.Local.js')
    ->appendFile($this->layout()->staticBaseUrl.'externals/autocompleter/Autocompleter.Request.js')
    ->appendFile($this->layout()->staticBaseUrl. 'externals/tagger/tagger.js');

  $this->headTranslate(array('Save', 'Cancel', 'delete'));
?>

<script type="text/javascript">
  var taggerInstance;
  en4.core.runonce.add(function() {
    taggerInstance = new Tagger('media_image_next', {
      'title' : '<?php echo $this->translate('ADD TAG');?>',
      'description' : '<?php echo $this->translate('Type a tag or select a name from the list.');?>',
      'createRequestOptions' : {
        'url' : '<?php echo $this->url(array('module' => 'core', 'controller' => 'tag', 'action' => 'add'), 'default', true) ?>',
        'data' : {
          'subject' : '<?php echo $this->subject()->getGuid() ?>'
        }
      },
      'deleteRequestOptions' : {
        'url' : '<?php echo $this->url(array('module' => 'core', 'controller' => 'tag', 'action' => 'remove'), 'default', true) ?>',
        'data' : {
          'subject' : '<?php echo $this->subject()->getGuid() ?>'
        }
      },
      'cropOptions' : {
        'container' : $('media_image_next')
      },
      'tagSitereviewElement' : 'media_tags',
      'existingTags' : <?php echo $this->action('retrieve', 'tag', 'core', array('sendNow' => false)) ?>,
      'suggestParam' : <?php echo $this->action('suggest', 'friends', 'user', array('sendNow' => false, 'includeSelf' => true)) ?>,
      'guid' : <?php echo ( $this->viewer()->getIdentity() ? "'".$this->viewer()->getGuid()."'" : 'false' ) ?>,
      'enableCreate' : <?php echo ( $this->canEdit ? 'true' : 'false') ?>,
      'enableDelete' : <?php echo ( $this->canEdit ? 'true' : 'false') ?>
    });

    var nextHref = $('media_image_next').get('href');
    taggerInstance.addEvents({
      'onBegin' : function() {
        $('media_image_next').erase('href');
      },
      'onEnd' : function() {
        $('media_image_next').set('href', nextHref);
      }
    });
  });
</script>

<h2>
  <?php echo $this->translate('%1$s '."&#187; Photos", $this->sitereview->getParent()->__toString()) ?>
</h2>

<div class='sitereview_photo_view'>
  <div class="sitereview_photo_nav">
    <div>
      <?php
				echo $this->translate('Picture %1$s of %2$s',
              $this->locale()->toNumber($this->image->getCollectionIndex() + 1),
              $this->locale()->toNumber($this->sitereview->count()))
      ?>
    </div>
    <?php if ($this->sitereview->count() > 1): ?>
			<div>
				<?php echo $this->htmlLink($this->image->getPrevCollectible()->getHref(), $this->translate('Prev')) ?>
				<?php echo $this->htmlLink($this->image->getNextCollectible()->getHref(), $this->translate('Next')) ?>
      </div>
    <?php endif; ?>
	</div>
    
	<div class='albums_viewmedia_info sitereview_photo_info'>
		<div class='sitereview_photo_container' id='media_image_div'>
			<a id='media_image_next' href='<?php echo $this->escape($this->image->getNextCollectible()->getHref())?>'>
				<?php echo $this->htmlImage($this->image->getPhotoUrl(), $this->image->getTitle(), array('id' => 'media_image'));?>
			</a>
		</div>
	  <br />
    <?php if($this->enablePinit): ?>
	  <div class="seaocore_pinit_button">
	  	<a href="http://pinterest.com/pin/create/button/?url=<?php echo urlencode(((!empty($_ENV["HTTPS"]) && 'on' == strtolower($_ENV["HTTPS"])) ? "https://":"http://") . $_SERVER['HTTP_HOST'].$this->image->getHref()); ?>&media=<?php  echo urlencode(!preg_match("~^(?:f|ht)tps?://~i", $this->image->getPhotoUrl()) ?(((!empty($_ENV["HTTPS"]) && 'on' == strtolower($_ENV["HTTPS"])) ? "https://":"http://") . $_SERVER['HTTP_HOST'] ):''.$this->image->getPhotoUrl()); ?>&description=<?php echo $this->image->getTitle(); ?>" class="pin-it-button" count-layout="horizontal"  id="new_pin" ><?php echo $this->translate("Pin It");?></a>
			<script type="text/javascript" > 
			   en4.core.runonce.add(function() {              
			      new Asset.javascript( 'http://assets.pinterest.com/js/pinit.js',{});                 
			   });			 
			</script>
	  </div>
   <?php endif;?>
    <?php echo $this->socialShareButton();?> 
		<?php if ($this->image->getTitle()): ?>
			<div class="sitereview_photo_title">
				<?php echo $this->image->getTitle(); ?>
			</div>
		<?php endif; ?>

		<?php if ($this->image->getDescription()): ?>
			<div class="sitereview_photo_description">
				<?php echo $this->image->getDescription() ?>
			</div>
		<?php endif; ?>

	  <div class="event_photo_tags" id="media_tags" class="tag_sitereview" style="display: none;">
			<?php echo $this->translate('Tagged:'); ?>
	    </div>
	    <div class="sitereview_photo_date">
				<?php echo $this->translate('Added'); ?> <?php echo $this->timestamp($this->image->creation_date) ?>
	      <?php if( $this->canEdit ): ?>
					- <a href='javascript:void(0);' onclick='taggerInstance.begin();'><?php echo $this->translate('Add Tag');?></a>
					- <?php echo $this->htmlLink(array('route' => "sitereview_photo_extended_listtype_$this->listingtype_id", 'controller' => 'photo', 'action' => 'edit', 'photo_id' => $this->image->getIdentity(), 'format' => 'smoothbox'), $this->translate('Edit'), array('class' => 'smoothbox')) ?>
				<?php endif; ?>

				<?php if( $this->canEdit && $this->canDelete): ?>
					- <?php echo $this->htmlLink(array('route' => "sitereview_photo_extended_listtype_$this->listingtype_id", 'controller' => 'photo', 'action' => 'remove', 'photo_id' => $this->image->getIdentity(), 'format' => 'smoothbox'), $this->translate('Delete'), array('class' => 'smoothbox')) ?>
				<?php endif; ?>

	      <?php if(!empty($this->viewer_id )):?>
					<?php if (SEA_PHOTOLIGHTBOX_SHARE): ?>
						- <?php echo $this->htmlLink(Array('module' => 'activity', 'controller' => 'index', 'action' => 'share', 'route' => 'default', 'type' => $this->image->getType(), 'id' => $this->image->getIdentity(), 'format' => 'smoothbox'), $this->translate("Share"), array('class' => 'smoothbox')); ?>
					<?php endif; ?>

					<?php if (SEA_PHOTOLIGHTBOX_REPORT): ?>
						- <?php echo $this->htmlLink(Array('module' => 'core', 'controller' => 'report', 'action' => 'create', 'route' => 'default', 'subject' => $this->image->getGuid(), 'format' => 'smoothbox'), $this->translate("Report"), array('class' => 'smoothbox')); ?>
					<?php endif; ?>
				<?php endif; ?>

				<?php if(SEA_PHOTOLIGHTBOX_DOWNLOAD): ?>
					-
          <iframe src="about:blank" style="display:none" name="downloadframe"></iframe>
				  <a href="<?php echo $this->url(array('module' => 'seaocore', 'controller' => 'core', 'action' => 'download'), 'default', true); ?><?php echo '?path=' . urlencode($this->image->getPhotoUrl()).'&file_id='.$this->image->file_id ?>" target='downloadframe'><?php echo $this->translate('Download')?></a>
			  <?php endif; ?> 

        <?php if( !empty($this->viewer_id ) && SEA_PHOTOLIGHTBOX_MAKEPROFILEPHOTO): ?>
					- <?php echo $this->htmlLink(array('route' => 'user_extended', 'module' => 'user', 'controller' => 'edit', 'action' => 'external-photo', 'photo' => $this->image->getGuid(), 'format' => 'smoothbox'), $this->translate('Make Profile Photo'), array('class' => 'smoothbox')) ?>
	      <?php endif; ?>

      </div>
			<?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitetagcheckin')) :?>
				<div class="seaotagcheckinshowlocation">
					<?php
						// RENDER LOCATION WIDGET
						echo $this->content()->renderWidget("sitetagcheckin.location-sitetagcheckin"); 
					?>
				</div>
			<?php endif;?>
    </div>
    <?php 
        include_once APPLICATION_PATH . '/application/modules/Seaocore/views/scripts/_listComment.tpl';
    ?>
</div>