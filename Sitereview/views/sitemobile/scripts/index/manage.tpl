<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: manage.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>

<?php $listing_title_plural = $this->listingtypeArray->title_plural; ?>
<?php $listing_title_singular = $this->listingtypeArray->title_singular; ?>
<?php
$reviewApi = Engine_Api::_()->sitereview();
$expirySettings = $reviewApi->expirySettings($this->listingtype_id);
$approveDate = null;
if ($expirySettings == 2):
  $approveDate = $reviewApi->adminExpiryDuration($this->listingtype_id);
endif;
?>
<?php if (!Engine_Api::_()->sitemobile()->isApp()): ?>
<style type="text/css">
  .sitereview_browse_list_info_expiry{
    color:red;
  }
</style>
<?php endif;?>

<?php $sitereview_approved = true;
$renew_date = date('Y-m-d', mktime(0, 0, 0, date("m"), date('d', time()) + (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.renew.email', 2)))); ?>
<?php if ($this->current_count >= $this->quota && !empty($this->quota)): ?>
  <div class="tip"> 
    <span><?php echo $this->translate("You have already created the maximum number of $this->listing_plural_lc allowed. If you would like to create a new $this->listing_singular_lc, please delete an old one first."); ?> </span> 
  </div>
  <br/>
<?php endif; ?>

<?php if ($this->paginator->getTotalItemCount() > 0): ?>
  <?php if (!Engine_Api::_()->sitemobile()->isApp()): ?>
  <div class="sm-content-list">
    <ul class="sr_reviews_listing" data-role="listview" data-icon="arrow-r">
      <?php foreach ($this->paginator as $item): ?>
        <?php Engine_Api::_()->sitereview()->setListingTypeInRegistry($item->listingtype_id);
        $listingType = Zend_Registry::get('listingtypeArray' . $item->listingtype_id); ?>
        <li class="b_medium" <?php if (Engine_Api::_()->sitemobile()->isApp()): ?> data-icon="ellipsis-vertical" <?php else : ?> data-icon="cog" <?php endif?> data-inset="true">
          <a href="<?php echo $item->getHref(); ?>">		
            <?php echo $this->itemPhoto($item, 'thumb.normal', '', array('align' => 'center')) ?>
            <h3><?php echo $item->getTitle() ?></h3>
            <p>
							<?php echo $this->translate(array('%s like', '%s likes', $item->like_count), $this->locale()->toNumber($item->like_count)) ?> -
							<?php echo $this->translate(array('%s view', '%s views', $item->view_count), $this->locale()->toNumber($item->view_count)) ?> - 
							<?php echo $this->translate(array('%s comment', '%s comments', $item->comment_count), $this->locale()->toNumber($item->comment_count)) ?> 
							<?php if($this->listingtypeArray->reviews == 3 || $this->listingtypeArray->reviews == 2): ?>
              - <?php echo $this->partial('_showReview.tpl', 'sitereview', array('sitereview' => $item)) ?>
							<?php endif; ?>  
            </p>
            <p>
              <?php echo $this->timestamp(strtotime($item->creation_date)) ?> 
            </p>
            <?php if ($approveDate && $approveDate > $item->approved_date): ?>
              <p class="sitereview_browse_list_info_expiry">
                <?php echo $this->translate('Expired'); ?>
              </p>
            <?php elseif ($expirySettings == 2 && $approveDate && $approveDate < $item->approved_date): ?>
              <?php $exp = $item->getExpiryTime(); ?>
              <p class='seaocore_browse_list_info_date clear'>
                <?php echo $exp ? $this->translate("Expiry On: %s", $this->locale()->toDate($exp, array('size' => 'medium'))) : ''; ?>
              </p>
            <?php elseif ($expirySettings == 1): ?> 
              <p class="seaocore_browse_list_info_date clear">
                <?php $current_date = date("Y-m-d i:s:m", time()); ?>
                <?php if (!empty($item->end_date) && $item->end_date != '0000-00-00 00:00:00'): ?>
                  <?php if ($item->end_date >= $current_date): ?>
                    <?php echo $this->translate("Ending On: %s", $this->locale()->toDate(strtotime($item->end_date), array('size' => 'medium'))); ?>
                  <?php else: ?>
                    <?php echo $this->translate("Ending On: %s", 'Expired', array('size' => 'medium')); ?>
                    <?php echo $this->translate('(You can edit the end date to make the ' . $this->listing_singular_lc . ' live again.)'); ?>
                  <?php endif; ?>
                <?php endif; ?>
              </p>
            <?php endif; ?>
          </a>
            
            
                
          <a href="#manage_<?php echo $item->getGuid() ?>" data-rel="popup" data-transition="pop"></a>
          <div data-role="popup" id="manage_<?php echo $item->getGuid() ?>" <?php echo $this->dataHtmlAttribs("popup_content", array('data-theme' => "c")); ?> data-tolerance="15"  data-overlay-theme="a" data-theme="none" aria-disabled="false" data-position-to="window">
            <div data-inset="true" style="min-width:150px;" class="sm-options-popup">
              <?php if (!Engine_Api::_()->sitemobile()->isApp()): ?>
                <h3><?php echo $item->getTitle() ?></h3>
              <?php endif ?>
                
                <?php if(Engine_Api::_()->sitereview()->hasPackageEnable()):?>
                
                
                <?php 
$listingId = $item->listing_id;
$redirectUrl = $this->url(array(), "sitereview_session_payment_$this->listingtype_id", true)."?listing_id=$listingId";
?>
    	<form name="setSession_form" method="post" id="setSession_form" action="<?php echo $redirectUrl ?>">
			<input type="hidden" name="listing_id_session" id="listing_id_session" />
	</form>
                
                
	<?php if (Engine_Api::_()->sitereviewpaidlisting()->canShowPaymentLink($item->listing_id)): ?>	
                <a class="ui-btn-default ui-link" href='javascript:void(0);' onclick="submitSession(<?php echo $item->listing_id ?>)"><?php echo $this->translate('Make Payment'); ?></a>
	<?php endif; ?>

	<?php if (Engine_Api::_()->sitereviewpaidlisting()->canShowRenewLink($item->listing_id)): ?>
                <a class="ui-btn-default ui-link" href='javascript:void(0);' onclick="submitSession(<?php echo $item->listing_id ?>)"><?php echo $this->translate("Renew $this->listing_singular_uc"); ?></a>
	<?php endif; ?>
        <?php endif;?>
                
              <?php if ($this->can_edit): ?>
              <?php
              echo $this->htmlLink(array(
                  'action' => 'edit',
                  'listing_id' => $item->listing_id,
                  'route' => "sitereview_specific_listtype_$this->listingtype_id",
                  'reset' => true,
                      ), $this->translate('Edit'), array(
                  'class' => 'ui-btn-default ui-btn-action'
              ))
              ?>
              <?php endif; ?>
              <?php if ($this->can_delete): ?>
              <?php
              echo $this->htmlLink(array('route' => "sitereview_specific_listtype_$this->listingtype_id", 'module' => 'sitereview', 'controller' => 'index', 'action' => 'delete', 'listing_id' => $item->listing_id), $this->translate('Delete'), array(
                  'class' => 'smoothbox ui-btn-default ui-btn-danger',
              ));
              ?> 
              <?php endif; ?>
              <?php if (!Engine_Api::_()->sitemobile()->isApp()): ?>
                <a href="#" data-rel="back" class="ui-btn-default">
                  <?php echo $this->translate('Cancel'); ?>
                </a>
              <?php endif?>

            </div> 
          </div>
          
          
          
          
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
 <?php else: ?>
  <?php if(!$this->autoContentLoad):?>
  <ul class="p_list_grid" id='managelistings_ul'>
    <?php endif;?>
          <?php foreach ($this->paginator as $item): ?>
        <?php Engine_Api::_()->sitereview()->setListingTypeInRegistry($item->listingtype_id);
        $listingType = Zend_Registry::get('listingtypeArray' . $item->listingtype_id); ?>
        <li>
          <a href="<?php echo $item->getHref(); ?>" class="ui-link-inherit">		
            <div class="p_list_grid_top_sec">
							<div class="p_list_grid_img">
								<?php
									$url = $item->getPhotoUrl('thumb.profile');
									if (empty($url)): $url = $this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/nophoto_listing_thumb_profile.png';
									endif;
								?>
                <span style="background-image: url(<?php echo $url; ?>);"></span>
							</div>
							<div class="p_list_grid_title">
								<span>
									<?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($item->getTitle(), $this->title_truncationGrid) ?>
								</span>
							</div>
						</div>
          </a>
          <div class="p_list_grid_info">
            <span class="p_list_grid_stats">
              <?php echo $this->timestamp(strtotime($item->creation_date)) ?> 
            </span>
            <span class="p_list_grid_stats">
            <?php echo $this->translate(array('%s like', '%s likes', $item->like_count), $this->locale()->toNumber($item->like_count)) ?> -
            <?php echo $this->translate(array('%s view', '%s views', $item->view_count), $this->locale()->toNumber($item->view_count)) ?> - 
            <?php echo $this->translate(array('%s comment', '%s comments', $item->comment_count), $this->locale()->toNumber($item->comment_count)) ?>
            <?php if($this->listingtypeArray->reviews == 3 || $this->listingtypeArray->reviews == 2): ?>
             - <?php echo $this->partial('_showReview.tpl', 'sitereview', array('sitereview' => $item)) ?>
            <?php endif; ?>
            </span> 
            <?php if ($approveDate && $approveDate > $item->approved_date): ?>
              <span class="p_list_grid_stats">
                <?php echo $this->translate('Expired'); ?>
              </span>
            <?php elseif ($expirySettings == 2 && $approveDate && $approveDate < $item->approved_date): ?>
              <?php $exp = $item->getExpiryTime(); ?>
              <span class="p_list_grid_stats">
                <?php echo $exp ? $this->translate("Expiry On: %s", $this->locale()->toDate($exp, array('size' => 'medium'))) : ''; ?>
              </span>
            <?php elseif ($expirySettings == 1): ?> 
              <span class="p_list_grid_stats">
                <?php $current_date = date("Y-m-d i:s:m", time()); ?>
                <?php if (!empty($item->end_date) && $item->end_date != '0000-00-00 00:00:00'): ?>
                  <?php if ($item->end_date >= $current_date): ?>
                    <?php echo $this->translate("Ending On: %s", $this->locale()->toDate(strtotime($item->end_date), array('size' => 'medium'))); ?>
                  <?php else: ?>
                    <?php echo $this->translate("Ending On: %s", 'Expired', array('size' => 'medium')); ?>
                    <?php echo $this->translate('(You can edit the end date to make the ' . $this->listing_singular_lc . ' live again.)'); ?>
                  <?php endif; ?>
                <?php endif; ?>
            </span>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>  
    <?php if(!$this->autoContentLoad):?>
    </ul>
  <?php endif;?>
  <?php endif;?>
<?php elseif ($this->search): ?>
  <div class="tip"> 
    <span>
      <?php
      if (!empty($sitereview_approved)) {
        echo $this->translate('You do not have any ' . strtolower($listing_title_singular) . ' that match your search criteria.');
      } else {
        echo $this->translate($this->listing_manage_msg);
      }
      ?> 
    </span> 
  </div>
<?php else: ?>
  <div class="tip">
    <span> 
      <?php
      if (!empty($sitereview_approved)) {
        echo $this->translate('You do not have any ' . strtolower($listing_title_plural) . '.');
      } else {
        echo $this->translate($this->listing_manage_msg);
      }
      ?>
    </span> 
  </div>
<?php endif; ?>
 <?php if (!Engine_Api::_()->sitemobile()->isApp()): ?> 
<?php echo $this->paginationControl($this->paginator, null, null, array('query' => $this->formValues, 'pageAsQuery' => true)); ?>
  <?php endif;?>


<?php if (Engine_Api::_()->sitemobile()->isApp()) { ?>
     <script type="text/javascript">       
         sm4.core.runonce.add(function() { 
              var activepage_id = sm4.activity.activityUpdateHandler.getIndexId();
              sm4.core.Module.core.activeParams[activepage_id] = {'currentPage' : '<?php echo sprintf('%d', $this->page) ?>', 'totalPages' : '<?php echo sprintf('%d', $this->totalPages) ?>', 'formValues' : <?php echo json_encode($this->formValues);?>, 'contentUrl' : '<?php echo $this->url(array('action' => 'manage'));?>', 'activeRequest' : false, 'container' : 'managelistings_ul' }; 
          });
  </script>       
   <?php } ?>    
  <script>
    function submitSession(id) {
          if (sm4.core.isApp()) {
              var title = '<?php echo $this->listing_singular_uc?>';
            alert( "Please go to the Full Site to Make Payment for your "+title+" listing.");
        }else{
	$("#listing_id_session").value=id;
	$("#setSession_form").submit();
        }
}
      
      </script>