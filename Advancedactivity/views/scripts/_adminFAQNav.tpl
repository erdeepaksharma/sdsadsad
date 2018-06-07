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
<?php 
 $this->navigationAAF = $navigationAAF = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main_faq', array(), $active);
    ?> 

<?php if (count($this->navigationAAF)): ?>
  <div class='seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigationAAF)->render() ?>
  </div>
<?php endif; ?>
 