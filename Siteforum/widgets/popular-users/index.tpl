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
?><ul>
    <?php foreach ($this->users as $user): ?>
        <li>
            <div class='info'>
                <div class="siteforum_user_photo fleft prelative"><?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon'), array('class' => 'thumb')) ?>
                    <?php if ($this->onlineIcon): ?>
                        <span class="seao_online_icon fright seaocore_txt_light" >
                            <?php if ($this->show_online_user || Engine_Api::_()->siteforum()->isOnline($user->getIdentity())) { ?> 
                            <i title=<?php echo $this->translate("Online");?>></i>
                            <?php } ?>
                        </span> 
                    <?php endif; ?>

                </div>
                <div class='author'>
                    <?php echo $this->htmlLink($user->getHref(), $user->getTitle()) ?>
                </div>
                <?php if ($this->topic): ?>
                    <span class="siteforum_topic_counts"><?php echo $this->translate("%s topics", $user->total_result); ?> </span> 
                <?php elseif ($this->post): ?>
                    <span class="siteforum_post_counts"><?php echo $this->translate("%s posts", $user->total_result); ?></span>
                <?php elseif ($this->reputation && Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.reputation', 1)): ?>
                    <span class="siteforum_reputation_counts"><?php echo $this->translate("%s reputations", $user->total_result); ?></span>
                <?php elseif ($this->thanks && Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.thanks', 1)): ?>
                    <span class="siteforum_thanks_counts"><?php echo $this->translate("%s thanks", $user->total_result); ?></span>
                <?php endif; ?>
            </div>
        </li>
    <?php endforeach; ?>
</ul>