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
	$this->headScript()
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/scripts/core.js');
  $this->headLink()
        ->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_rating.css');
  $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');?>

<?php if($this->loaded_by_ajax):?>
  <script type="text/javascript">
    var params = {
      requestParams :<?php echo json_encode($this->params) ?>,
      responseContainer :$$('.layout_sitereview_editor_profile_reviews_sitereview')
    }
    en4.sitereview.ajaxTab.attachEvent('<?php echo $this->identity ?>',params);
  </script>
<?php endif;?>

<?php if($this->showContent): ?>
    <?php if ($this->type == 'editor'): ?>
      <script type="text/javascript">

        var paginatorEditorReviewSitereview = function(page) {
          var url = en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>;
          en4.core.request.send(new Request.HTML({
            'url' : url,
            'data' : {
              'format' : 'html',
              'subject' : en4.core.subject.guid,
              'page' : page,
              'isAjax' : 1,
              'itemCount' : '<?php echo $this->itemCount ?>',
              'type':'<?php echo $this->type ?>'
            }
          }), {
            'element' : $('editorReviewContent_<?php echo $this->identity; ?>').getParent()
          });
        }

      </script>

      <div id='editorReviewContent_<?php echo $this->identity; ?>' class="o_hidden">
        <?php if ($this->showEditorLink): ?>
          <h4 class="o_hidden">
            <?php echo $this->htmlLink(array('route' => "sitereview_review_editor_profile", 'username' => $this->user->username, 'user_id' => $this->subject()->user_id),  $this->translate('View Editor Profile'), array('class'=>'fright buttonlink_right icon_next')) ?>
          </h4>
        <?php endif; ?>
        <ul class="sr_reviews_listing o_hidden clr">
          <?php if($this->paginator->getTotalItemCount()): ?>
          <?php foreach ($this->paginator as $review): ?>
            <li>
              <div class='review_info'>
                <div class='sr_reviews_listing_title'>
                  <?php if($review->featured): ?>
                    <i class="sr_icon seaocore_icon_featured fright" title="<?php echo $this->translate('Featured'); ?>"></i> 
                  <?php endif; ?>	 
                  <?php echo $this->htmlLink($review->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($review->title, $this->truncation), array('title' => $review->title)) ?>
                </div>

                <div class="sr_reviews_listing_stat mbot5">
                  <?php $ratingData = $review->getRatingData(); ?>
                  <?php
                  $rating_value = 0;
                  foreach ($ratingData as $reviewcat):
                    if (empty($reviewcat['ratingparam_name'])):
                      $rating_value = $reviewcat['rating'];
                      break;
                    endif;
                  endforeach;
                  ?>
                  <?php echo $this->showRatingStar($rating_value, $review->type, 'big-star', $review->getParent()->listingtype_id); ?>
                </div>

                <?php $sitereview = $review->getParent() ?>
                <div class="sr_reviews_listing_date seaocore_txt_light">
                  <?php echo $this->translate('For'); ?>  
                  <?php echo $this->htmlLink($sitereview->getHref(), $sitereview->getTitle()) ?> 
                  <?php echo $this->translate('on %s', $this->timestamp(strtotime($review->modified_date))); ?>
                </div>          
              </div>
            </li>
          <?php endforeach; ?>
          <?php else: ?>
            <div class="tip mtop10"> 
              <span> 
                <?php echo $this->translate('No Editor Review has been written yet.'); ?>
              </span>
            </div>       
          <?php endif; ?>  
        </ul>
        <div class="seaocore_pagination">
          <?php if ($this->paginator->getCurrentPageNumber() > 1): ?>
            <div id="sitereview_previous_<?php echo $this->identity; ?>" class="paginator_previous">
              <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array('onclick' => 'paginatorEditorReviewSitereview(' . $this->page . ' - 1)', 'class' => 'buttonlink icon_previous')); ?>
            </div>
          <?php endif; ?>
          <?php if ($this->paginator->getCurrentPageNumber() < $this->paginator->count()): ?>
            <div id="sitereview_next_<?php echo $this->identity; ?>" class="paginator_next">
              <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array('onclick' => 'paginatorEditorReviewSitereview(' . $this->page . ' + 1)', 'class' => 'buttonlink_right icon_next')); ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <script type="text/javascript">

        var paginatorUserReviewSitereview = function(page) {
          var url = en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>;
          en4.core.request.send(new Request.HTML({
            'url' : url,
            'data' : {
              'format' : 'html',
              'subject' : en4.core.subject.guid,
              'page' : page,
              'isAjax' : 1,
              'itemCount' : '<?php echo $this->itemCount ?>',
              'type':'<?php echo $this->type ?>'
            }
          }), {
            'element' : $('userReviewContent_<?php echo $this->identity;?>').getParent()
          });
        }
        var active_request_review = false;
        function reviewHelpful(option, review_id) {
        if(active_request_review)
         return;
        <?php if (!$this->viewer_id): ?>
          return;
        <?php endif; ?>
        active_request_review = true;
        var url = en4.core.baseUrl+'sitereview/review/helpful'+"/helpful/" + option+ '/review_id/' +review_id ;
        var request = new Request.HTML({ 
          url : url,
          data : {
            format : 'html'
          },
          onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) { 
            if($('review_helpful_message_' + review_id )) {        
              $('review_helpful_message_' + review_id ).style.display = 'block';
            }
            $('review_helpful_' + review_id).style.display = 'none';
            active_request_review = false;
          }
        });
        request.send();
        return false;
      }
      </script>

      <div id='userReviewContent_<?php echo $this->identity;?>' class="o_hidden">
        <ul class="sr_reviews_listing o_hidden clr">
          <?php foreach ($this->paginator as $review): ?>
            <li>
              <div class=" sr_reviews_listing_title">
                <?php if($review->featured): ?>
                    <i class="sr_icon seaocore_icon_featured fright" title="<?php echo $this->translate('Featured'); ?>"></i> 
                <?php endif; ?>	
                <?php echo $this->htmlLink($review->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($review->title, $this->truncation), array('title' => $review->title)) ?>
              </div>

              <div class="sr_reviews_listing_stat mbot5">
                <?php $ratingData = $review->getRatingData(); ?>
                <?php
                $rating_value = 0;
                foreach ($ratingData as $reviewcat):
                  if (empty($reviewcat['ratingparam_name'])):
                    $rating_value = $reviewcat['rating'];
                    break;
                  endif;
                endforeach;
                ?>
                 <?php echo $this->showRatingStar($rating_value, 'user', 'big-star', $review->getParent()->listingtype_id); ?>
              </div>

              <?php $sitereview = $review->getParent() ?>
              <div class="sr_reviews_listing_date seaocore_txt_light">
                <?php echo $this->translate('For'); ?>  
                <?php echo $this->htmlLink($sitereview->getHref(), $sitereview->getTitle()) ?> 
                <?php echo $this->translate('on %s', $this->timestamp(strtotime($review->modified_date))); ?>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
        <div class="seaocore_pagination">
          <?php if ($this->paginator->getCurrentPageNumber() > 1): ?>
            <div id="sitereview_previous_<?php echo $this->identity; ?>" class="paginator_previous">
              <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array('onclick' => 'paginatorUserReviewSitereview(' . $this->page . ' - 1)', 'class' => 'buttonlink icon_previous')); ?>
            </div>
          <?php endif; ?>
          <?php if ($this->paginator->getCurrentPageNumber() < $this->paginator->count()): ?>
            <div id="sitereview_next_<?php echo $this->identity; ?>" class="paginator_next">
              <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array('onclick' => 'paginatorUserReviewSitereview(' . $this->page . ' + 1)', 'class' => 'buttonlink_right icon_next')); ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
<?php endif; ?>