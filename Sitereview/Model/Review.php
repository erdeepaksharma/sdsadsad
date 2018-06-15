<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Review.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_Review extends Core_Model_Item_Abstract {

  protected $_parent_type = 'sitereview_listing';
  protected $_owner_type = 'user';

  /**
   * Gets an absolute URL to the page to view this item
   *
   * @return string
   */
  public function getOwner($type = null) {

    if ($this->owner_id == 0)
      return;

    if ($this->type == 'editor' && $type == 'editor') {

      $editor = Engine_Api::_()->getDbtable('editors', 'sitereview')->getEditor($this->owner_id, $this->getParent()->listingtype_id);

      if ($editor) {
        return $editor;
      }
    }
    return parent::getOwner();
  }

  /**
   * Return href
   * */
  public function getHref($params = array()) {

		//GET LISTING TYPE ID
		$listingtype_id = $this->getParent()->listingtype_id;
    if ($this->type == 'editor') {
      return $this->getParent()->getHref(array('tab' => Engine_Api::_()->sitereview()->getTabId($listingtype_id, 'sitereview.editor-reviews-sitereview')));
    } else {

      //GET CONTENT ID
      $content_id = Engine_Api::_()->sitereview()->existWidget('sitereview_view_reviews', 0);
      $params = array_merge(array(
          'route' => "sitereview_view_review_listtype_$listingtype_id",
          'reset' => true,
          'listing_id' => $this->resource_id,
          'review_id' => $this->review_id,
          'slug' => $this->getSlug(),
          'tab' => $content_id,
              ), $params);
      $route = $params['route'];
      $reset = $params['reset'];
      unset($params['route']);
      unset($params['reset']);
      return Zend_Controller_Front::getInstance()->getRouter()
                      ->assemble($params, $route, $reset);
    }

  }

  /**
   * Return parent
   * */
  public function getAuthorizationItem() {
    return $this->getParent('sitereview_listing');
  }

  /**
   * Return description
   * */
  public function getDescription() {
    $tmpBody = strip_tags($this->body);
    return ( Engine_String::strlen($tmpBody) > 255 ? Engine_String::substr($tmpBody, 0, 255) . '...' : $tmpBody );
  }

  /**
   * Return rich content for feed items
   * */
  public function getRichContent() {
    $view = Zend_Registry::get('Zend_View');
    $view = clone $view;
    $view->clearVars();
    $view->addScriptPath('application/modules/Sitereview/views/scripts/');

    // Render the thingy
    $view->review = $this;
    $view->ratingData = $ratingData = Engine_Api::_()->getDbtable('ratings', 'sitereview')->profileRatingbyCategory($this->getIdentity());

    $rating_value = 0;
    foreach ($ratingData as $ratingparam):
      if (empty($ratingparam['ratingparam_name'])):
        $rating_value = $ratingparam['rating'];
        break;
      endif;
    endforeach;
    $view->ratingValue = $rating_value;

    return $view->render('activity-feed/_review.tpl');
  }

  /**
   * Return slug
   * */
  public function getSlug($str = null, $maxstrlen = 64) {

    if (null === $str) {
      $str = $this->title;
    }
    
    $maxstrlen = 225;

    return Engine_Api::_()->seaocore()->getSlug($str, $maxstrlen);
  }

  /**
   * Return rating data
   * */
  public function getRatingData() {
    return Engine_Api::_()->getDbtable('ratings', 'sitereview')->profileRatingbyCategory($this->getIdentity());
  }

  /**
   * Return helpful count
   * */
  public function getCountHelpful($type=1) {
    return Engine_Api::_()->getDbtable('helpful', 'sitereview')->getCountHelpful($this->getIdentity(), $type);
  }

  /**
   * Return previous review
   * */
  public function getPreviousReview() {
    $select = $this->getTable()->select()
            ->where('status =?', 1)
            ->where('review_id < (?)', $this->review_id)
            ->where('resource_id =?', $this->resource_id)
            ->where('resource_type =?', $this->resource_type)
            ->where("type in (?)", array('user', 'visitor'))
            ->order('review_id DESC');
    return $this->getTable()->fetchRow($select);
  }

  /**
   * Return next review
   * */
  public function getNextReview() {
    $select = $this->getTable()->select()
                    ->where('status =?', 1)
                    ->where('review_id > (?)', $this->review_id)
                    ->where('resource_id =?', $this->resource_id)
                    ->where('resource_type =?', $this->resource_type)->where("type in (?)", array('user', 'visitor'));
    return $this->getTable()->fetchRow($select);
  }

  /**
   * Gets a proxy object for the comment handler
   *
   * @return Engine_ProxyObject
   * */
  public function comments() {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('comments', 'core'));
  }

  /**
   * Gets a proxy object for the like handler
   *
   * @return Engine_ProxyObject
   * */
  public function likes() {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('likes', 'core'));
  }

  /**
   * Delete the reviews and belongings
   * 
   */
  public function _delete() {

    $review_id = $this->review_id;
    $db = Engine_Db_Table::getDefaultAdapter();

    $db->beginTransaction();
    try {

      //DELETE RATING ENTRIES
      $ratingTable = Engine_Api::_()->getDbtable('ratings', 'sitereview');
      $ratingTable->delete(array('review_id =?' => $review_id));

      //DELETE UPDATED ENTRIES
      $reviewDescriptionsTable = Engine_Api::_()->getDbtable('reviewDescriptions', 'sitereview');
      $reviewDescriptionsTable->delete(array('review_id =?' => $review_id));

      //DELETE RATING ENTRIES
      $reviewHelpfulTable = Engine_Api::_()->getDbtable('helpful', 'sitereview');
      $reviewHelpfulTable->delete(array('review_id =?' => $review_id));

      //UPDATE REVIEW_COUNT IN LISTING TABLE
      $listingTable = Engine_Api::_()->getItemTable($this->getParent()->getType());
      $primary = current($listingTable->info("primary"));
      $sitelisting = $listingTable->fetchRow(array("$primary = ?" => $this->resource_id));
      $sitelisting->review_count--;
      $sitelisting->save();

      $ratingTable->listRatingUpdate($this->resource_id, $this->resource_type);
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    //DELETE LISTING
    parent::_delete();
  }

}