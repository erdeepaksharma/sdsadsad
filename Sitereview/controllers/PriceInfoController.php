<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: PriceInfoController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_PriceInfoController extends Seaocore_Controller_Action_Standard {

  protected $_listingType;

  //COMMON ACTION WHICH CALL BEFORE EVERY ACTION OF THIS CONTROLLER
  public function init() {

    $front = Zend_Controller_Front::getInstance();
    $action = $front->getRequest()->getActionName();
    if ($action == 'redirect') {
      return;
    }

    //ONLY LOGGED IN USER CAN ADD PRICE
    if (!$this->_helper->requireUser()->isValid())
      return;

    //GET LISTING TYPE ID
    $listingtype_id = $this->_getParam('listingtype_id', null);
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $this->_listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);


    //AUTHENTICATION CHECK
    if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "view_listtype_$listingtype_id")->isValid()) {
      return $this->_forwardCustom('requireauth', 'error', 'core');
    }
 
    if ($action != 'redirect') {
      $viewer = Engine_Api::_()->user()->getViewer();

      if ($action == 'edit' || $action == 'delete') {
        $priceinfo_id = $this->_getParam('id', null);
        $priceInfo = Engine_Api::_()->getDbTable('priceinfo', 'sitereview')->getPriceInfo($priceinfo_id);
        $sitereview = Engine_Api::_()->getItem('sitereview_listing', $priceInfo->listing_id);
      } else {
        $sitereview = Engine_Api::_()->getItem('sitereview_listing', $this->_getParam('id'));
      }

      if (!$this->_helper->requireAuth()->setAuthParams($sitereview, $viewer, "edit_listtype_$listingtype_id")->isValid()) {
        return $this->_forwardCustom('requireauth', 'error', 'core');
      }
      
      //IF WHERE TO BUY IS NOT ALLOWED
      if (!$sitereview->allowWhereToBuy()) {
        return $this->_forwardCustom('requireauth', 'error', 'core');
      }
    }
  }

  public function indexAction() {

    //GET LISTING ID
    $listing_id = $this->_getParam('id', null);
    $this->view->includeDiv = $this->_getParam('includeDiv', 1);
    $this->view->listing_singular_uc = ucfirst($this->_listingType->title_singular);
    $this->view->listing_singular_lc = strtolower($this->_listingType->title_singular);
    $this->view->listing_singular_upper = strtoupper($this->_listingType->title_singular);
    $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
    $this->view->listingtype_id = $listingtype_id = $sitereview->listingtype_id;

    $this->view->TabActive = 'priceinfo';
    $this->view->show_price = ($this->_listingType->where_to_buy == 1) ? 0 : 1;
    $this->view->priceInfos = Engine_Api::_()->getDbTable('priceinfo', 'sitereview')->getPriceDetails($listing_id);
    
    Engine_Api::_()->core()->setSubject($sitereview);  
    if(!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
        $this->_helper->content
            ->setContentName("sitereview_priceinfo_index_listtype_$listingtype_id")
            //->setNoRender()
            ->setEnabled();
    
    }  
    
  }  

  public function addAction() {

    //LAYOUT
    if (null === $this->_helper->ajaxContext->getCurrentContext()) {
      $this->_helper->layout->setLayout('default-simple');
    } else {
      $this->_helper->layout->disableLayout(true);
    }

    //ONLY LOGGED IN USER CAN VIEW THIS PAGE
    if (!$this->_helper->requireUser()->isValid())
      return;

    //GET LISTING ID
    $listing_id = $this->_getParam('id', null);
    $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);  
    //MAKE FORM
    $this->view->form = $form = new Sitereview_Form_Priceinfo_Add();

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $table = Engine_Api::_()->getDbTable('priceinfo', 'sitereview');
      $db = $table->getAdapter();
      $db->beginTransaction();
      try {

        $values = array_merge($form->getValues(), array('listing_id' => $listing_id));
        if ($values['wheretobuy_id'] != 1) {
          unset($values['title']);
          unset($values['address']);
          unset($values['contact']);
        } elseif (empty($values['title'])) {
          $error = $this->view->translate('Please complete Title field - it is required.');
          $error = Zend_Registry::get('Zend_Translate')->_($error);
          $form->getDecorator('errors')->setOption('escape', false);
          $form->addError($error);
          return;
        }
        $priceInfo = $table->createRow();
        $priceInfo->setFromArray($values);
        $priceInfo->save();

        $preg_match = preg_match('/\s*[a-zA-Z0-9]{2,5}:\/\//', $priceInfo->url);

        if (empty($preg_match)) {
          $priceInfo->url = "http://" . $priceInfo->url;
          $priceInfo->save();
        }

        //COMMIT
        $db->commit();
        $listingtype_id = $this->_listingType->listingtype_id;
        $listingtypeSingular = strtoupper($this->_listingType->title_singular);
        if(Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
            $this->view->responseHTML = $this->view->action('index', 'price-info', 'sitereview', array(
            'includeDiv' => 0,
            'id' => $listing_id,
            'listingtype_id' => $listingtype_id,
            'format' => 'html',
                ));
            $this->_helper->contextSwitch->initContext();
            if (empty($this->view->responseHTML)) {
              $this->_forwardCustom('success', 'utility', 'core', array(
                  'smoothboxClose' => true,
                  'parentRefresh' => true,
                  'format' => 'smoothbox',
                  'messages' => Zend_Registry::get('Zend_Translate')->_('NEW_'.$listingtypeSingular.'_WHERE_TO_BUY_OPTION_HAS_BEEN_ADDED_SUCCESSFULLY')
              ));
            }
        } else {
            $this->_forwardCustom('success', 'utility', 'core', array(
            'redirect' => $this->_helper->url->url(array('action' => 'index', 'id' => $listing_id), "sitereview_priceinfo_listtype_$listingtype_id", true),
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('NEW_'.$listingtypeSingular.'_WHERE_TO_BUY_OPTION_HAS_BEEN_ADDED_SUCCESSFULLY')),
           ));
        }
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    }
  }

  public function editAction() {

    //LAYOUT
    if (null === $this->_helper->ajaxContext->getCurrentContext()) {
      $this->_helper->layout->setLayout('default-simple');
    } else {
      $this->_helper->layout->disableLayout(true);
    }

    //ONLY LOGGED IN USER CAN VIEW THIS PAGE
    if (!$this->_helper->requireUser()->isValid())
      return;

    //GET PRICE INFO ID
    $priceinfo_id = $this->_getParam('id', null);

    $priceInfo = Engine_Api::_()->getDbTable('priceinfo', 'sitereview')->getPriceInfo($priceinfo_id);
    $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $priceInfo->listing_id);  
    //MAKE FORM
    $this->view->form = $form = new Sitereview_Form_Priceinfo_Edit();
    $form->populate($priceInfo->toArray());

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();
      if ($values['wheretobuy_id'] != 1) {
        unset($values['title']);
      } elseif (empty($values['title'])) {
        $error = $this->view->translate('Please complete Title field - it is required.');
        $error = Zend_Registry::get('Zend_Translate')->_($error);
        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
        return;
      }
      $table = Engine_Api::_()->getDbTable('priceinfo', 'sitereview');
      $db = $table->getAdapter();
      $db->beginTransaction();
      try {

        $priceInfo->setFromArray($values);
        $priceInfo->save();

        $preg_match = preg_match('/\s*[a-zA-Z0-9]{2,5}:\/\//', $priceInfo->url);
        if (empty($preg_match)) {
          $priceInfo->url = "http://" . $priceInfo->url;
          $priceInfo->save();
        }

        //COMMIT
        $db->commit();
        $listingtype_id = $this->_listingType->listingtype_id;
        if(Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
            $this->view->responseHTML = $this->view->action('index', 'price-info', 'sitereview', array(
                'includeDiv' => 0,
                'id' => $priceInfo->listing_id,
                'listingtype_id' => $listingtype_id,
                'format' => 'html',
                    ));
            $this->_helper->contextSwitch->initContext();
            $listingtypeSingular = strtoupper($this->_listingType->title_singular);
            if (empty($this->view->responseHTML)) {
              $this->_forwardCustom('success', 'utility', 'core', array(
                  'smoothboxClose' => true,
                  'parentRefresh' => true,
                  'format' => 'smoothbox',
                  'messages' => Zend_Registry::get('Zend_Translate')->_('EDIT_'.$listingtypeSingular.'_WHERE_TO_BUY_OPTION_HAS_BEEN_EDITED_SUCCESSFULLY')
              ));
            }
        } else {
            $this->_forwardCustom('success', 'utility', 'core', array(
            'redirect' => $this->_helper->url->url(array('action' => 'index', 'id' => $priceInfo->listing_id), "sitereview_priceinfo_listtype_$listingtype_id", true),
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('EDIT_'.$listingtypeSingular.'_WHERE_TO_BUY_OPTION_HAS_BEEN_EDITED_SUCCESSFULLY')),
           ));
        }
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    }
  }

  public function deleteAction() {

    //LAYOUT
    if (null === $this->_helper->ajaxContext->getCurrentContext()) {
      $this->_helper->layout->setLayout('default-simple');
    } else {
      $this->_helper->layout->disableLayout(true);
    }

    //ONLY LOGGED IN USER CAN VIEW THIS PAGE
    if (!$this->_helper->requireUser()->isValid())
      return;

    //GET PRICE INFO ID
    $this->view->priceinfo_id = $priceinfo_id = $this->_getParam('id', null);

    $priceInfo = Engine_Api::_()->getDbTable('priceinfo', 'sitereview')->getPriceInfo($priceinfo_id);
    $this->view->listing_id = $listing_id = $priceInfo->listing_id;
    $this->view->listingtype_id = $this->_listingType->listingtype_id;
    if (!$this->getRequest()->isPost())
      return;

    //DELTE PRICE INFO
    Engine_Api::_()->getDbTable('priceinfo', 'sitereview')->delete(array('priceinfo_id = ?' => $priceinfo_id));

    $listingtype_id = $this->_listingType->listingtype_id;
    $listingtypeSingular = strtoupper($this->_listingType->title_singular);
    if(Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
        $this->view->responseHTML = $this->view->action('index', 'price-info', 'sitereview', array(
            'includeDiv' => 0,
            'id' => $listing_id,
            'listingtype_id' => $listingtype_id,
            'format' => 'html',
                ));
        $this->_helper->contextSwitch->initContext();

        if (empty($this->view->responseHTML)) {
          $this->_forwardCustom('success', 'utility', 'core', array(
              'smoothboxClose' => true,
              'parentRefresh' => true,
              'format' => 'smoothbox',
              'messages' => Zend_Registry::get('Zend_Translate')->_('DELETE_'.$listingtypeSingular.'_WHERE_TO_BUY_OPTION_HAS_BEEN_DELETED_SUCCESSFULLY')
          ));
        }
    } else {
        $this->_forwardCustom('success', 'utility', 'core', array(
            'redirect' => $this->_helper->url->url(array('action' => 'index', 'id' => $listing_id), "sitereview_priceinfo_listtype_$listingtype_id", true),
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('DELETE_'.$listingtypeSingular.'_WHERE_TO_BUY_OPTION_HAS_BEEN_DELETED_SUCCESSFULLY')),
           ));
    }
  }

	public function redirectAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    if($viewer) {
			$viewer_id = $viewer->getIdentity();
		} else {
			$viewer_id = 0;
		}
    $listing_owner_id = Engine_Api::_()->getItem('sitereview_listing', $this->_getParam('id'))->owner_id;
    $url = $this->_getParam('url', null);
    if (empty($url)) {
      return $this->_forwardCustom('notfound', 'error', 'core');
    }
    header('Location: ' . @base64_decode($url) . "?shopping_user=$viewer_id&listing_owner=$listing_owner_id");
    exit(0);
  }

}
