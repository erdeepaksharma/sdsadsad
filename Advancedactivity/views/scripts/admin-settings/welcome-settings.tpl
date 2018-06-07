<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: welcome-settings.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>

<?php if( empty($this->isWelcomePageCorrect) ): ?>
<div class="tip">
  <span><?php echo $this->translate("You will be able to vertically adjust the position of widgets on Welcome Tab page from the Layout Editor."); ?></span>
</div>
<?php endif; ?>

<h2>
  <?php echo $this->translate("ADVANCED_ACTIVITY_PLUGIN_NAME") . " " . $this->translate("Plugin") ?>
</h2>

<?php if (count($this->navigation)): ?>
  <div class='seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
  </div>
<?php endif; ?>

<?php
  $pageURL = $this->url(array('module' => 'core','controller' => 'content', 'action' => 'index', 'page' => $this->pageId), 'admin_default');
  $this->form->setTitle('Welcome Tab Settings');
  $this->form->setDescription($this->translate('Below, you can configure the settings for the various blocks of Welcome Tab. Note that the Welcome Tab content can be placed anywhere on your website from the Layout Editor. It is a part of the Advanced Activity Feeds widget, and you can choose to show this tab from the Edit Settings of the widget.<br />You can use this feature to show welcome / introductory content to users. This tab will display welcome content to users and enable them to quickly familiarize and grow their network in your community. There are some pre-built widgets for this tab which easily and quickly get users going on your website, and you can also create your custom content. This feature enables users to quickly get familiar with your website, thus converting signups to frequent logins.<br />With below intelligent settings, you can configure Welcome Tab content such that different users are shown different content depending on their number of friends, and days since signup on you site.<br />You will be able to vertically adjust the position of widgets on Welcome Tab page from the <a href="%s">Layout Editor</a>.<br />', $pageURL));
  $this->form->getDecorator('Description')->setOption('escape', false);
?>

<?php if (count($this->sub_navigation)): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->sub_navigation)->render() ?>
  </div>
<?php endif; ?>

<div class="seaocore_settings_form">
	<div class='settings'>
	  <?php
	    echo $this->form->render($this);
	  ?>
	</div>
</div>	
