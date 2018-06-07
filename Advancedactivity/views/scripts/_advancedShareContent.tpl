<?php
/**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: index.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>

<?php $profileUrl = $this->viewer->getHref(); 
      $action = $this->onThisDay;
      $attachment = null;
      if($action->attachment_count > 0){
        $attachments = $action->getAttachments();
        $attachment = $attachments[0] ? $attachments[0]->item : '';
      }
?>
<div class="memories_discribtion">  <img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Advancedactivity/externals/images/se_memories.png"/> 
   <div>    <h2>Your Memories On <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('core_general_site_title', $this->translate('_SITE_TITLE')); ?></h2>
        <p class="about_memories">Hey ! <?php echo $this->viewer->getTitle(); ?> , we are care about you and your memories that you post, share or added here.
            We thought that You would like to look back on this post from 1 year ago.</p>
        <ul class="feed" id="aaf_on_this_day">
            <li class="activity-item">
                <?php echo $this->advancedActivity($this->onThisDay); ?>
            </li>
        </ul>
        <p class="privacy_type"><?php echo $this->translate("This will not be visible to anyone unless shared by you."); ?></p>
        <div class="onthisday_actionlinks">
            <span class="onthisday_action-seemore"> <a href="<?php echo $profileUrl ?>/onthisday/1"> <?php echo $this->translate('See more memories') ?></a> </span>
            <span class="onthisday_action-share"> <?php $attachment_type = $attachment ? $attachment->getType() : 'activity_action'; $attachment_id = $attachment ? $attachment->getIdentity() : $action->getIdentity(); echo $this->htmlLink(array('route' => 'default', 'module' => 'seaocore', 'controller' => 'activity', 'action' => 'share', 'type' => $attachment_type, 'id'=>$attachment_id,'action_id'=>$action->getIdentity(),'onthisday'=>1, 'format' => 'smoothbox'), $this->translate('Share'), array('class' => 'smoothbox', 'title' => 'Share')) ?></span>
        </div>
   </div>
</div>
<script type="text/javascript">
    en4.core.runonce.add(function(){
        if($('aaf_on_this_day').getElements('.comment-likes-activity-item'))
           $('aaf_on_this_day').getElements('.comment-likes-activity-item').destroy();
    });
</script>
