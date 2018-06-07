<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereaction
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _adminAAFNav.php 6590 2016-07-07 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<h2>
<?php echo $this->translate("ADVANCED_ACTIVITY_PLUGIN_NAME") . " " . $this->translate("Plugin");
 $this->navigationAAF = $navigationAAF = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), $active);
    ?> 
</h2>

   

<?php if (count($this->navigationAAF)): ?>
  <div class='seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigationAAF)->render() ?>
  </div>
<?php endif; ?>

 <style type="text/css">

  .seaocore_admin_tabs > ul > li:hover ul
  {
    display: block;
  }
  .seaocore_admin_tabs ul ul {
    display: none;
    position: absolute;
    margin: 0;
    padding: .25em 0;
    min-width: 170px;
    background: #444;
    border-bottom-left-radius: 3px;
    border-bottom-right-radius: 3px;
    border-top-right-radius: 3px;
    z-index: 9999999999;
    margin-top: 26px;
  }

  .seaocore_admin_tabs ul ul li{
    float: none !important;
  }
  .seaocore_admin_tabs ul ul li:hover
  {
    background-color: #5BA1CD;
  }
  .seaocore_admin_tabs ul ul li a
  {
    letter-spacing: 0px;
    text-decoration: none;
    font-size: 8pt;
    display: block;
    padding: .5em 12px !important;
    outline: none;
    color: #aaa !important;
    background-color: #444 !important;
  }
  .seaocore_admin_tabs ul ul li a:hover
  {
    color: #fff !important;
    background: #555 !important;
  }
</style>