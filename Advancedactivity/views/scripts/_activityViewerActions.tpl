<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _activityText.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>

<?php $sharesTable = Engine_Api::_()->getDbtable('shares', 'advancedactivity'); ?>
<?php if( !$this->ignoreScriptInclude ): ?>
  <script type="text/javascript">
    en4.core.runonce.add(function () {
      en4.advancedactivity.bindOnLoadForViewerFeeds({
        allowReaction: <?php echo $this->allowReaction ? 1 : 0 ?>,
        likeLoadUrl: '<?php echo $this->url(array('module' => 'advancedactivity', 'controller' => 'index', 'action' => 'get-likes'), 'default', true) ?>'
      })
    });
  </script>
<?php endif; ?>

<?php
$advancedactivityCoreApi = Engine_Api::_()->advancedactivity();
$advancedactivitySaveFeed = Engine_Api::_()->getDbtable('saveFeeds', 'advancedactivity');
?>
<?php
$action = $this->action;
$subject = $action->getSubject();
$object = $action->getObject();
?>
<?php $item = (isset($action->getTypeInfo()->is_object_thumb) && !empty($action->getTypeInfo()->is_object_thumb)) ? $action->getObject() : $action->getSubject(); ?>

<?php
if( $this->onViewPage ): $actionBaseId = "view-" . $action->action_id;
else:$actionBaseId = $action->action_id;
endif;
?>
<?php
$this->commentForm->setActionIdentity($actionBaseId);
$this->commentForm->action_id->setValue($action->action_id);
?>
<script type="text/javascript">
  (function () {

    en4.core.runonce.add(function () {

      $('<?php echo $this->commentForm->body->getAttrib('id') ?>').autogrow();
      var allowQuickComment = '<?php echo ($this->isMobile || !$this->commentShowBottomPost || Engine_Api::_()->getApi('settings', 'core')->core_spam_comment) ? 0 : 1; ?>';
      en4.advancedactivity.attachComment($('<?php echo $this->commentForm->getAttrib('id') ?>'), allowQuickComment);

      if (allowQuickComment == '1' && <?php echo $this->submitComment ? '1' : '0' ?>) {
        document.getElementById("<?php echo $this->commentForm->getAttrib('id') ?>").style.display = "";
        document.getElementById("<?php echo $this->commentForm->submit->getAttrib('id') ?>").style.display = "none";
        if (document.getElementById("feed-comment-form-open-li_<?php echo $actionBaseId ?>")) {
          document.getElementById("feed-comment-form-open-li_<?php echo $actionBaseId ?>").style.display = "none";
        }
        document.getElementById("<?php echo $this->commentForm->body->getAttrib('id') ?>").focus();
      }
<?php if( Engine_Api::_()->getApi('settings', 'core')->getSetting('aaf.comment.like.box', 0) ): ?>$('comment-likes-activityboox-item-<?php echo $action->action_id; ?>').toggle();
<?php endif; ?>
    });
  })();
</script>


<?php // Icon, time since, action links  ?>
<?php
$canComment = ( $action->getTypeInfo()->commentable && $action->commentable &&
  $this->viewer()->getIdentity() &&
  Engine_Api::_()->authorization()->isAllowed($action->getCommentObject(), null, 'comment') &&
  !empty($this->commentForm) );
?>
<div class='feed_item_date feed_item_icon'>
    <ul class = "<?php echo ($this->settings('advancedactivity.feed.menu.align', 'right') == 'right') ? 'txt_right' : '' ?>" >
    <?php if( $canComment ): ?>
      <?php if( $this->allowReaction ): ?>
        <li class="feed_item_option_reaction seao_icons_toolbar_attach">
          <?php
          echo $this->reactions($action, array(
            'target' => $action->action_id,
            'id' => 'like_' . $action->action_id,
            'class' => 'aaf_like_toolbar'
          ));
          ?>
        </li>
      <?php else: ?>
        <?php if( $action->likes()->isLike($this->viewer()) ): ?>
          <li class="feed_item_option_unlike">
            <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Unlike'), array('onclick' => 'javascript:en4.advancedactivity.unlike(this,' . $action->action_id . ');', 'action-title' => $this->translate('Like'))) ?>
          </li>
        <?php else: ?>
          <li class="feed_item_option_like">
            <?php
            echo $this->htmlLink('javascript:void(0);', $this->translate('Like'), array('onclick' => 'javascript:en4.advancedactivity.like(this,' . $action->action_id . ');', 'title' =>
              $this->translate('Like this item'), 'action-title' => $this->translate('Unlike'), 'id' => 'aaf_like_' . $action->action_id))
            ?>

          </li>
        <?php endif; ?>
      <?php endif; ?>
      <?php if( Engine_Api::_()->getApi('settings', 'core')->core_spam_comment ): // Comments - likes    ?>
        <li class="feed_item_option_comment">
          <?php
          echo $this->htmlLink(array('route' => 'default', 'module' => 'activity', 'controller' => 'index', 'action' => 'viewcomment', 'action_id' => $action->getIdentity(), 'format' => 'smoothbox'), $this->translate('Comment'), array(
            'class' => 'smoothbox', 'title' => $this->translate('Leave a comment')
          ))
          ?>

        </li>
      <?php else: ?>
        <li class="feed_item_option_comment">
          <?php
          echo $this->htmlLink('javascript:void(0);', $this->translate('Comment'), array('onclick' => 'document.getElementById("' . $this->commentForm->getAttrib('id') . '").style.display = "";
document.getElementById("' . $this->commentForm->submit->getAttrib('id') . '").style.display = "' . (($this->isMobile || !$this->commentShowBottomPost || Engine_Api::_()->getApi('settings', 'core')->core_spam_comment) ? "block" : "none") . '";
 if(document.getElementById("feed-comment-form-open-li_' . $actionBaseId . '")){
document.getElementById("feed-comment-form-open-li_' . $actionBaseId . '").style.display = "none";}
document.getElementById("' . $this->commentForm->body->getAttrib('id') . '").focus();document.getElementById("' . "comment-likes-activityboox-item-$actionBaseId" . '").style.display = "block"; ', 'title' =>
            $this->translate('Leave a comment')))
          ?>

        </li>
      <?php endif; ?>
    <?php endif; ?>
    <?php if( in_array($action->getTypeInfo()->type, array('signup', 'friends', 'friends_follow')) ): ?>    
      <?php $userFriendLINK = $this->aafUserFriendshipAjax($action); ?>
      <?php if( $userFriendLINK ): ?>
        <li><?php echo $userFriendLINK; ?>
        </li>  
      <?php endif; ?>
    <?php endif; ?>
    <?php
    if( $this->viewer()->getIdentity() && (
      'user' == $action->subject_type && $this->viewer()->getIdentity() == $action->subject_id) && $advancedactivityCoreApi->hasFeedTag($action) 
    ):
      ?>
      <li class="feed_item_option_add_tag">
        <?php
        echo $this->htmlLink(array(
          'route' => 'default',
          'module' => 'advancedactivity',
          'controller' => 'feed',
          'action' => 'tag-friend',
          'id' => $action->action_id
          ), $this->translate('Tag Friends'), array('class' => 'smoothbox icon_friend_add', 'title' =>
          $this->translate('Tag more friends')))
        ?>

      </li>
    <?php elseif( $this->viewer()->getIdentity() && $advancedactivityCoreApi->hasMemberTagged($action, $this->viewer()) ): ?>  
      <li class="feed_item_option_remove_tag">
        <?php
        echo $this->htmlLink(array(
          'route' => 'default',
          'module' => 'advancedactivity',
          'controller' => 'feed',
          'action' => 'remove-tag',
          'id' => $action->action_id
          ), $this->translate('Remove Tag'), array('class' => 'smoothbox icon_friend_remove'))
        ?>
      </li>
    <?php endif; ?>


    <?php // Share  ?>
    <?php $shareableItem = $action->getShareableItem(); ?>
    <?php if( $this->viewer()->getIdentity() && $shareableItem ): ?>
      <li class="feed_item_option_share seao_icons_toolbar_attach">
        <?php
        echo $this->settings('aaf.social.share.enable', 1) ? $this->shareIcons($action) : '';
        echo $this->htmlLink(array('route' => 'default', 'module' => 'seaocore',
          'controller' => 'activity', 'action' => 'share', 'type' => $shareableItem->getType(), 'id' =>
          $shareableItem->getIdentity(), 'action_id' => $action->getIdentity(), 'format' => 'smoothbox', "not_parent_refresh" => 1), $this->translate('ADVACADV_SHARE'), array('class' => 'smoothbox share_icons_link', 'title' => $this->translate('Share this by re-posting it with your own message.')))
        ?>
      </li>
    <?php endif; ?>
    <?php if( $canComment && Engine_Api::_()->getApi('settings', 'core')->getSetting('aaf.comment.like.box', 0) ): ?>
      <?php $likeCount = $action->likes()->getLikeCount(); ?>
      <?php $commentCount = $action->comments()->getCommentCount() ?>  
      <?php if( $likeCount || $commentCount ): ?>
        <li class="like_comment_counts" onclick="$('comment-likes-activityboox-item-<?php echo $actionBaseId ?>').toggle()">
          <?php if( $likeCount ): ?>
            <span class="like_icon"><?php echo $this->locale()->toNumber($likeCount); ?></span>
          <?php endif; ?>
          <?php if( $commentCount ): ?>
            <span class="comment_icon"><?php echo $this->locale()->toNumber($commentCount); ?></span>
          <?php endif; ?>

        </li>
      <?php endif; ?>
    <?php endif; ?>
    <?php $feedBodyText = $this->string()->escapeJavascript(strip_tags($action->body)); ?>
    <?php if( $this->settings('aaf.translation.feed.enable', 1) && !empty($feedBodyText) ) : ?>
      <li class="feed_item_option_translate">
        <a href="javascript:void(0)" onclick="en4.advancedactivity.translateFeed('<?php echo $feedBodyText; ?>')" title="<?php echo $this->translate('Translate') ?>"><?php echo $this->translate('Translate') ?></a>
      </li>
    <?php endif; ?>

    <?php $category = $action->getCategory(); ?>
    <?php if( $category ): ?>
      <li>
        <span class = "aaf_feed_category feed_item_option_add_tag">
          <?php echo $this->translate("in %s", $this->htmlLink($category->getHref(), $this->translate($category->getTitle()), array('class' => ''))) ?>
        </span>
      </li>
    <?php endif; ?>
  </ul>
</div>



<?php if( ($action->getTypeInfo()->commentable && $action->commentable) ) : // Comments - likes -share       ?>
  <div id='comment-likes-activityboox-item-<?php echo $actionBaseId ?>' class='comments' <?php if( !$this->viewAllLikes && !$this->viewAllComments && Engine_Api::_()->getApi('settings', 'core')->getSetting('aaf.comment.like.box', 0) && $this->viewer()->getIdentity() ): ?>style="display: none;"  <?php endif; ?>>
    <ul>  
      <?php // Share Count   ?>
      <?php if( $action->getTypeInfo()->shareable && $action->shareable && 0 ): ?>       
        <li class="aaf_share_counts">
          <div></div>
          <div class="comments_likes">
            <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'advancedactivity', 'controller' => 'index', 'action' => 'share-item', 'action_id' => $action->getIdentity(), 'format' => 'smoothbox'), $this->translate(array('%s share', '%s shares', $share), $this->locale()->toNumber($share)), array('class' => 'smoothbox seaocore_icon_share aaf_commentbox_icon')) ?>
          </div>
        </li>
      <?php endif; ?>


      <?php if( $action->getTypeInfo()->commentable && $action->commentable ): // Comments - likes -share ?>
        <?php if( $action->likes()->getLikeCount() > 0 && (count($action->likes()->getAllLikesUsers()) > 0) ): ?>
          <li>
            <div></div>
            <div class="comments_likes">
              <?php if( $this->allowReaction ): ?>
                <?php echo $this->likeReactionsLink($action); ?>
              <?php endif; ?>
              <?php if( $action->likes()->getLikeCount() <= 3 || $this->viewAllLikes ): ?>
                <?php if( $this->allowReaction ): ?>
                  <?php
                  $likeS = '%s reacted on this.';
                  $likeP = '%s react this.';
                  ?>
                <?php else: ?>
                  <?php
                  $likeS = '%s likes this.';
                  $likeP = '%s like this.';
                  ?>
                <?php endif; ?>
                <?php echo $this->translate(array($likeS, $likeP, $action->likes()->getLikeCount()), $this->aafFluentList($action->likes()->getAllLikesUsers())) ?>

              <?php else: ?>
                <?php if( $this->allowReaction ): ?>
                  <?php
                  $likeS = '%s person reacted on this';
                  $likeP = '%s people react this'
                  ?>
                  <?php
                  $url = $this->url(array('action' => 'likes', 'module' => 'sitereaction',
                    'controller' => 'index', 'subject_type' => $action->getType(), 'subject_id' => $action->getIdentity()), 'default', true);
                  ?>
                <?php else: ?>
                  <?php
                  $likeS = '%s person likes this';
                  $likeP = '%s people like this'
                  ?>
                  <?php
                  $url = $action->getHref(array('show_likes' => true));
                  $class = '';
                  ?>
                <?php endif; ?>
                <?php
                echo $this->htmlLink($url, $this->translate(array($likeS, $likeP, $action->likes()->getLikeCount()), $this->locale()->toNumber($action->likes()->getLikeCount())), array('class' => $class)
                )
                ?>
              <?php endif; ?>
            </div>
          </li>
        <?php endif; ?>  
        <?php if( $action->comments()->getCommentCount() > 0 ): ?>
          <?php if( $action->comments()->getCommentCount() > 5 && !$this->viewAllComments ): ?>
            <li>
              <div></div>
              <div class="comments_viewall">
                <?php if( $action->comments()->getCommentCount() > 2 ): ?>
                  <?php
                  echo $this->htmlLink($action->getHref(array('show_comments' => true)), $this->translate(array('View all %s comment', 'View all %s comments', $action->comments()->getCommentCount()), $this->locale()->toNumber($action->comments()->getCommentCount())))
                  ?>
                <?php else: ?>
                  <?php
                  echo $this->htmlLink('javascript:void(0);', $this->translate(array('View all %s comment', 'View all %s comments', $action->comments()->getCommentCount()), $this->locale()->toNumber($action->comments()->getCommentCount())), array('onclick' => 'en4.advancedactivity.viewComments(' . $action->action_id . ');'))
                  ?>
                <?php endif; ?>
              </div>
            </li>
          <?php endif; ?>
          <?php foreach( $action->getComments($this->viewAllComments) as $comment ): ?>
            <li id="comment-<?php echo $comment->comment_id ?>">
              <div class="comments_author_photo">
                <?php
                echo $this->htmlLink($this->item($comment->poster_type, $comment->poster_id)->getHref(), $this->itemPhoto($this->item($comment->poster_type, $comment->poster_id), 'thumb.icon', $action->getSubject()->getTitle()), array('class' => 'notranslate sea_add_tooltip_link', 'rel' => $this->item($comment->poster_type, $comment->poster_id)->getType() . ' ' . $this->item($comment->poster_type, $comment->poster_id)->getIdentity())
                )
                ?>
              </div>
              <div class="comments_info">
                <span class='comments_author'>
                  <?php
                  echo $this->htmlLink($this->item($comment->poster_type, $comment->poster_id)->getHref(), $this->item($comment->poster_type, $comment->poster_id)->getTitle(), array('class' => 'notranslate sea_add_tooltip_link', 'rel' => $this->item($comment->poster_type, $comment->poster_id)->getType() . ' ' . $this->item($comment->poster_type, $comment->poster_id)->getIdentity())
                  );
                  ?>
                  <?php
                  if( $this->viewer()->getIdentity() &&
                    (('user' == $action->subject_type && $this->viewer()->getIdentity() == $action->subject_id) ||
                    ("user" == $comment->poster_type && $this->viewer()->getIdentity() == $comment->poster_id) ||
                    ("user" !== $comment->poster_type && Engine_Api::_()->getItemByGuid($comment->poster_type . "_" . $comment->poster_id)->isOwner($this->viewer())) ||
                    $this->activity_moderate ) ):
                    ?>
                    <a href="javascript:void(0);" class="aaf_icon_remove" title="<?php
                    echo
                    $this->translate('Delete Comment')
                    ?>" onclick="deletefeed('<?php
                       echo
                       $action->action_id
                       ?>', '<?php echo $comment->comment_id ?>', '<?php
                       echo
                       $this->escape($this->url(array('route' => 'default',
                           'module' => 'advancedactivity', 'controller' => 'index', 'action' => 'delete')))
                       ?>')"></a>
                     <?php endif; ?>
                </span>
                <span class="comments_body">
                  <?php
                  include APPLICATION_PATH . '/application/modules/Seaocore/views/scripts/_commentBody.tpl';
                  ?>
                </span>
                <ul class="comments_date">
                  <li class="comments_timestamp">
                    <?php echo $this->timestamp($comment->creation_date); ?>
                  </li>
                  <?php
                  if( $canComment ):
                    $isLiked = $comment->likes()->isLike($this->viewer());
                    ?>
                    <li class="comments_like"> 
                      &#183;
                      <?php if( !$isLiked ): ?>
                        <a href="javascript:void(0)" onclick="en4.advancedactivity.like(this,<?php echo sprintf("'%d', %d", $action->getIdentity(), $comment->getIdentity()) ?>);" action-title="<?php echo $this->translate('unlike') ?>">
                          <?php echo $this->translate('like') ?>
                        </a>
                      <?php else: ?>
                        <a href="javascript:void(0)" onclick="en4.advancedactivity.unlike(this,<?php echo sprintf("'%d', %d", $action->getIdentity(), $comment->getIdentity()) ?>);" action-title="<?php echo $this->translate('like') ?>">
                          <?php echo $this->translate('unlike') ?>
                        </a>
                      <?php endif ?>
                    </li>
                  <?php endif ?>
                  <?php if( $comment->likes()->getLikeCount() > 0 ): ?>
                    <li class="comments_likes_total"> 
                      &#183;
                      <a href="javascript:void(0);" id="comments_comment_likes_<?php echo $comment->comment_id ?>" class="comments_comment_likes" title="<?php echo $this->translate('Loading...') ?>">
                        <?php echo $this->translate(array('%s likes this', '%s like this', $comment->likes()->getLikeCount()), $this->locale()->toNumber($comment->likes()->getLikeCount())) ?>
                      </a>
                    </li>
                  <?php endif ?>
                </ul>
              </div>
            </li>
          <?php endforeach; ?>
          <?php if( $canComment ): ?>
            <li id='feed-comment-form-open-li_<?php echo $actionBaseId ?>' onclick='<?php echo 'document.getElementById("' . $this->commentForm->getAttrib('id') . '").style.display = "";
document.getElementById("' . $this->commentForm->submit->getAttrib('id') . '").style.display = "' . (($this->isMobile || !$this->commentShowBottomPost || Engine_Api::_()->getApi('settings', 'core')->core_spam_comment) ? "block" : "none") . '";
document.getElementById("feed-comment-form-open-li_' . $actionBaseId . '").style.display = "none";
  document.getElementById("' . $this->commentForm->body->getAttrib('id') . '").focus();' ?>' <?php if( !$this->commentShowBottomPost || Engine_Api::_()->getApi('settings', 'core')->core_spam_comment ): ?> style="display:none;"<?php endif; ?> >                  <div></div>
				<div class="comment_form_user_photo">
					<?php echo $this->itemPhoto($this->viewer(), 'thumb.icon') ?>
				</div>
           	<div class="seaocore_comment_box seaocore_txt_light"><?php echo $this->translate('Post a comment...') ?></div>
            </li>
            <?php endif; ?>
          <?php endif; ?>
        <?php endif; ?>
    </ul>
    <?php
    if( $canComment ) {
      echo $this->commentForm->render();
    }
    ?>
  </div>
<?php endif; ?>
    


