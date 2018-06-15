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

<?php $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');?>
<?php 
  include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/Adintegration.tpl';
?>

<div class="sr_view_top">
	<?php echo $this->htmlLink($this->sitereview->getHref(array('profile_link' => 1)), $this->itemPhoto($this->sitereview, 'thumb.icon', '', array('align' => 'left'))) ?>
	<h2>	
		<?php echo $this->sitereview->__toString() ?>	
		<?php echo $this->translate('&raquo; '); ?>
		<?php echo $this->htmlLink($this->sitereview->getHref(array('tab'=> $this->tab_selected_id)), $this->translate('Discussions')) ?>
	</h2>
</div>

<?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('communityad') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.communityads', 1) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.adtopicview', 3) && $review_communityad_integration): ?>
	<div class="layout_right" id="communityad_adtopicview">
		<?php echo $this->content()->renderWidget("communityad.ads", array( "itemCount"=>Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.adtopicview', 3),"loaded_by_ajax"=>1,'widgetId'=>'review_adtopicview'))?>
	</div>
<?php endif; ?>

<div class="layout_middle">
  <div class="sitereview_sitereviews_options">

   <?php echo $this->htmlLink($this->sitereview->getHref(), $this->translate("Back to $this->listing_singular_uc"), array('class' => 'buttonlink icon_back')) ?>
    <?php 
      if ($this->can_post) 
      {
        echo $this->htmlLink(array('route' => "sitereview_extended_listtype_$this->listingtype_id", 'controller' => 'topic', 'action' => 'create', 'subject' => $this->sitereview->getGuid(), 'content_id' => $this->tab_selected_id ), $this->translate('Post New Topic'), array('class' => 'buttonlink icon_sitereview_post_new')) ;
      }
    ?>
  </div>

  <?php if( $this->paginator->count() > 1 ): ?>
    <div>
      <br />
      <?php echo $this->paginationControl($this->paginator) ?>
      <br />
    </div>
  <?php endif; ?>

  <ul class="sitereview_sitereviews">
    <?php foreach( $this->paginator as $topic ): ?>
      <?php 
          $lastpost = $topic->getLastPost();
          $lastposter = Engine_Api::_()->getItem('user', $topic->lastposter_id);
      ?>
      <li>

        <div class="sitereview_sitereviews_replies seaocore_txt_light">
          <span>
            <?php echo $this->locale()->toNumber($topic->post_count - 1) ?>
          </span>
          <?php echo $this->translate(array('reply', 'replies', $topic->post_count - 1)) ?>
        </div>

        <div class="sitereview_sitereviews_lastreply">
          <?php echo $this->htmlLink($lastposter->getHref(), $this->itemPhoto($lastposter, 'thumb.icon')) ?>
          <div class="sitereview_sitereviews_lastreply_info">
            <?php echo $this->htmlLink($lastpost->getHref(), $this->translate('Last Post')) ?> <?php echo $this->translate('by');?> <?php echo $lastposter->__toString() ?>
            <br />
            <?php echo $this->timestamp(strtotime($topic->modified_date), array('tag' => 'div', 'class' => 'sitereview_sitereviews_lastreply_info_date seaocore_txt_light')) ?>
          </div>
        </div>

        <div class="sitereview_sitereviews_info">
          <h3<?php if( $topic->sticky ): ?> class='sitereview_sitereviews_sticky'<?php endif; ?>>
            <?php echo $this->htmlLink($topic->getHref(), $topic->getTitle()) ?>
          </h3>
          <div class="sitereview_sitereviews_blurb">
            <?php echo $this->viewMore(strip_tags($topic->getDescription())) ?>
          </div>
        </div>

      </li>
    <?php endforeach; ?>
  </ul>

  <?php if( $this->paginator->count() > 1 ): ?>
    <div>
      <?php echo $this->paginationControl($this->paginator) ?>
    </div>
  <?php endif; ?>

</div>