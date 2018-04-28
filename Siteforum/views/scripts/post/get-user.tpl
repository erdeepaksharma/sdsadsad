<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: get-user.tpl 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<script type="text/javascript">
    var siteforumMemberPage = Number('<?php echo $this->paginator->getCurrentPageNumber() ?>');
</script>

<?php if ($this->totalMembers > 0): ?>
    <?php if ($this->paginator->getCurrentPageNumber() > 1): ?>
        <div class="seaocore_members_popup_paging">
            <div id="user_like_members_previous" class="paginator_previous">
                <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array('onclick' => 'paginateEventMembers(siteforumMemberPage - 1)', 'style' => '')); ?>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (count($this->paginator) > 0) { ?>
    <?php foreach ($this->paginator as $member): ?>
        <div class="item_member_list siteforum_popup_member_list">
            <div class="item_member_details">
                <div class="item_member_name" id="siteforum_profile_list_title_<?php echo $member->user_id ?>">
                    <?php echo $this->htmlLink($member->getHref(), $member->getTitle(), array('class' => 'item_photo seao_common_add_tooltip_link', 'title' => $member->getTitle(), 'target' => '_parent', 'rel' => 'user' . ' ' . $member->user_id)); ?>

                </div> 
            </div>

            <div class="item_member_thumb">
                <?php echo $this->htmlLink($member->getHref(), $this->itemPhoto($member, 'thumb.icon'), array()) ?>            
            </div>
        </div>
    <?php endforeach; ?>
<?php } else { ?>
    <div class='tip m10'>
        <span>
            <?php echo $this->translate('No members were found.'); ?>
        </span>
    </div>
<?php } ?>

<?php if ($this->totalMembers > 1): ?>
    <?php if ($this->paginator->getCurrentPageNumber() < $this->paginator->count()): ?>
        <div class="seaocore_members_popup_paging">   
            <div id="user_siteforum_members_next" class="paginator_next">
                <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array('onclick' => 'paginateEventMembers(siteforumMemberPage + 1)')); ?>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
