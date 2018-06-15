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
	$apiKey = Engine_Api::_()->seaocore()->getGoogleMapApiKey();
	$this->headScript()
					->appendFile("https://maps.googleapis.com/maps/api/js?libraries=places&key=$apiKey");
?>

<?php if($this->loaded_by_ajax):?>
  <script type="text/javascript">
    var params = {
      requestParams :<?php echo json_encode($this->params) ?>,
      responseContainer :$$('.layout_sitereview_photos_sitereview')
    }
    en4.sitereview.ajaxTab.attachEvent('<?php echo $this->identity ?>',params);
  </script>
<?php endif;?>

<?php if($this->showContent): ?>
  <a id="sitereview_photo_anchor" class="pabsolute"></a>
  <script type="text/javascript">
    var sitereviewPhotoPage = <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber()) ?>;
    var paginateSitereviewPhoto = function(page) {
      var params = {
          requestParams :<?php echo json_encode($this->params) ?>,
          responseContainer :$$('.layout_sitereview_photos_sitereview')
        }
        params.requestParams.content_id = <?php echo sprintf('%d', $this->identity) ?>;
        params.requestParams.page = page;
        en4.sitereview.ajaxTab.sendReq(params);

    }
  </script>
  
  <?php if( $this->can_edit ): ?>
    <script type="text/javascript">
      var SortablesInstance;

      en4.core.runonce.add(function() {
        $$('.thumbs_nocaptions > li').addClass('sortable');
        SortablesInstance = new Sortables($$('.thumbs_nocaptions'), {
          clone: true,
          constrain: true,
          //handle: 'span',
          onComplete: function(e) {
            var ids = [];
            $$('.thumbs_nocaptions > li').each(function(el) {
              ids.push(el.get('id').match(/\d+/)[0]);
            });
            //console.log(ids);

            // Send request
            var url = en4.core.baseUrl+'sitereview/album/order';
            var request = new Request.JSON({
              'url' : url,
              'data' : {
                format : 'json',
                order : ids,
                'subject' : en4.core.subject.guid
              }
            });
            request.send();
          }
        });
      });
    </script>
  <?php endif ?>
    <?php $listingtype_id = $this->sitereview->listingtype_id;?>  
  <?php if ($this->total_images): ?>
        <?php if ($this->allowed_upload_photo): ?>
      <div class="seaocore_add">
          
        <a href='<?php echo $this->url(array('listing_id' => $this->sitereview->listing_id,'content_id' => $this->identity, 'listingtype_id'=> $listingtype_id), "sitereview_photoalbumupload_listtype_$listingtype_id", true) ?>'  class='buttonlink icon_sitereviews_photo_new'><?php echo $this->translate('Add Photos'); ?></a>
        <?php if ($this->can_edit): ?>
        <a href='<?php echo $this->url(array('listing_id' => $this->sitereview->listing_id, 'listingtype_id'=> $listingtype_id), "sitereview_albumspecific_listtype_$listingtype_id", true) ?>'  class='buttonlink seaocore_icon_edit'><?php echo $this->translate('Edit Photos'); ?></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
    <ul class="sr_thumbs thumbs_nocaptions">
      <?php foreach ($this->paginator as $image): ?>
        <li id="thumbs-photo-<?php echo $image->photo_id ?>">
          <a href="<?php echo $this->url(array('listing_id' => $image->listing_id, 'photo_id' => $image->photo_id), "sitereview_image_specific_listtype_$this->listingtype_id") ?>" <?php if(SEA_LIST_LIGHTBOX) :?> onclick='openSeaocoreLightBox("<?php echo $this->url(array('listing_id' => $image->album_id, 'photo_id' => $image->photo_id), "sitereview_image_specific_listtype_$this->listingtype_id") ?>");return false;' <?php endif;?> class="thumbs_photo" title="<?php echo $image->title ?>">
            <span style="background-image: url(<?php echo $image->getPhotoUrl('thumb.large'); ?>);"></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <?php if ($this->allowed_upload_photo): ?>
      <div class="seaocore_add">
        <a href='<?php echo $this->url(array('listing_id' => $this->sitereview->listing_id), "sitereview_photoalbumupload_listtype_$this->listingtype_id", true) ?>'  class='buttonlink icon_sitereviews_photo_new'><?php echo $this->translate('Add Photos'); ?></a>
      </div>
      <div class="tip">
        <span>
          <?php $url = $this->url(array('listing_id' => $this->sitereview->listing_id), "sitereview_photoalbumupload_listtype_$this->listingtype_id", true);?>
					<?php echo $this->translate('You have not added any photo in your '.$this->listing_singular_lc.'. %1$sClick here%2$s to add your first photo.', "<a href='$url'>", "</a>"); ?>
        </span>
      </div>
    <?php endif; ?>
  <?php endif; ?>
  <?php if ($this->paginator->count() > 1): ?>
     <div >
      <?php if ($this->paginator->getCurrentPageNumber() > 1): ?>
         <div id="user_group_members_previous" class="paginator_previous">
          <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array('onclick' => 'paginateSitereviewPhoto(sitereviewPhotoPage - 1)', 'class' => 'buttonlink icon_previous')); ?>
          </div>
      <?php endif; ?>
      <?php if ($this->paginator->getCurrentPageNumber() < $this->paginator->count()): ?>
        <div id="user_group_members_next" class="paginator_next">
          <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array('onclick' => 'paginateSitereviewPhoto(sitereviewPhotoPage + 1)','class' => 'buttonlink_right icon_next'));?>
          </div>
        <?php endif; ?>
     </div>
  <?php endif; ?>
<?php endif; ?>