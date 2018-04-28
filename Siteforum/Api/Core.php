<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Core.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Api_Core extends Core_Api_Abstract {

  protected $_table;

  public function deleteSuggestion($viewer_id, $entity, $entity_id, $notifications_type) {
    $is_moduleEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('suggestion');
    if (!empty($is_moduleEnabled)) {
      $suggestion_table = Engine_Api::_()->getItemTable('suggestion');
      $suggestion_table_name = $suggestion_table->info('name');
      $suggestion_select = $suggestion_table->select()
        ->from($suggestion_table_name, array('suggestion_id'))
        ->where('owner_id = ?', $viewer_id)
        ->where('entity = ?', $entity)
        ->where('entity_id = ?', $entity_id);
      $suggestion_array = $suggestion_select->query()->fetchAll();
      if (!empty($suggestion_array)) {
        foreach ($suggestion_array as $sugg_id) {
          Engine_Api::_()->getItem('suggestion', $sugg_id['suggestion_id'])->delete();
          Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('object_id = ?' => $sugg_id['suggestion_id'], 'type = ?' => $notifications_type));
        }
      }
    }
  }

  public function isModulesSupport() {
    $isSpectacularActivate = Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.isActivate', 0);
    if (empty($isSpectacularActivate))
      return array();

    $modArray = array(
      'advancedactivity' => '4.8.9p10',
      'communityad' => '4.8.9p6',
      'suggestion' => '4.8.9p2',
      'sitelike' => '4.8.9p1',
      'facebookse' => '4.8.9p1',
      'facebooksefeed' => '4.8.9p1',
      'sitefaq' => '4.8.9',
      'seaocore' => '4.8.9p20',
    );
    $finalModules = array();
    foreach ($modArray as $key => $value) {
      $isModEnabled = Engine_Api::_()->hasModuleBootstrap($key);
      if (!empty($isModEnabled)) {
        $getModVersion = Engine_Api::_()->getDbtable('modules', 'core')->getModule($key);
        $isModSupport = $this->checkVersion($getModVersion->version, $value);
        if (empty($isModSupport)) {
          $finalModules[] = $getModVersion->title;
        }
      }
    }
    return $finalModules;
  }
    private function checkVersion($databaseVersion, $checkDependancyVersion) {
        $f = $databaseVersion;
        $s = $checkDependancyVersion;
        if (strcasecmp($f, $s) == 0)
            return -1;

        $fArr = explode(".", $f);
        $sArr = explode('.', $s);
        if (count($fArr) <= count($sArr))
            $count = count($fArr);
        else
            $count = count($sArr);

        for ($i = 0; $i < $count; $i++) {
            $fValue = $fArr[$i];
            $sValue = $sArr[$i];
            if (is_numeric($fValue) && is_numeric($sValue)) {
                if ($fValue > $sValue)
                    return 1;
                elseif ($fValue < $sValue)
                    return 0;
                else {
                    if (($i + 1) == $count) {
                        return -1;
                    } else
                        continue;
                }
            }
            elseif (is_string($fValue) && is_numeric($sValue)) {
                $fsArr = explode("p", $fValue);

                if ($fsArr[0] > $sValue)
                    return 1;
                elseif ($fsArr[0] < $sValue)
                    return 0;
                else {
                    return 1;
                }
            } elseif (is_numeric($fValue) && is_string($sValue)) {
                $ssArr = explode("p", $sValue);

                if ($fValue > $ssArr[0])
                    return 1;
                elseif ($fValue < $ssArr[0])
                    return 0;
                else {
                    return 0;
                }
            } elseif (is_string($fValue) && is_string($sValue)) {
                $fsArr = explode("p", $fValue);
                $ssArr = explode("p", $sValue);
                if ($fsArr[0] > $ssArr[0])
                    return 1;
                elseif ($fsArr[0] < $ssArr[0])
                    return 0;
                else {
                    if ($fsArr[1] > $ssArr[1])
                        return 1;
                    elseif ($fsArr[1] < $ssArr[1])
                        return 0;
                    else {
                        return -1;
                    }
                }
            }
        }
    }
  public function getItemTableClass($type) {

    // Generate item table class manually
    $module = 'Siteforum';
    $class = $module . '_Model_DbTable_' . self::typeToClassSuffix($type, $module);
    if (substr($class, -1, 1) === 'y' && substr($class, -3) !== 'way') {
      $class = substr($class, 0, -1) . 'ies';
    } else if (substr($class, -1, 1) !== 's') {
      $class .= 's';
    }

    return $class;
  }

  /**
   * Gets the class of an item
   *
   * @param string $type The item type
   * @return string The class name
   */
  public function getItemClass($type) {
    $module = 'Siteforum';
    return ucfirst($module) . '_Model_' . self::typeToClassSuffix($type, $module);
  }

  /**
   * Used to inflect item types to class suffix.
   * 
   * @param string $type
   * @param string $module
   * @return string
   */
  static public function typeToClassSuffix($type, $module) {

    $parts = explode('_', $type);
    if (count($parts) > 1 && ($parts[0] === strtolower($module) || $parts[0] === strtolower('forum'))) {
      array_shift($parts);
    }
    $partial = str_replace(' ', '', ucwords(join(' ', $parts)));
    return $partial;
  }

  public function isOnline($userId) {

    $onlineTable = Engine_Api::_()->getDbtable('online', 'user');
    $onlineTableName = $onlineTable->info('name');
    $select = $onlineTable->select()
      ->from($onlineTableName)
      ->where($onlineTableName . '.user_id = ?', $userId)
      ->where('active > ?', new Zend_Db_Expr('DATE_SUB(NOW(),INTERVAL 20 MINUTE)'));
    $result = $onlineTable->fetchAll($select);
    if (count($result) != 0) {
      return 1;
    } else {
      return 0;
    }
    return $row;
  }

  public function getTags($params = array()) {

    $tableSitetopic = Engine_Api::_()->getDbtable('topics', 'siteforum');
    $tableSitetopicName = $tableSitetopic->info('name');

    $select = $tableSitetopic->select()
      ->setIntegrityCheck(false)
      ->from($tableSitetopicName, array("topic_id"));

    if (!empty($params['user_id'])) {
      $select->where($tableSitetopicName . '.user_id = ?', $params['user_id']);
    }

    $select->distinct(true);

    $tableTagMaps = Engine_Api::_()->getDbtable('tagMaps', 'core');
    $tableTagMapsName = $tableTagMaps->info('name');

    $tableTags = 'engine4_core_tags';

    $select = $tableTagMaps->select()
      ->setIntegrityCheck(false)
      ->from($tableTagMapsName, array("COUNT($tableTagMapsName.resource_id) AS Frequency"))
      ->joinInner($tableTags, "$tableTags.tag_id = $tableTagMapsName.tag_id", array('text', 'tag_id'))
      ->where($tableTagMapsName . '.resource_type = ?', 'forum_topic')
      ->group("$tableTags.text");

    if (isset($params['orderingType']) && !empty($params['orderingType'])) {
      $select->order("$tableTags.text");
    }

    $total_tags = $params['totalTags'];

    if (!empty($total_tags)) {
      $select = $select->limit($total_tags);
    }

    //RETURN RESULTS
    return $select->query()->fetchAll();
  }
  
    public function isModerator($user) {
        
      $listitemTable = Engine_Api::_()->getItemTable('forum_list_item');
       $results = $listitemTable->select()
                ->from($listitemTable->info('name'),'child_id')
                ->where('child_id = ?',$user->getIdentity())
                ->query()
                ->fetchColumn(0);
       
       if(!empty($results))
         return 1;
       else
         return 0;
  }

}
