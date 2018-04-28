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

<ul>
    <?php
    foreach ($this->posts as $post):
        $user = $post->getOwner();
        $topic = $post->getParent();
        $siteforum = $topic->getParent();
        ?>
        <li>
            <?php if ($this->truncationDescription): ?>
                <div class='description'>
                    <?php echo $this->viewMore(strip_tags($post->getDescription()), $this->truncationDescription) ?>
                </div>
            <?php endif; ?>
            <div class='siteforum_post_info'>
                <span class='author'>
                    <?php echo $this->translate('By') ?>
                    <?php echo $this->htmlLink($user->getHref(), $user->getTitle()) . ',' ?>

                    <?php echo $this->translate('In') ?>
                    <?php echo $this->htmlLink($topic->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($topic->getTitle(), $this->truncationLastPost)) ?>

                </span>
                <div class="seaocore_txt_light">
                    <span class='siteforum_time_icon'>
                        <?php echo $this->timestamp($post->creation_date) ?>
                    </span>
                    <?php if (!empty($this->statistics) && in_array('likeCount', $this->statistics)): ?>
                        <span class="siteforum_like_counts" title="<?php echo $this->translate(array('%1$s %2$s Like', '%1$s %2$s Likes', $post->like_count), $this->locale()->toNumber($post->like_count), '') ?>"><?php echo $post->like_count ?> 
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($this->statistics) && in_array('thankCount', $this->statistics) && Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.thanks', 1)) : ?>
                        <span class="siteforum_thanks_icon" title="<?php echo $this->translate(array('%1$s %2$s Thank', '%1$s %2$s Thanks', $post->thanks_count), $this->locale()->toNumber($post->thanks_count), '') ?>"><?php echo $post->thanks_count ?> 
                        </span>
                    <?php endif; ?>
                </div>

            </div>

        </li>
    <?php endforeach; ?>
</ul>