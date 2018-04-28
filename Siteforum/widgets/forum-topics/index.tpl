<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?><?php
$this->headLink()->appendStylesheet($this->layout()->staticBaseUrl
        . 'application/modules/Siteforum/externals/styles/style_siteforum.css');
?>

<div class="siteforum_header">
    <?php if ($this->canPost && !empty($this->siteforum)): ?>
        <div class="siteforum_header_options">
            <?php
            echo $this->htmlLink($this->siteforum->getHref(array(
                        'action' => 'topic-create',
                    )), $this->translate('Post New Topic'), array(
                'class' => 'siteforum_post_counts'
            ))
            ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($this->siteforum) && !empty($this->moderatorCount)): ?>
        <div class="siteforum_header_moderators fright">
            <?php echo $this->translate(array('Moderator:', 'Moderators:', $this->moderatorCount),$this->locale()->toNumber($this->moderatorCount));?>
            <?php echo $this->fluentList($this->moderators) ?>
        </div>
    <?php endif; ?>
    <?php if (count($this->paginator) > 0): ?>
        <div class="siteforum_header_pages">
            <?php echo $this->paginationControl($this->paginator); ?>
        </div>
    <?php endif; ?>
</div>

<?php if (count($this->paginator) > 0): ?>
    <ul class="siteforum_topics">
        <?php
        foreach ($this->paginator as $i => $topic):
            $last_post = $topic->getLastCreatedPost();
            if ($last_post) {
                $last_user = $this->user($last_post->user_id);
            } else {
                $last_user = $this->user($topic->user_id);
            }
            ?>
            <li class="siteforum_nth_<?php echo $i % 2 ?>">
                <div class="fleft">
                    <div class="siteforum_topics_icon">
                        <?php echo $this->htmlLink($topic->getOwner()->getHref(), $this->itemPhoto($topic->getOwner(), 'thumb.icon')); ?>

                        <?php if ($this->onlineIcon && Engine_Api::_()->siteforum()->isOnline($topic->getOwner()->getIdentity())) { ?>   
                            <span class="seao_online_icon fright seaocore_txt_light" >
                                <i title=<?php echo $this->translate("Online");?>></i>
                            </span> 
                        <?php } ?>
                    </div>

                    <div class="siteforum_topics_info">
                        <div class="siteforum_topics_title">
                            <h3<?php if ($topic->closed): ?> class="closed"<?php endif; ?><?php if ($topic->sticky): ?> class="sticky"<?php endif; ?>>
                                <?php echo $this->htmlLink($topic->getHref(), $topic->getTitle()); ?>
                            </h3>
                            <?php //echo $this->pageLinks($topic, $this->itemCountPerPage, null, 'siteforum_pagelinks') ?>
                        </div>
                        <div> <?php echo $this->viewMore(strip_tags($topic->getDescription()), 180) ?></div>
                        <div class="siteforum_topics_lastpost">
                            <?php
                            if ($last_post):
                                list($openTag, $closeTag) = explode('-----', $this->htmlLink($last_post->getHref(array('slug' => $topic->getSlug())), '-----'));
                                ?>
                                <?php //echo $this->htmlLink($last_post->getHref(), $this->itemPhoto($last_user, 'thumb.icon'))  ?>
                                <span class="siteforum_topics_lastpost_info">
                                    <?php
                                    echo $this->translate(
                                            '%1$sLast post%2$s by %3$s', $openTag, $closeTag, $this->htmlLink($last_user->getHref(), $last_user->getTitle())
                                    )
                                    ?>
                                    <?php echo $this->translate(' -') ?>
                                    <?php echo $this->timestamp($topic->modified_date, array('class' => 'siteforum_topics_lastpost_date')) ?>
                                </span>
                            <?php endif; ?>
                            <div class="seaocore_txt_light fright">
                                <?php if (!empty($this->statistics) && in_array('viewCount', $this->statistics)) : ?> 
                                    <span  class="siteforum_view_counts">
                                        <?php echo $this->translate(array('%1$s %2$s view', '%1$s %2$s views', $topic->view_count), $this->locale()->toNumber($topic->view_count), '') ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($this->statistics) && in_array('likeCount', $this->statistics)) : ?> 
                                    <span  class="siteforum_like_counts">
                                        <?php echo $this->translate(array('%1$s %2$s like', '%1$s %2$s likes', $topic->like_count), $this->locale()->toNumber($topic->like_count), '') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="siteforum_topics_stats fright">
                        <?php if (!empty($this->statistics) && in_array('postCount', $this->statistics)) : ?> 
                            <div class="siteforum_icon_reply" title="<?php echo $this->translate(array('%1$s %2$s Reply', '%1$s %2$s Replies', $topic->post_count - 1), $this->locale()->toNumber($topic->post_count - 1), '') ?>">
                                <?php echo $topic->post_count - 1 ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($this->statistics) && in_array('ratings', $this->statistics) && Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.rating', 1)): ?>
                            <div class="rating mtop10" id="siteforum_rating">  
                                <div class="o_hidden txt_center" >
                                    <span title="<?php echo $this->translate('Overall Rating: %s', $topic->rating); ?>">

                                        <?php for ($x = 1; $x <= $topic->rating; $x++) { ?>
                                            <span class="seao_rating_star_generic rating_star_y" title="<?php echo $this->translate('Overall Rating: %s', $topic->rating); ?>"></span>
                                            <?php
                                        }
                                        $roundrating = round($topic->rating);
                                        if (($roundrating - $topic->rating) > 0) {
                                            ?>
                                            <span class="seao_rating_star_generic rating_star_half_y" title="<?php echo $this->translate('Overall Rating: %s', $topic->rating); ?>"></span>
                                            <?php
                                        }
                                        $roundrating++;
                                        for ($x = $roundrating; $x <= 5; $x++) {
                                            ?>
                                            <span class="seao_rating_star_generic seao_rating_star_disabled" title="<?php echo $this->translate('Overall Rating: %s', $topic->rating); ?>"></span>
                                        <?php } ?>
                                    </span>
                                </div> 
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <div class="tip">
        <span>
            <?php if (!empty($this->siteforum)): ?>
                <?php echo $this->translate('There are no topics in this forum yet.') ?>
            <?php else: ?>
                <?php echo $this->translate('No results found.') ?>
            <?php endif; ?>
        </span>
    </div>
<?php endif; ?>

<?php if (count($this->paginator) > 0): ?>
    <div class="siteforum_header_pages">
        <?php echo $this->paginationControl($this->paginator); ?>
    </div>
<?php endif; ?>

<script type="text/javascript">
    $$('.core_main_siteforum').getParent().addClass('active');
</script>
