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
class Sitereview_Widget_LocationSidebarSitereviewController extends Seaocore_Content_Widget_Abstract {

    public function indexAction() {

        //DONT RENDER IF NOT AUTHORIZED
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return $this->setNoRender();
        }

        //GET SUBJECT
        $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        $this->view->listingtypeArray = $listingtypeArray = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);

        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
         if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "map"))
         return $this->setNoRender();
        }

        if (empty($listingtypeArray->location)) {
          return $this->setNoRender();
        }

        //GET LOCATION
        $value['id'] = $sitereview->getIdentity();
        $value['listingtype_id'] = $sitereview->listingtype_id;

        $this->view->location = $location = Engine_Api::_()->getDbtable('locations', 'sitereview')->getLocation($value);

        //DONT RENDER IF LOCAITON IS EMPTY
        if (empty($location)) {
            return $this->setNoRender();
        }

        $this->view->height = $this->_getParam('height', 200);
    }

}