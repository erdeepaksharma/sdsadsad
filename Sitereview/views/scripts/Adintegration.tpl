<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Adintegration.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php

  $session = new Zend_Session_Namespace();
  if(!empty($session->show_hide_ads)) {
    if($session->review_communityad_integration == 1)
      $review_communityad_integration = $session->review_communityad_integration; 
    else
      $review_communityad_integration = 0;	
  }
  else {
    $review_communityad_integration = 1;
  }

?>