<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitemobile
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2013-06-03 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php $navigation_common = Engine_Api::_()->getApi('menus', 'sitemobile')->getNavigation("sitereview_main_common"); ?>
<?php
if (count($this->navigation) > 0 ):
  foreach ($navigation_common->getPages() as $page):
    $page->set('order', $page->get('order') + 900);
  endforeach;
  foreach ($this->navigation->getPages() as $page):
    $navigation_common->addPage($page);
  endforeach;
endif;
$this->navigation = $navigation_common;
?>

<?php echo $this->render('application/modules/Sitemobile/widgets/sitemobile-navigation/index.tpl'); ?>