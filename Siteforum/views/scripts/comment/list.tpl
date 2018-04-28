<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: list.tpl 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>

<?php
$this->headTranslate(array(
    'Are you sure you want to delete this?',
));
?>

<?php if (!$this->page): ?>
    <div class='comments' id="comments-<?php echo $this->subject()->getIdentity(); ?>">
    <?php endif; ?><?php if ($this->comments->getTotalItemCount() > 0): // COMMENTS ------- ?>
        <ul> 
            <?php if ($this->comments->getTotalItemCount() > 0): // COMMENTS ------- ?>

                <?php if ($this->page && $this->comments->getCurrentPageNumber() > 1): ?>
                    <li>
                        <div> </div>
                        <div class="comments_viewall">
                            <?php
                            echo $this->htmlLink('javascript:void(0);', $this->translate('View previous comments'), array(
                                'onclick' => 'en4.siteforum.comments.loadComments("' . $this->subject()->getType() . '", "' . $this->subject()->getIdentity() . '", "' . ($this->page - 1) . '")'
                            ))
                            ?>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if (!$this->page && $this->comments->getCurrentPageNumber() < $this->comments->count()): ?>
                    <li>
                        <div> </div>
                        <div class="comments_viewall">
                            <?php
                            echo $this->htmlLink('javascript:void(0);', $this->translate('View more comments'), array(
                                'onclick' => 'en4.siteforum.comments.loadComments("' . $this->subject()->getType() . '", "' . $this->subject()->getIdentity() . '", "' . ($this->comments->getCurrentPageNumber()) . '")'
                            ))
                            ?>
                        </div>
                    </li>
                <?php endif; ?>

                <?php
                // Iterate over the comments backwards (or forwards!)
                $comments = $this->comments->getIterator();
                if ($this->page):
                    $i = 0;
                    $l = count($comments) - 1;
                    $d = 1;
                    $e = $l + 1;
                else:
                    $i = count($comments) - 1;
                    $l = count($comments);
                    $d = -1;
                    $e = -1;
                endif;
                for (; $i != $e; $i += $d):
                    $comment = $comments[$i];
                    $poster = $this->item($comment->poster_type, $comment->poster_id);
                    $canDelete = ( $this->canDelete || $poster->isSelf($this->viewer()) );
                    $canEdit = ( $this->canEdit || $poster->isSelf($this->viewer()) );
                    ?>
                    <li id="comment-<?php echo $comment->comment_id ?>">
                        <div class="comments_author_photo">
                            <?php
                            echo $this->htmlLink($poster->getHref(), $this->itemPhoto($poster, 'thumb.icon', $poster->getTitle())
                            )
                            ?>
                        </div>
                        <div class="comments_info">
                            <span class='comments_author'>
                                <?php echo $this->htmlLink($poster->getHref(), $poster->getTitle()); ?>
                            </span>
                            <span class="comments_body" id="comments_body_<?php echo $comment->comment_id ?>">
                                <?php echo $this->viewMore($comment->body) ?></span>
                            <div id="comment_edit_<?php echo $comment->comment_id ?>" class="mtop5 comment_edit" style="display: none;">
                                <form method="post" action="" class="activity-comment-form" enctype="application/x-www-form-urlencoded" id="activity-comment-edit-form-<?php echo $comment->comment_id; ?>">
                                    <textarea rows = "1" id="activity-comment-edit-body-<?php echo $comment->comment_id; ?>" name="body"></textarea>
                                    <button type="submit" id="activity-comment-edit-submit-<?php echo $comment->comment_id; ?>" class="mtop5" name="submit" onclick="en4.siteforum.comments.attachEditComment($('activity-comment-edit-form-<?php echo $comment->comment_id; ?>'));
                                                        return false;"><?php echo $this->translate("Edit"); ?></button>
                                    <?php echo $this->translate('or'); ?> <a href="javascript: void(0);" onclick="$('comment_edit_<?php echo $comment->comment_id ?>').style.display = 'none';
                                                        $('comments_body_<?php echo $comment->comment_id ?>').style.display = 'inline-block';
                                                        $('activity-comment-edit-body-<?php echo $comment->comment_id ?>').innerHTML = '<?php echo $comment->body; ?>';
                                                        $('comments_date_<?php echo $comment->comment_id ?>').style.display = 'block';"><?php echo $this->translate('cancel'); ?></a>
                                    <input type="hidden" id="activity-comment-edit-id-<?php echo $comment->comment_id; ?>" value="<?php echo $comment->comment_id; ?>" name="comment_id">
                                    <input type="hidden" name="type" value="<?php echo $this->subject()->getType(); ?>">
                                    <input type="hidden" name="id" value="<?php echo $this->subject()->getIdentity(); ?>">
                                </form>
                            </div>

                            <div class="comments_date" id="comments_date_<?php echo $comment->comment_id; ?>">
                                <?php echo $this->timestamp($comment->creation_date); ?>
                                <?php if ($canEdit): ?>
                                    -
                                    <a href="javascript:void(0);" onclick="$('comment_edit_<?php echo $comment->comment_id ?>').style.display = 'block';
                                                            $('comments_body_<?php echo $comment->comment_id ?>').style.display = 'none';
                                                            $('activity-comment-edit-body-<?php echo $comment->comment_id ?>').innerHTML = '<?php echo $comment->body; ?>';
                                                            $('comments_date_<?php echo $comment->comment_id ?>').style.display = 'none';">
                                           <?php echo $this->translate('edit'); ?>
                                    </a>
                                <?php endif; ?>
                                <?php if ($canDelete): ?>
                                    -
                                    <a href="javascript:void(0);" onclick="en4.siteforum.comments.deleteComment('<?php echo $this->subject()->getType() ?>', '<?php echo $this->subject()->getIdentity() ?>', '<?php echo $comment->comment_id ?>')">
                                        <?php echo $this->translate('delete') ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                <?php endfor; ?>

                <?php if ($this->page && $this->comments->getCurrentPageNumber() < $this->comments->count()): ?>
                    <li>
                        <div> </div>
                        <div class="comments_viewall">
                            <?php
                            echo $this->htmlLink('javascript:void(0);', $this->translate('View later comments'), array(
                                'onclick' => 'en4.siteforum.comments.loadComments("' . $this->subject()->getType() . '", "' . $this->subject()->getIdentity() . '", "' . ($this->page + 1) . '")'
                            ))
                            ?>
                        </div>
                    </li>
                <?php endif; ?>
            <?php endif; ?>
        </ul><?php endif; ?>
    <script type="text/javascript">
        en4.core.runonce.add(function () {
            $($('comment-form-<?php echo $this->subject()->getIdentity(); ?>').body).autogrow();
            en4.siteforum.comments.attachCreateComment($('comment-form-<?php echo $this->subject()->getIdentity(); ?>'));
        });
    </script>
    <?php if (isset($this->form)) echo $this->form->setAttribs(array('id' => 'comment-form-' . $this->subject()->getIdentity(), 'style' => 'display:none;'))->render() ?>
    <?php if (!$this->page): ?>
    </div>
<?php endif; ?>
 