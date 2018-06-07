<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Widget_CustomBlockController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    $getCustomBlockSQL = Engine_Api::_()->advancedactivity()->getCustomBlockSettings(array('advancedactivity.custom-block'));
    if (empty($getCustomBlockSQL)) {
      return $this->setNoRender();
    }

    $getCustomBlockArray = Engine_Api::_()->advancedactivity()->getCustomBlock();
    $select = Engine_Api::_()->getDbtable('customblocks', 'advancedactivity')->getObj($getCustomBlockArray);

    $this->view->renderOne = $this->_getParam('renderOne', 0);
    $paginator = Zend_Paginator::factory($select);

    $this->view->getCustomBlockObj = $paginator;

    // Do not render if nothing to show
    if ($paginator->getTotalItemCount() <= 0) {
      return $this->setNoRender();
    }
  }

}

?>