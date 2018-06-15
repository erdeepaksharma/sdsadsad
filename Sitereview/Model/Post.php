<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Post.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_Post extends Core_Model_Item_Abstract {

  protected $_parent_type = 'sitereview_topic';
  protected $_owner_type = 'user';

  /**
   * Gets an absolute URL to the page to view this item
   *
   * @return string
   */
  public function getHref($params = array()) {
    
    //GET LISTING TYPE ID
    $listingtype_id = Engine_Api::_()->getDbTable('listings', 'sitereview')->getListingTypeId($this->listing_id);
    $tab_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('tab', null);
    $params = array_merge(array(
        'route' => "sitereview_extended_listtype_$listingtype_id",
        'controller' => 'topic',
        'action' => 'view',
        'listing_id' => $this->listing_id,
        'topic_id' => Engine_Api::_()->getItem('sitereview_topic', $this->topic_id)->getIdentity(),
        'post_id' => $this->getIdentity(),
        'tab' => $tab_id
            ), $params);
    $route = @$params['route'];
    unset($params['route']);
    return Zend_Controller_Front::getInstance()->getRouter()->assemble($params, $route, true);
  }

  public function getDescription() {
    
    //REMOVE BBCODE
    $desc = preg_replace('/\[[^\[\]]+?\]/', '', $this->body);
    return Engine_String::substr($desc, 0, 255);
  }

  public function getPostIndex() {
    
    $table = Engine_Api::_()->getDbTable('posts', 'sitereview');
    $select = $table->select()
            ->from($table->info('name'), new Zend_Db_Expr('COUNT(post_id) as count'))
            ->where('topic_id = ?', $this->topic_id)
            ->where('post_id < ?', $this->getIdentity())
            ->order('post_id ASC');

    $data = $select->query()->fetch();

    return (int) $data['count'];
  }

  public function getAuthorizationItem() {
    
    return $this->getParent('sitereview_listing');
  }

  //INTERNAL HOOKS
  protected function _insert() {
    
    if ($this->_disableHooks)
      return;

    if (!$this->listing_id) {
      throw new Exception('Cannot create post without listing_id');
    }

    if (!$this->topic_id) {
      throw new Exception('Cannot create post without topic_id');
    }

    //UPDATE TOPIC
    $table = Engine_Api::_()->getDbtable('topics', 'sitereview');
    $select = $table->select()->where('topic_id = ?', $this->topic_id)->limit(1);
    $topic = $table->fetchRow($select);

    $topic->lastpost_id = $this->post_id;
    $topic->lastposter_id = $this->user_id;
    $topic->modified_date = date('Y-m-d H:i:s');
    $topic->post_count++;
    $topic->save();

    parent::_insert();
  }

  protected function _delete() {
    
    if ($this->_disableHooks)
      return;

    //UPDATE TOPIC
    $table = Engine_Api::_()->getDbtable('topics', 'sitereview');
    $select = $table->select()->where('topic_id = ?', $this->topic_id)->limit(1);
    $topic = $table->fetchRow($select);
    $topic->post_count--;

    if ($topic->post_count == 0) {
      $topic->delete();
    } else {
      $topic->save();
    }
    parent::_delete();
  }

}