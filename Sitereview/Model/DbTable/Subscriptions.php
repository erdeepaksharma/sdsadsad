<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Subscriptions.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Sitereview_Model_DbTable_Subscriptions extends Engine_Db_Table
{
  public function sendNotifications(Sitereview_Model_Listing $sitereview)
  {
    if( !empty($sitereview->draft)) {
      return $this;
    }

    //GET REVIEW OWNER
    $owner = $sitereview->getOwner('user');
    $listingtype_id = $sitereview->listingtype_id;

    $listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $title_singular = strtolower($listingType->title_singular);

    //GET NOTIFICATION TABLE
    $notificationTable = Engine_Api::_()->getDbtable('notifications', 'activity');

    //GET ALL SUBSCRIBERS
    $identities = $this->select()
      ->from($this, 'subscriber_user_id')
      ->where('user_id = ?', $sitereview->owner_id)
      ->where('listingtype_id =?', $sitereview->listingtype_id)
      ->query()
      ->fetchAll(Zend_Db::FETCH_COLUMN);

    if( empty($identities) || count($identities) <= 0 ) {
      return $this;
    }

    $users = Engine_Api::_()->getItemMulti('user', $identities);

    if( empty($users) || count($users) <= 0 ) {
      return $this;
    }

    //SEND NOTIFICATIONS
    foreach( $users as $user ) {
      $notificationTable->addNotification($user, $owner, $sitereview, 'sitereview_subscribed_new', array("listingtype" => $title_singular));
    }

    return $this;
  }

  public function checkSubscription(User_Model_User $user, User_Model_User $subscriber, $listingtype_id)
  {

    return (bool) $this->select()
        ->from($this, new Zend_Db_Expr('TRUE'))
        ->where('user_id = ?', $user->getIdentity())
        ->where('subscriber_user_id = ?', $subscriber->getIdentity())
        ->where('listingtype_id = ?', $listingtype_id)
        ->query()
        ->fetchColumn();
  }

  public function createSubscription(User_Model_User $user, User_Model_User $subscriber, $listingtype_id)
  {
    //IGNORE IF ALREADY SUBSCRIBED
    if( $this->checkSubscription($user, $subscriber, $listingtype_id) ) {
      return $this;
    }

    //CREATE
    $this->insert(array(
      'user_id' => $user->getIdentity(),
      'subscriber_user_id' => $subscriber->getIdentity(),
      'listingtype_id' => $listingtype_id
    ));

    return $this;
  }

  public function removeSubscription(User_Model_User $user, User_Model_User $subscriber, $listingtype_id)
  {
    //IGNORE IF ALREADY NOT SUBSCRIBED
    if( !$this->checkSubscription($user, $subscriber, $listingtype_id) ) {
      return $this;
    }

    //DELETE
    $this->delete(array(
      'user_id = ?' => $user->getIdentity(),
      'subscriber_user_id = ?' => $subscriber->getIdentity(),
      'listingtype_id =?' => $listingtype_id
    ));

    return $this;
  }
}
