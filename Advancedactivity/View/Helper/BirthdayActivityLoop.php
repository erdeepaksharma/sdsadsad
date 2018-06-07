<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: BirthdayActivityLoop.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/

class Advancedactivity_View_Helper_BirthdayActivityLoop extends Zend_View_Helper_Abstract {

  public function birthdayActivityLoop(Activity_Model_Action $action = null, array $data = array()) {
    if (null === $action) {
      return '';
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $actions = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getBirthdayFeedRelatedActions($action);
    if (empty($actions)) {
      return;
    }
    $form = new Activity_Form_Comment();

    $activity_moderate = "";
    if ($viewer->getIdentity()) {
      $activity_moderate = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $viewer->level_id, 'activity');
    }

    $hideItems = array();
    if (empty($subject)) {
      if ($viewer->getIdentity())
        $hideItems = Engine_Api::_()->getDbtable('hide', 'advancedactivity')->getHideItemByMember($viewer);
    }
    $poster = $posterId = array();
    $activity = array();
    foreach ($actions as $value) {
      // skip the hide actions and content        
      if (!empty($hideItems)) {
        if (isset($hideItems[$action->getType()]) && in_array($value->getIdentity(), $hideItems[$value->getType()])) {
          continue;
        }
        if (isset($hideItems[$value->getSubject()->getType()]) && in_array($value->getSubject()->getIdentity(), $hideItems[$value->getSubject()->getType()])) {
          continue;
        }
      }
      // skip disabled actions
      $activity[] = $value;
      if (in_array($value->subject_id, $posterId))
        continue;
      $posterId[] = $value->subject_id;
      $poster[] = Engine_Api::_()->getItem('user', $value->subject_id);
    }

    if (empty($activity)) {
      return;
    }
    $birtday = Engine_Api::_()->getApi('birthday', 'advancedactivity')->getMemberBirthday($action->getObject());
    $isAbletoWish = false;
    $bithDate = null;
    if (!empty($birtday)) {
      $date = time();
      $birtdayArray = explode('-', $birtday);
      $birth_month = $birtdayArray[0];
      $birth_day = $birtdayArray[1];
      $bithDateTime = mktime(0, 0, 0, $birth_month, $birth_day);
//      $tmstmp_obj = new Zend_Date($bithDateTime);
//      $tmstmp_obj->setTimezone(Engine_Api::_()->getApi('settings', 'core')->getSetting('core_locale_timezone', 'GMT'));
//      $bithDateTime_tmstmp = $tmstmp_obj->getTimestamp();
      $bithDate =   Engine_Api::_()->getApi('birthday', 'advancedactivity')->getDateDisplay($bithDateTime);    //date("F, d", $bithDateTime_tmstmp);
      if (!$viewer->isSelf($action->getObject()) && $action->getObject()->authorization()->isAllowed($viewer, 'comment'))
        $isAbletoWish = true;
    }
    $data = array_merge($data, array(
        'mainAction' => $action,
        'birthdayActions' => $activity,
        'totalFeed' => count($activity),
        'birthday' => $birtday,
        'birthdate' => $bithDate,
        'isAbletoWish' => $isAbletoWish,
        'poster' => $poster,
        'countPoster' => count($poster),
        'commentForm' => $form,
        'user_limit' => Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_userlength'),
        'allow_delete' => Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_userdelete'),
        'activity_moderate' => $activity_moderate,
            ));

    return $this->view->partial(
                    '_birthdayActivityText.tpl',
                    /*  Customization Start */ 'advancedactivity',
                    /*  Customization End */ $data
    );
  }

}