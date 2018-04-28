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
<?php
$this->headLink()->appendStylesheet($this->layout()->staticBaseUrl
        . 'application/modules/Siteforum/externals/styles/style_siteforum.css');
?>


<div class="o_hidden">		
    <?php if (!$this->forum_id && !empty($this->statistics) && in_array('totalForums', $this->statistics)): ?>
        <div class="fleft txt_center">
            <i class="icon_forum seaocore_txt_light"></i>
            <span class="dblock seaocore_txt_light"><?php echo $this->translate('Forums'); ?></span>
            <span class="dblock bold"><?php echo $this->forumStatistics['forum']['forum_count']; ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($this->statistics) && in_array('topicCount', $this->statistics)): ?>
        <div class="fleft txt_center">
            <i class="icon_topic seaocore_txt_light"></i>
            <span class="dblock seaocore_txt_light"><?php echo $this->translate('Topics'); ?></span>
            <span class="dblock bold"><?php echo $this->forumStatistics['forum']['topic_count']; ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($this->statistics) && in_array('postCount', $this->statistics)): ?>
        <div class="fleft txt_center">
            <i class="icon_post seaocore_txt_light"></i>
            <span class="dblock seaocore_txt_light"><?php echo $this->translate('Posts'); ?></span>
            <span class="dblock bold"><?php echo $this->forumStatistics['forum']['post_count']; ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($this->statistics) && in_array('totalUsers', $this->statistics)): ?>
        <div class="fleft txt_center">
            <i class="icon_user seaocore_txt_light"></i>
            <span class="dblock seaocore_txt_light"><?php echo $this->translate('Total Users'); ?></span>
            <span class="dblock bold"><?php echo $this->forumStatistics['total_user']['user_count']; ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($this->statistics) && in_array('activeUsers', $this->statistics)): ?>
        <div class="fleft txt_center">
            <i class="icon_active_user seaocore_txt_light"></i>
            <span class="dblock seaocore_txt_light"><?php echo $this->translate('Active Users'); ?></span>
            <span class="dblock bold"><?php echo $this->forumStatistics['active_user']; ?></span>
        </div>
    <?php endif; ?>


</div>