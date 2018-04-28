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
?>
<script type="text/javascript">
    en4.core.runonce.add(function () {

<?php if (!$this->renderOne): ?>
            var anchor = $('siteforum_topics').getParent();
            $('siteforum_topics_previous').style.display = '<?php echo ( $this->paginator->getCurrentPageNumber() == 1 ? 'none' : '' ) ?>';
            $('siteforum_topics_next').style.display = '<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' ) ?>';

            $('siteforum_topics_previous').removeEvents('click').addEvent('click', function () {
                en4.core.request.send(new Request.HTML({
                    url: en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
                    data: {
                        format: 'html',
                        subject: en4.core.subject.guid,
                        page: <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() - 1) ?>
                    }
                }), {
                    'element': anchor
                })
            });

            $('siteforum_topics_next').removeEvents('click').addEvent('click', function () {
                en4.core.request.send(new Request.HTML({
                    url: en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
                    data: {
                        format: 'html',
                        subject: en4.core.subject.guid,
                        page: <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() + 1) ?>
                    }
                }), {
                    'element': anchor
                })
            });
<?php endif; ?>
    });
</script>

<ul class="siteforum_topics" id="siteforum_topics">
    <?php
    foreach ($this->paginator as $topic):
        $last_post = $topic->getLastCreatedPost();
        if ($last_post) {
            $last_user = $this->user($last_post->user_id);
        } else {
            $last_user = $this->user($topic->user_id);
        }
        ?>
        <li>
            <div class="siteforum_topics_icon">
                <?php echo $this->htmlLink($topic->getOwner()->getHref(), $this->itemPhoto($topic->getOwner(), 'thumb.icon')); ?>
            </div>

            <div class="siteforum_topics_info">
                <div class="siteforum_topics_title">
                    <h3<?php if ($topic->closed): ?> class="closed"<?php endif; ?><?php if ($topic->sticky): ?> class="sticky"<?php endif; ?>>
                        <?php echo $this->htmlLink($topic->getHref(), $topic->getTitle()); ?>
                    </h3>
                    <?php //echo $this->pageLinks($topic, $this->itemCountPerPage, null, 'siteforum_pagelinks') ?>
                </div>
                <?php if ($this->truncationDescription): ?>
                    <div> <?php echo $this->viewMore(strip_tags($topic->getDescription()), $this->truncationDescription) ?></div>
                <?php endif; ?>
                <div class="siteforum_topics_lastpost">
                    <?php //echo $this->htmlLink($last_post->getHref(), $this->itemPhoto($last_user, 'thumb.icon')) ?>
                    <span class="siteforum_topics_lastpost_info">
                        <?php
                        if ($last_post):
                            list($openTag, $closeTag) = explode('-----', $this->htmlLink($last_post->getHref(array('slug' => $topic->getSlug())), '-----'));
                            ?>
                            <?php
                            echo $this->translate(
                                    '%1$sLast post%2$s by %3$s', $openTag, $closeTag, $this->htmlLink($last_user->getHref(), $last_user->getTitle())
                            )
                            ?>
                            <?php echo $this->translate(' -') ?>
                            <?php echo $this->timestamp($topic->modified_date, array('class' => 'siteforum_topics_lastpost_date')) ?>
                        <?php endif; ?>
                    </span>
                    <div class="seaocore_txt_light fright">
                        <?php if (!empty($this->statistics) && in_array('viewCount', $this->statistics)): ?>
                            <span  class="siteforum_view_counts">
                                <?php echo $this->translate(array('%1$s %2$s view', '%1$s %2$s views', $topic->view_count), $this->locale()->toNumber($topic->view_count), '') ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($this->statistics) && in_array('likeCount', $this->statistics)): ?>
                            <span  class="siteforum_like_counts">
                                <?php echo $this->translate(array('%s like', '%s likes', $topic->like_count), $this->locale()->toNumber($topic->like_count), ''); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="siteforum_topics_stats fright">
                <?php if (!empty($this->statistics) && in_array('postCount', $this->statistics)): ?>
                    <div class="siteforum_icon_reply" title="<?php echo $this->translate(array('%1$s %2$s Reply', '%1$s %2$s Replies', $topic->post_count - 1), $this->locale()->toNumber($topic->post_count - 1), '') ?>">
                        <?php echo $topic->post_count - 1 ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($this->statistics) && in_array('ratings', $this->statistics) && Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.rating', 1)) : ?>
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


<div>
    <div id="siteforum_topics_previous" class="paginator_previous">
        <?php
        echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array(
            'onclick' => '',
            'class' => 'buttonlink icon_previous'
        ));
        ?>
    </div>
    <div id="siteforum_topics_next" class="paginator_next">
        <?php
        echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array(
            'onclick' => '',
            'class' => 'buttonlink_right icon_next'
        ));
        ?>
    </div>
</div>
