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
    foreach ($this->topics as $topic):
        $user = $topic->getOwner('user');
        $siteforum = $topic->getParent();
        ?>
        <li>
            <div class="fleft">
                <?php echo $this->htmlLink($topic->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($topic->getTitle(), $this->truncationTitle)) ?>

                <div class="seaocore_txt_light">
                    <?php if (!empty($this->statistics) && in_array('viewCount', $this->statistics)) : ?>
                        <span  class="siteforum_view_counts">       
                            <?php echo $this->translate(array('%s view', '%s views', $topic->view_count), $this->locale()->toNumber($topic->view_count)); ?>
                        </span>
                    <?php endif; ?>

                    <?php if (!empty($this->statistics) && in_array('likeCount', $this->statistics)) : ?> 
                        <span  class="siteforum_like_counts">
                            <?php echo $this->translate(array('%s like', '%s likes', $topic->like_count), $this->locale()->toNumber($topic->like_count)); ?>
                        </span>
                    <?php endif; ?>
                </div>  
            </div>

            <div class="fright">
                <?php if (!empty($this->statistics) && in_array('postCount', $this->statistics)): ?>
                <div class="siteforum_icon_reply" title=<?php echo $this->translate("Replies");?>>
                        <?php echo $topic->post_count; ?>
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