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
?> <?php $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Siteforum/externals/styles/style_siteforum.css'); ?>

<script type="text/javascript">
    en4.core.runonce.add(function () {

<?php if (!$this->renderOne): ?>
            var anchor = $('siteforum_topic_posts').getParent();
            $('siteforum_topic_posts_previous').style.display = '<?php echo ( $this->paginator->getCurrentPageNumber() == 1 ? 'none' : '' ) ?>';
            $('siteforum_topic_posts_next').style.display = '<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' ) ?>';

            $('siteforum_topic_posts_previous').removeEvents('click').addEvent('click', function () {
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

            $('siteforum_topic_posts_next').removeEvents('click').addEvent('click', function () {
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

<?php $user = $this->subject; ?>

<ul id="siteforum_topic_posts">
    <?php
    foreach ($this->paginator as $post):
        if (!isset($signature))
            $signature = $post->getSignature();
        $topic = $post->getParent();
        $siteforum = $topic->getParent();
        ?>
        <li>
            <?php if ($this->truncationDescription): ?>
                <div> <?php echo $this->viewMore(strip_tags($post->getDescription()), $this->truncationDescription) ?></div>
            <?php endif; ?>

            <div class='siteforum_post_info'>

                <div class="seaocore_txt_light"> 
                    <span>
                        <?php echo $this->translate('in %1$s', $topic->__toString()) ?>
                        <?php //echo $this->translate('in the forum %1$s', $siteforum->__toString()) ?></span>
                    <span class='siteforum_time_icon'>
                        <?php echo $this->locale()->toDateTime(strtotime($post->creation_date)); ?>
                    </span>
                    <?php if (!empty($this->statistics) && in_array('likeCount', $this->statistics)): ?>
                        <span class="siteforum_like_counts" title="<?php echo $this->translate(array('%1$s %2$s Like', '%1$s %2$s Likes', $post->like_count), $this->locale()->toNumber($post->like_count), '') ?>"><?php echo $post->like_count ?> </span>
                    <?php endif; ?>
                    <?php if (!empty($this->statistics) && in_array('thankCount', $this->statistics) && Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.thanks', 1)) : ?>
                        <span class="siteforum_thanks_icon" title="<?php echo $this->translate(array('%1$s %2$s Thank', '%1$s %2$s Thanks', $post->thanks_count), $this->locale()->toNumber($post->thanks_count), '') ?>"><?php echo $post->thanks_count ?> 
                        </span>
                    <?php endif; ?>
                </div>

                <div>
                    <?php if ($post->edit_id): ?>
                        <i>
                            <?php echo $this->translate('This post was edited by %1$s at %2$s', $this->user($post->edit_id)->__toString(), $this->locale()->toDateTime(strtotime($post->creation_date))); ?>
                        </i>
                    <?php endif; ?>
                </div>
                <div>

                    <?php if ($post->file_id): ?>
                        <div class="siteforum_topic_posts_info_photo">
                            <?php echo $this->itemPhoto($post, null, '', array('class' => 'siteforum_post_photo')); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </li>
    <?php endforeach; ?>
</ul>

<div>
    <div id="siteforum_topic_posts_previous" class="paginator_previous">
        <?php
        echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array(
            'onclick' => '',
            'class' => 'buttonlink icon_previous'
        ));
        ?>
    </div>
    <div id="siteforum_topic_posts_next" class="paginator_next">
        <?php
        echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array(
            'onclick' => '',
            'class' => 'buttonlink_right icon_next'
        ));
        ?>
    </div>
</div>
