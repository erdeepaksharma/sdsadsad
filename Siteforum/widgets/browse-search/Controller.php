<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Widget_BrowseSearchController extends Engine_Content_Widget_Abstract {

    public function indexAction() {

        $searchForm = $this->view->searchForm = new Siteforum_Form_Search();

        $request = Zend_Controller_Front::getInstance()->getRequest();

        $forums = Engine_Api::_()->getDbtable('forums', 'siteforum')->getForumsAssoc();

        if (!empty($forums) && is_array($forums) && $searchForm->getElement('forum_id')) {
            $searchForm->getElement('forum_id')->addMultiOptions($forums);
        }

        $searchForm
                ->setMethod('get')
                ->populate($request->getParams())
        ;

        $searchForm->getElement('forum_id')->setAttrib('style', "width:" . $this->_getParam('forumWidth', 250) . "px");
        $searchForm->getElement('search')->setAttrib('style', 'width:' . $this->_getParam('searchWidth', 500) . 'px');
        $siteforumSearch = Zend_Registry::isRegistered('siteforumSearch') ? Zend_Registry::get('siteforumSearch') : null;
        if(empty($siteforumSearch))
            return $this->setNoRender();
        
        if($this->_getParam('viewType') == 'vertical'){
          $searchForm->setAttrib('class', 'siteforum-search-box_v');
        }
    }

}
