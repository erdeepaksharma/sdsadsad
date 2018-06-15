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
    if ($this->checkSiteModeSM()) {
     include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/navigation_viewsSM.tpl'; 
   } else {
      include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/navigation_views.tpl'; 
 }?>