<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php
  $content_id = $this->identity;
  $contentTable = Engine_Api::_()->getDbTable('content', 'core');
  $contentTableName = $contentTable->info('name');
  $parent_content_id = $contentTable->select()
          ->from($contentTableName, 'parent_content_id')
          ->where('content_id = ?', $content_id)
          ->query()
          ->fetchColumn();
  
  $name = '';
  if($parent_content_id) {
    $name = $contentTable->select()
            ->from($contentTableName, 'name')
            ->where('content_id = ?', $parent_content_id)
            ->query()
            ->fetchColumn();
  }
?>

<div class="tip" id="sitereview_ads_plugin_<?php echo $this->identity; ?>">
  <span>
    <p>
      <?php if(!empty($name) && $name != 'left' && $name != 'right'): ?>
        <?php echo $this->translate("Note: You are the Super Admin of this site, that is why only you can view this message."); ?> <br/> 
      <?php endif; ?>
      <?php echo $this->message; ?>
    </p> 
    <p class="fright clr">
    <a href="javascript:void(0)" onclick="removeAdsWidget('<?php echo $this->identity; ?>')"><?php echo $this->translate("Dismiss");?>&raquo;</a>
    </p>
  </span>
</div>