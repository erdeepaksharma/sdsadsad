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
  $this->headScript()
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/scripts/core.js');
?>

<?php if($this->loaded_by_ajax):?>
  <script type="text/javascript">
    var params = {
      requestParams :<?php echo json_encode($this->params) ?>,
      responseContainer :$$('.layout_sitereview_profile_sitereview')
    }
    en4.sitereview.ajaxTab.attachEvent('<?php echo $this->identity ?>',params);
  </script>
<?php endif;?>

<?php if($this->showContent): ?>
    <script type="text/javascript">
      en4.core.runonce.add(function(){

        <?php if( !$this->renderOne ): ?>
          var listingtype_id = <?php echo $this->listingtype_id; ?>;
          if(listingtype_id < 0) { listingtype_id = 0; }
          var anchor = $('profile_sitereviews').getParent();
          $('profile_lists_previous_'+listingtype_id).style.display = '<?php echo ( $this->paginator->getCurrentPageNumber() == 1 ? 'none' : '' ) ?>';
          $('profile_lists_next_'+listingtype_id).style.display = '<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' ) ?>';

          $('profile_lists_previous_'+listingtype_id).removeEvents('click').addEvent('click', function(){
            en4.core.request.send(new Request.HTML({
              url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
              data : {
                format : 'html',
                isajax : 1,        
                subject : en4.core.subject.guid,
                page : <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() - 1) ?>
              }
            }), {
              'element' : anchor
            })
          });

          $('profile_lists_next_'+listingtype_id).removeEvents('click').addEvent('click', function(){
            en4.core.request.send(new Request.HTML({
              url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
              data : {
                format : 'html',
                isajax : 1,        
                subject : en4.core.subject.guid,
                page : <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() + 1) ?>
              }
            }), {
              'element' : anchor
            })
          });
        <?php endif; ?>
      });
    </script>

    <?php 
      $ratingValue = $this->ratingType; 
      $ratingShow = 'small-star';
        if ($this->ratingType == 'rating_editor') {$ratingType = 'editor';} elseif ($this->ratingType == 'rating_avg') {$ratingType = 'overall';} else { $ratingType = 'user';}
    ?>

    <ul id="profile_sitereviews" class="sr_browse_list">
      <?php foreach( $this->paginator as $sitereview ): ?>
        <?php Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
              $listingType = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);?>
        <li class="b_medium">
          <div class='sr_browse_list_photo b_medium'>
            <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)):?>
              <?php if($sitereview->featured):?>
                <i class="sr_list_featured_label" title="<?php echo $this->translate('Featured'); ?>"></i>
              <?php endif;?>
              <?php if($sitereview->newlabel):?>
                <i class="sr_list_new_label" title="<?php echo $this->translate('New'); ?>"></i>
              <?php endif;?>
            <?php endif;?>
            <?php echo $this->htmlLink($sitereview->getHref(array('profile_link' => 1)), $this->itemPhoto($sitereview, 'thumb.normal', '', array('align' => 'center'))) ?>
            <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)):?>
              <?php if (!empty($sitereview->sponsored)): ?>
                  <div class="sr_list_sponsored_label" style="background: <?php echo $listingType->sponsored_color; ?>">
                    <?php echo $this->translate('SPONSORED'); ?>                 
                  </div>
              <?php endif; ?>
            <?php endif; ?>
          </div>
          <div class='sr_browse_list_info'>
            <div class="sr_browse_list_show_rating fright">
              <?php if($ratingValue == 'rating_both'): ?>
                <?php echo $this->showRatingStar($sitereview->rating_editor, 'editor', $ratingShow, $sitereview->listingtype_id); ?>
                <br/>
                <?php echo $this->showRatingStar($sitereview->rating_users, 'user', $ratingShow, $sitereview->listingtype_id); ?>
              <?php else: ?>
                <?php echo $this->showRatingStar($sitereview->$ratingValue, $ratingType, $ratingShow, $sitereview->listingtype_id); ?>
              <?php endif; ?>
            </div>
            <div class='sr_browse_list_info_header'>
              <div class="sr_list_title_small o_hidden">
                <?php echo $this->htmlLink($sitereview->getHref(),  Engine_Api::_()->seaocore()->seaocoreTruncateText($sitereview->getTitle(), $this->title_truncation), array('title' => $sitereview->getTitle())) ?>
              </div>
            </div>
            <div class='sr_browse_list_info_stat seaocore_txt_light'>
              <?php echo $this->timestamp(strtotime($sitereview->creation_date)) ?>,

              <?php if(!empty($this->statistics)): ?>
                  <?php 

                    $statistics = '';

                    if(in_array('commentCount', $this->statistics)) {
                      $statistics .= $this->translate(array('%s comment', '%s comments', $sitereview->comment_count), $this->locale()->toNumber($sitereview->comment_count)).', ';
                    }

                    if(in_array('reviewCount', $this->statistics) && (!empty($listingType->allow_review) || (isset($sitereview->rating_editor) && $sitereview->rating_editor))) {
                      $statistics .= $this->partial(
                      '_showReview.tpl', 'sitereview', array('sitereview'=>$sitereview)).', ';
                    }

                    if(in_array('viewCount', $this->statistics)) {
                      $statistics .= $this->translate(array('%s view', '%s views', $sitereview->view_count), $this->locale()->toNumber($sitereview->view_count)).', ';
                    }

                    if(in_array('likeCount', $this->statistics)) {
                      $statistics .= $this->translate(array('%s like', '%s likes', $sitereview->like_count), $this->locale()->toNumber($sitereview->like_count)).', ';
                    }                 

                    $statistics = trim($statistics);
                    $statistics = rtrim($statistics, ',');

                  ?>

                  <?php echo $statistics; ?>

                <?php endif ?>
            </div>
            <div class='sr_browse_list_info_blurb'>
              <?php echo substr(strip_tags($sitereview->body), 0, 350); if (strlen($sitereview->body)>349) echo $this->translate("...");?>
            </div>
            <div class="sr_browse_list_info_footer clr o_hidden mtop5">
              <div class="sr_browse_list_info_footer_icons"> 
                <?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)) :?>
                  <?php if ($sitereview->sponsored == 1): ?>
                    <i title="<?php echo $this->translate('Sponsored');?>" class="sr_icon seaocore_icon_sponsored"></i>
                  <?php endif; ?>
                  <?php if ($sitereview->featured == 1): ?>
                    <i title="<?php echo $this->translate('Featured');?>" class="sr_icon seaocore_icon_featured"></i>
                  <?php endif; ?>
                <?php endif;?>
                <?php if( $sitereview->closed ): ?>
                  <i class="sr_icon icon_sitereviews_close" title="<?php echo $this->translate('Closed'); ?>"></i>
                <?php endif;?>
              </div>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="seaocore_profile_list_more">
      <?php if($this->listingtype_id < 0): ?>
        <?php $listingtype_id = 0; ?>
      <?php else: ?>
        <?php $listingtype_id = $this->listingtype_id; ?>
      <?php endif; ?>	
      <div id="profile_lists_previous_<?php echo $listingtype_id?>" class="paginator_previous" style="display:none;">
        <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array(
          'onclick' => '',
          'class' => 'buttonlink icon_previous'
        )); ?>
      </div>
      <div id="profile_lists_next_<?php echo $listingtype_id?>" class="paginator_next" style="display:none;">
        <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array(
          'onclick' => '',
          'class' => 'buttonlink_right icon_next'
        )); ?>
      </div>
    </div>
<?php endif;?>
