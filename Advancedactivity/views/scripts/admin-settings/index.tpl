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

<script type="text/javascript">

    window.addEvent('domready', function () {
        showiconOptions('<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.tabtype', 3)
?>');
        switchContentField('<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.scroll.autoload', 1)
?>', 'advancedactivity_maxautoload');
    });
    function switchContentField(value, id) {
        if ($(id + '-wrapper')) {
            if (value == 0) {
                $(id + '-wrapper').style.display = 'none';
            } else {
                $(id + '-wrapper').style.display = 'block';
            }
        }
    }
    function showiconOptions(option) {
        if ($('advancedactivity_tabtype-wrapper')) {
            if (option == 2) {
                $('advancedactivity_icon-wrapper').style.display = 'none';
                $('logo_photo_preview-wrapper').style.display = 'none';
                $('advancedactivity_icon1-wrapper').style.display = 'none';
                $('logo_photo_preview1-wrapper').style.display = 'none';


            } else {
                $('advancedactivity_icon-wrapper').style.display = 'block';
                $('logo_photo_preview-wrapper').style.display = 'block';
                $('advancedactivity_icon1-wrapper').style.display = 'block';
                $('logo_photo_preview1-wrapper').style.display = 'block';
            }
        }
    }


    function updateTextFields(option) {
        if ($('logo_photo_preview')) {
            $('logo_photo_preview').value = option;
        }
        if ($('logo_photo_preview-element')) {
            $('logo_photo_preview-element').innerHTML = "<img src='" + option + "' width='20' height='20' >";
        }
    }


    function updateTextFields1(option1) {
        if ($('advancedactivity_icon1-wrapper')) {
            $('advancedactivity_icon1-wrapper').value = option1;
        }
        if ($('logo_photo_preview1-element')) {
            $('logo_photo_preview1-element').innerHTML = "<img src='" + option1 + "' width='16' height='16' >";
        }
    }

    window.addEvent('domready', function () {
        updateTextFields1('<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting(
        'advancedactivity.icon1');
?>');

    });


// function updateTextFields2(option2) {
//   if($('advancedactivity_icon2-wrapper')) {
//     $('advancedactivity_icon2-wrapper').value = option2;
//   }
//   $('logo_photo_preview2-element').innerHTML = "<img src='" + option2 + "' width='16' height='16' >" ;
// }
// 
// window.addEvent('domready', function() {
//   updateTextFields2('<?php //echo Engine_Api::_()->getApi( 'settings' , 'core' )->getSetting(
//'advancedactivity.icon2' ) ; 
?>');
// 
// });
</script>

<h2>
<?php echo $this->translate("ADVANCED_ACTIVITY_PLUGIN_NAME") . " " . $this->translate("Plugin") ?>
</h2>

    <?php if (count($this->navigation)): ?>
    <div class='seaocore_admin_tabs'>
        <?php
        // Render the menu
        //->setUlClass()
        echo $this->navigation()->menu()->setContainer($this->navigation)->render()
        ?>
    </div>
<?php endif; ?>
<?php 
 $this->navigationAAF = $navigationAAF = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main_settings', array(), 'advancedactivity_admin_main_settings_global');
   
 ?> 

<?php if (count($this->navigationAAF)): ?>
  <div class='seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigationAAF)->render() ?>
  </div>
<?php endif; ?>
<?php

if (!empty($this->isModsSupport)):
    foreach ($this->isModsSupport as $modName) {
        echo $this->translate('<div class="tip"><span>Note: Your website does not have the latest version of "%s". Please upgrade "%s" on your website to the latest version available in your SocialEngineAddOns Client Area to enable its integration with "Advanced Activity Feeds / Wall Plugin".</span></div>', ucfirst($modName), ucfirst($modName));
    }
endif;
?>
<?php
$settings_fb = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.facebook');
$settings_twitter = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.twitter');
$linkedin_apikey = Engine_Api::_()->getApi('settings', 'core')->getSetting('linkedin.apikey');
$linkedin_secret = Engine_Api::_()->getApi('settings', 'core')->getSetting('linkedin.secretkey');
$instagram_apikey = Engine_Api::_()->getApi('settings', 'core')->getSetting('instagram.apikey');
$instagram_secret = Engine_Api::_()->getApi('settings', 'core')->getSetting('instagram.secretkey');
if ((empty($settings_fb['secret']) ||
        empty($settings_fb['appid']))):
    ?>   
    <div class="seaocore_tip">
        <span>
            <?php
            if ((empty($settings_fb['secret']) &&
                    empty($settings_fb['appid'])))
                echo $this->translate('The Facebook Application details (App ID or App Secret) are not entered by you. Please click <a href="%s/admin/user/settings/facebook" target= "_blank" >here</a> to enter them.', Zend_Controller_Front::getInstance()->getBaseUrl());
            else
                echo $this->translate('The Facebook Application details (App ID or App Secret) entered by you are incorrect. Please click <a href="%s/admin/user/settings/facebook" target= "_blank" >here</a> to enter them correctly.', Zend_Controller_Front::getInstance()->getBaseUrl());
            ?>

        </span>

    </div>
<?php endif; ?>

<?php if ((empty($settings_twitter['secret']) ||
        empty($settings_twitter['key']))) :
    ?>   
    <div class="seaocore_tip">
        <span>
            <?php
            if ((empty($settings_twitter['secret']) &&
                    empty($settings_twitter['key'])))
                echo $this->translate('The Twitter Application details (App ID or App Secret) are not entered by you. Please click <a href="%s/admin/user/settings/twitter" target= "_blank" >here</a> to enter them.', Zend_Controller_Front::getInstance()->getBaseUrl());
            else
                echo $this->translate('The Twitter Application details (App ID or App Secret) entered by you are incorrect. Please click <a href="%s/admin/user/settings/twitter" target= "_blank" >here</a> to enter them correctly.', Zend_Controller_Front::getInstance()->getBaseUrl());
            ?>

        </span>

    </div>
<?php endif; ?>

<?php if ((empty($linkedin_apikey) ||
        empty($linkedin_secret)) && Engine_Api::_()->getApi('settings', 'core')->getSetting('linkedin.enable', 0)) :
    ?>   
    <div class="seaocore_tip">
        <span>
    <?php echo $this->translate('The Linkedin Application details (App Key or Secret Key) entered by you are incorrect. Please enter them correctly here.'); ?>

        </span>

    </div>
<?php endif; ?>

        <?php if ( FALSE && (empty($instagram_apikey) ||
                empty($instagram_secret)) && Engine_Api::_()->getApi('settings', 'core')->getSetting('instagram.enable', 0)) :
            ?>  
    <div class="seaocore_tip">
        <span>
    <?php echo $this->translate('The Instagram Application details (App Key or Secret Key) entered by you are incorrect. Please enter them correctly here.'); ?>
        </span>
    </div>
<?php endif; ?>

<?php if (!function_exists('mb_strlen')): ?>
    <?php echo '<div class="seaocore_tip"><span>The PHP function "mb_strlen()", used by Twitter API library is disabled in the PHP installation on your server. Please contact your hosting company to enable this.<br />
You can also follow the instructions below to enable this yourself:<br />

"Search for: "extension=php_mbstring.dll" in the php.ini PHP configuration file of your server, and uncomment its line by removing the "#" from start. Then restart Apache."<br />

The PHP function "mb_strlen()" must be enabled on your server for Twitter integration to work.</span></div>'; ?>
<?php endif; ?>

<div class="seaocore_settings_form">
    <div class='settings'>
<?php echo $this->form->render($this); ?>
    </div>
</div>	