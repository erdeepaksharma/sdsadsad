<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Birthday.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Api_Birthday extends Core_Api_Abstract {

  public function getMembersBirthdaysInRange($params=array()) {

    $table = Engine_Api::_()->fields()->getTable('user', 'meta');
    //start for Birth day work
    $date = time();
    $range = $params['range']; // = 2;

    $current_month = date('m', $date);
    $current_date_month = date('m-d', $date);

    $previous_date = date('d', $date) - $range;
    $previous_date_month = date('m-d', mktime(0, 0, 0, date("m"), $previous_date));


    $rmetaName = $table->info('name');

    $usertable = Engine_Api::_()->getDbTable('users', 'user');
    $ruserName = $usertable->info('name');

    $valuetable = Engine_Api::_()->getDbTable('values', 'birthday');
    $rvalueName = $valuetable->info('name');

    $select = $table->select()
            ->setIntegrityCheck(false)
            ->from($rmetaName, array($ruserName . '.user_id'))
            ->join($rvalueName, $rvalueName . '.field_id = ' . $rmetaName . '.field_id', array("DATE_FORMAT(" . $rvalueName . " .value, '%m') AS Month", "DATE_FORMAT(" . $rvalueName . " .value, '%d') AS Day"))
            ->join($ruserName, $rvalueName . '.item_id = ' . $ruserName . '.user_id', array())
            ->where($rmetaName . '.type = ?', 'birthdate');

    $select->where("DATE_FORMAT(" . $rvalueName . " .value, '%m-%d') <= ?", $current_date_month);
    $select->where("DATE_FORMAT(" . $rvalueName . " .value, '%m-%d') >= ?", $previous_date_month);

    return $select->query()->fetchAll(Zend_Db::FETCH_COLUMN);
    ;
  }

  public function getMemberBirthday($member) {
    $table = Engine_Api::_()->fields()->getTable('user', 'meta');
    $rmetaName = $table->info('name');

    $usertable = Engine_Api::_()->getDbTable('users', 'user');
    $ruserName = $usertable->info('name');

    $valuetable = Engine_Api::_()->getDbTable('values', 'birthday');
    $rvalueName = $valuetable->info('name');

    $select = $table->select()
            ->setIntegrityCheck(false)
            ->from($rmetaName, array())
            ->join($rvalueName, $rvalueName . '.field_id = ' . $rmetaName . '.field_id', array("DATE_FORMAT(" . $rvalueName . " .value, '%m-%d') AS birthday"))
            ->join($ruserName, $rvalueName . '.item_id = ' . $ruserName . '.user_id', array())
            ->where($rmetaName . '.type = ?', 'birthdate')
            ->where($ruserName . '.user_id = ?', $member->getIdentity());
    return $select->query()->fetchColumn('birthday');
  }

  public function getDateDisplay($timestamp) {
    //$timestamp = mktime(0, 0, 0,$date_value_array[1], $date_value_array[2], $date_value_array[3]);
    $month_value = Zend_Registry::get('Zend_Translate')->_(date('F', $timestamp));
    $date_value = date('d', $timestamp);
    $format = Engine_Api::_()->getApi('settings', 'core')->getSetting('birthday.listformat', 0);
    $date_string = "";
    switch ($format) {
      case 0 :
      case 2 :
        $date_string.= $month_value . ', ' . $date_value;
        break;
      case 1 :
      case 3 :
        $date_string.= $date_value . ', ' . $month_value;
        break;
    }
    return $date_string;
  }

}