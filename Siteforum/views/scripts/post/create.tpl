<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: create.tpl 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<script type="text/javascript">
    function showUploader()
    {
        $('photo').style.display = 'block';
        $('photo-label').style.display = 'none';
    }
</script>
<div class="mbot5">
    <?php echo $this->htmlLink(array('route' => 'siteforum_general'), $this->translate("Forums")); ?>
    &#187; <?php echo $this->htmlLink(array('route' => 'siteforum_forum', 'forum_id' => $this->siteforum->getIdentity()), $this->siteforum->getTitle()); ?>
    &#187; <?php echo $this->htmlLink(array('route' => 'siteforum_topic', 'topic_id' => $this->topic->getIdentity()), $this->topic->getTitle()); ?>
    &#187 <?php echo $this->translate('Post Reply'); ?>
</div>
<?php echo $this->form->render($this) ?>
