<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _shareTopicButtons.tpl 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?><?php
$urlencode = urlencode(((!empty($_ENV["HTTPS"]) && 'on' == strtolower($_ENV["HTTPS"])) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $this->subject->getHref());
$object_link = (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->subject->getHref();

$urlShare = $this->url(array('module' => 'seaocore', 'controller' => 'activity', 'action' => 'share', 'type' => $this->subject->getType(), 'id' => $this->subject->getIdentity(), 'not_parent_refresh' => 1, 'format' => 'smoothbox'), 'default', true);
?>

<div>
    <?php
    echo '<div class="siteforum_grid_footer"><div style="position:relative;" ><a href="javascript:void(0);"  class="siteforum_share_links_toggle"><span class="seao_icon_share"></span>' . $this->translate('Share') . '</a>'
    . '<div class="siteforum_share_links" style="display:none;"><ul class="dropdown-menu social-share tall-event-box-menu">';
    ?>

    <?php if (!empty($this->shareOption) && in_array("facebook", $this->shareOption)): ?>
        <?php echo '<li class="share-btn"><a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=' . $urlencode . '"><span class="seao_icon_facebook"></span>' . $this->translate('Share on Facebook') . '</a></li>'; ?>
    <?php endif; ?>
    <?php if (!empty($this->shareOption) && in_array("twitter", $this->shareOption)): ?>
        <?php echo '<li class="share-btn"><a target="_blank" href="http://twitter.com/share?text=' . $this->subject->getTitle() . '&url=' . $urlencode . '"><span class="seao_icon_twitter"></span>' . $this->translate('Share on Twitter') . '</a></li>'; ?>
    <?php endif; ?>
    <?php if (!empty($this->shareOption) && in_array("linkedin", $this->shareOption)): ?>
        <?php echo '<li class="share-btn"><a target="_blank" href="https://www.linkedin.com/shareArticle?mini=true&url=' . $object_link . '"><span class="seao_icon_linkedin"></span>' . $this->translate('Share on LinkedIn') . '</a></li>'; ?>
    <?php endif; ?>
    <?php if (!empty($this->shareOption) && in_array("google", $this->shareOption)): ?>
        <?php echo '<li class="share-btn"><a target="_blank" href="https://plus.google.com/share?url=' . $urlencode . '&t=' . $this->subject->getTitle() . '"><span class="seao_icon_google_plus"></span>' . $this->translate('Share on Google+') . '</a></li>'; ?>
    <?php endif; ?>
    <?php if (!empty($this->shareOption) && in_array("community", $this->shareOption) && $this->viewer_id): ?>
        <?php echo '<li class="share-btn"><a onclick="javascript:Smoothbox.open(\'' . $urlShare . '\');" href="javascript:void(0);"><span class="smoothbox seao_icon_sharelink"></span>' . $this->translate('Share on %s', Engine_Api::_()->getApi('settings', 'core')->getSetting('core_general_site_title', $this->translate('_SITE_TITLE'))) . '</a></li>'; ?>
    <?php endif; ?>

    <?php echo '</ul></div></div></div>'; ?>
</div>
