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
class Sitereview_Widget_BrowselocationSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', 0);
    if (empty($listingtype_id)) {
      $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    }

    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);

    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $sitereviewBrowseLocation = Zend_Registry::isRegistered('sitereviewBrowseLocation') ?  Zend_Registry::get('sitereviewBrowseLocation') : null;
    $this->view->listing_singular_lc = strtolower($listingtypeArray->title_singular);
    $this->view->listing_plural_lc = strtolower($listingtypeArray->title_plural);
    $this->view->listing_plural_uc = ucfirst($listingtypeArray->title_plural);
    $this->view->listing_singular_upper = strtoupper($listingtypeArray->title_singular);
    // Make form
    $this->view->form = $form = new Sitereview_Form_Locationsearch(array('type' => 'sitereview_listing', 'listingTypeId' => $listingtype_id));

    if (!empty($_POST)) {
      $this->view->is_ajax = $_POST['is_ajax'];
    }

    if (empty($_POST['location'])) {
      $this->view->locationVariable = '1';
    }

    if (empty($_POST['is_ajax'])) {
      $p = Zend_Controller_Front::getInstance()->getRequest()->getParams();
      $form->isValid($p);
      $values = $form->getValues();
      $customFieldValues = array_intersect_key($values, $form->getFieldElements());
      $this->view->is_ajax = $this->_getParam('is_ajax', 0);
    } else {
      $values = $_POST;
      $valuesArray = array_merge($_POST, $form->getValues());
      $customFieldValues = array_intersect_key($valuesArray, $form->getFieldElements());
    }

    unset($values['or']);
    $this->view->assign($values);
    if (@$values['show'] == 2) {
      $friendsIds = Engine_Api::_()->user()->getViewer()->membership()->getMembers();
      $ids = array();
      foreach ($friendsIds as $friendId) {
        $ids[] = $friendId->user_id;
      }
      $values['users'] = $ids;
    }

    $values['type'] = 'browse';
    $values['type_location'] = 'browseLocation';

    if (isset($values['show'])) {
      if ($form->show->getValue() == 3) {
        @$values['show'] = 3;
      }
    }

    $this->view->current_page = $page = $this->_getParam('page', 1);
    $this->view->current_totalpages = $page * 15;
    $this->view->enableLocation = $checkLocation = Engine_Api::_()->sitereview()->enableLocation($listingtype_id);
    $this->view->enablePrice = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.price.field', 1);

    //check for miles or street.
    if (isset($values['locationmiles']) && !empty($values['locationmiles'])) {
      if (isset($values['sitereview_street']) && !empty($values['sitereview_street'])) {
        $values['location'] = $values['sitereview_street'] . ',';
        unset($values['sitereview_street']);
      }

      if (isset($values['sitereview_city']) && !empty($values['sitereview_city'])) {
        $values['location'].= $values['sitereview_city'] . ',';
        unset($values['sitereview_city']);
      }

      if (isset($values['sitereview_state']) && !empty($values['sitereview_state'])) {
        $values['location'].= $values['sitereview_state'] . ',';
        unset($values['sitereview_state']);
      }

      if (isset($values['sitereview_country']) && !empty($values['sitereview_country'])) {
        $values['location'].= $values['sitereview_country'];
        unset($values['sitereview_country']);
      }
    }

    $result = Engine_Api::_()->getDbtable('listings', 'sitereview')->getSitereviewsSelect($values, $customFieldValues);
    $this->view->paginator = $paginator = Zend_Paginator::factory($result);
    $paginator->setItemCountPerPage(15);
    $this->view->paginator = $paginator->setCurrentPageNumber($page);
    $this->view->totalresults = $paginator->getTotalItemCount();
    $this->view->mobile = Engine_Api::_()->seaocore()->isMobile();

    if (!empty($_POST['is_ajax'])) {

      $this->view->flageSponsored = 0;
      if (!empty($checkLocation) && $paginator->getTotalItemCount() > 0) {
        $ids = array();
        foreach ($paginator as $listing) {
          $id = $listing->getIdentity();
          $ids[] = $id;
          $listing_temp[$id] = $listing;
        }

        $values['listing_ids'] = $ids;
        $this->view->locations = $locations = Engine_Api::_()->getDbtable('locations', 'sitereview')->getLocation($values);

        foreach ($locations as $location) {
          if ($listing_temp[$location->listing_id]->sponsored) {
            $this->view->flageSponsored = 1;
            break;
          }
        }

        $this->view->list = $listing_temp;
      } else {
        $this->view->enableLocation = 0;
      }
    }
    
    if( empty($sitereviewBrowseLocation) ) {
      return $this->setNoRender();
    }
  }

}