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
class Sitereview_Widget_EditorsHomeStatisticsSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    //GET REVIEW TABLE
    $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');

    //FETCH TOTAL REVIEWS BY EDITOR
    $params = array();
    $params['type'] = 'editor';
    $this->view->totalEditorReviews = $reviewTable->totalReviews($params);

    //GET EDITOR TABLE
    $editorTable = Engine_Api::_()->getDbTable('editors', 'sitereview');

    //FETCH TOTAL EDITORS
    $this->view->totalEditors = $editorTable->getEditorsCount(0);

    //FETCH EDITORS PER LISTING TYPE
    $this->view->editorsPerListingType = $editorTable->getListingtypeEditorCount();
  }

}