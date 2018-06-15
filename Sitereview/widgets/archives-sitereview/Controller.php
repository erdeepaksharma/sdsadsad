<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Widget_ArchivesSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    
    //DON'T RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      return $this->setNoRender();
    }

    //GET SUBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject();
    $owner = $sitereview->getOwner();

    //SHOW ARCHIVES
    $this->view->archive_sitereview = Engine_Api::_()->getDbtable('listings', 'sitereview')->getArchiveSitereview($owner, $sitereview->listingtype_id);

    if (Count($this->view->archive_sitereview) <= 0) {
      return $this->setNoRender();
    }
  }

}