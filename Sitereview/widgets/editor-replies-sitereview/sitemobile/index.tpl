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

<?php if(empty($this->is_ajax)):?>
	<div id='editorReviewCommentContent' class="sm-content-list">
<?php endif;?>

<?php  if(count($this->replies) > 0):?>
  <?php if(empty($this->is_ajax)):?>
    <ul data-role="listview" data-icon="arrow-r" id="sr_editor_profile_content" class="sr_editor_profile_content">
  <?php endif;?>
  <?php foreach ($this->replies as $reply): ?>
   <?php $parentItem = $reply->getParent();?>
    <li>
      <a href="<?php echo $parentItem->getHref(). '#comments_'. $parentItem->getGuid(). '_0_', $parentItem->getTitle();?>"> 
				"<?php echo $this->smileyToEmoticons($this->translate(Engine_Api::_()->seaocore()->seaocoreTruncateText($reply->body, $this->truncation))) ?>"
				<?php if(!empty($reply->parent_comment_id)):?>
					<p><?php echo $this->translate("Replied On");?>
				<?php else:?>
					<p><?php echo $this->translate("Commented On");?>
				<?php endif;?>
				- 
       <?php echo $this->timestamp(strtotime($reply->creation_date)) ?></p>
			</a>
    </li>
  <?php endforeach;?>
  <?php if(empty($this->is_ajax)):?>
    </ul>
  <?php endif;?>
<?php else: ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("This Editor has not posted any comments yet!"); ?>
    </span> 
  </div>
<?php endif; ?>

<?php if(empty($this->is_ajax)):?>
	</div>
<?php endif;?>

<?php if(empty($this->is_ajax)):?>
	<div class="feed_viewmore" id="profile_view_more" onclick="viewMoreReply()">
		<?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array(
			'id' => 'feed_viewmore_link',
			'class' => 'ui-btn-default icon_viewmore'
		)) ?>
	</div>
  <div class="feeds_loading" id="loding_image" style="display: none;">
    <i class="ui-icon-spinner ui-icon icon-spin"></i>
   </div>
<?php endif;?>

<script type="text/javascript">
 	
	function viewMoreReply() {
		$('#profile_view_more').css('display', 'none');
    $('#loding_image').css('display','');
		sm4.core.request.send({
			'url' : sm4.core.baseUrl + 'core/widget/index/mod/sitereview/name/editor-replies-sitereview',
      type: "POST", 
      dataType: "html",
			'data' : {
				format : 'html',
				is_ajax : 1,
				page: getNextPage(),
        subject: sm4.core.subject.guid,
        itemCount: '<?php echo $this->itemCount?>',
        onlyListingtypeEditor: '<?php echo $this->onlyListingtypeEditor?>'
			},
      success : function(response) {	
				$.mobile.activePage.find('#editorReviewCommentContent').find('.sr_editor_profile_content').append(response).listview().listview('refresh');
        $.mobile.activePage.find('#loding_image').css('display','none');
				sm4.core.runonce.trigger();
				sm4.core.refreshPage();
				return false;
      }
		});
		return false;
	}
  
  sm4.core.runonce.add(function() {
    hideViewMoreLink();
  });

	function hideViewMoreLink(){
		$('#profile_view_more').css('display', '<?php echo ( $this->replies->count() == $this->replies->getCurrentPageNumber() || $this->replyCount == 0 ? 'none' : '' ) ?>');
	}

	function getNextPage(){
		return <?php echo sprintf('%d', $this->page + 1) ?>
	}
</script>