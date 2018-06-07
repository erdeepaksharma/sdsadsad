<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: faq.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>

<h2>
  <?php echo $this->translate("ADVANCED_ACTIVITY_PLUGIN_NAME") . " " . $this->translate("Plugin") ?>
</h2>

<?php if (count($this->navigation)): ?>
  <div class='seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
  </div>
<?php endif; ?>
<?php $active = 'advancedactivity_admin_main_activity_faq';
  include APPLICATION_PATH . '/application/modules/Advancedactivity/views/scripts/_adminFAQNav.tpl'; ?>


<?php include_once APPLICATION_PATH . '/application/modules/Advancedactivity/views/scripts/admin-settings/faq_help.tpl'; ?>
