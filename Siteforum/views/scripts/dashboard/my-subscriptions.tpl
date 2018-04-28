<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: my-subscriptions.tpl 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php include_once APPLICATION_PATH . '/application/modules/Siteforum/views/scripts/_DashboardNavigation.tpl'; ?>
<div class="siteforum_dashboard_content">

    <?php if ($this->paginator->getPages()->pageCount > 1): ?>
        <div class="siteforum_header">
            <div class="siteforum_header_pages">
                <?php echo $this->paginationControl($this->paginator); ?>
            </div>
        </div>
    <?php endif; ?>

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
                    <div class="siteforum_topics_icon">
                        <?php echo $this->htmlLink($topic->getOwner()->getHref(), $this->itemPhoto($topic->getOwner(), 'thumb.icon')); ?>

                        <?php if (Engine_Api::_()->siteforum()->isOnline($topic->getOwner()->getIdentity())) { ?>   
                            <span class="seao_online_icon fright seaocore_txt_light" >
                                <i title="Online"></i>
                            </span> 
                        <?php } ?>
                    </div>

                    <div class="siteforum_topics_info">
                        <div class="siteforum_topics_title">
                            <h3<?php if ($topic->closed): ?> class="closed"<?php endif; ?><?php if ($topic->sticky): ?> class="sticky"<?php endif; ?>>
                                <?php echo $this->htmlLink($topic->getHref(), $topic->getTitle()); ?>
                            </h3>
                            <?php //echo $this->pageLinks($topic, 25, null, 'siteforum_pagelinks') ?>
                        </div>
                        <div> <?php echo $this->viewMore(strip_tags($topic->getDescription()), 180) ?></div>
                        <div class="siteforum_topics_lastpost">
                            <?php
                            if ($last_post):
                                list($openTag, $closeTag) = explode('-----', $this->htmlLink($last_post->getHref(array('slug' => $topic->getSlug())), '-----'));
                                ?>
                                <?php //echo $this->htmlLink($last_post->getHref(), $this->itemPhoto($last_user, 'thumb.icon')) ?>
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
                                <span  class="siteforum_view_counts">
                                    <?php echo $this->translate(array('%1$s %2$s view', '%1$s %2$s views', $topic->view_count), $this->locale()->toNumber($topic->view_count), '') ?>
                                </span>
                                <span  class="siteforum_like_counts">
                                    <?php echo $this->translate(array('%1$s %2$s like', '%1$s %2$s likes', $topic->like_count), $this->locale()->toNumber($topic->like_count), '') ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="siteforum_topics_stats fright">
                        <div class="siteforum_icon_reply" title="<?php echo $this->translate(array('%1$s %2$s Reply', '%1$s %2$s Replies', $topic->post_count - 1), $this->locale()->toNumber($topic->post_count - 1), '') ?>">
                            <?php echo $topic->post_count - 1; ?>
                        </div>
                        <?php if (Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.rating', 1)): ?>
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
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="tip">
            <span>
                <?php echo $this->translate('You haven\'t subscribed any topic yet.') ?>
            </span>
        </div>
    <?php endif; ?>
    <div class="siteforum_header_pages">
        <?php echo $this->paginationControl($this->paginator); ?>
    </div>

</div>
</div>
<script type="text/javascript">
    $$('.core_main_siteforum').getParent().addClass('active');
</script>
