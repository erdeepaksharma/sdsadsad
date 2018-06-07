<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Activity.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Api_Cache extends Core_Api_Abstract
{

  protected $_enable = true;
  protected $_cache = array();
  protected $_lastId = null;
  protected $_cachePrefix = 'aaf';
  protected $_lifetime = 1800;
  protected $_ids = array();
  protected $_tags = array('aaf_feed');
  protected $_allowCommon = array();

  /**
   * Consts for clean() method
   */
  const FEELINGLIST_RESPONSE = 'feeling';

  public function __construct()
  {
    $this->_enable = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.feed.cache', 1);
    $this->_allowCommon = array(self::FEELINGLIST_RESPONSE);
  }

  public function save($data, $id = null)
  {
    if( !$this->_hasEnable() ) {
      return false;
    }
    if( $id ) {
      $this->_lastId = $id = $this->_makeId($id);
    }
    if( !$this->_lastId ) {
      return false;
    }
    $lifetime = $this->_lifetime;
    $this->_cache()->save(array('aaf_cache_data' => $data), $this->_lastId, $this->_getTag(), $lifetime);
  }

  public function load($id = null)
  {
    if( !$id || !$this->_hasEnable() ) {
      return false;
    }
    $this->_lastId = $this->_makeId($id);
    $data = $this->_cache()->load($this->_lastId);
    if( !is_array($data) || !isset($data['aaf_cache_data']) ) {
      return;
    }
    return $data['aaf_cache_data'];
  }

  public function test($id = null)
  {
    if( !$id || !$this->_hasEnable() ) {
      return false;
    }
    $this->_lastId = $this->_makeId($id);
    $data = $this->_cache()->load($this->_lastId);
    return is_array($data) && isset($data['aaf_cache_data']);
  }

  public function remove($id)
  {
    if( !$id || !$this->_hasEnable() ) {
      return false;
    }
    $lastId = $this->_makeId($id);
    $this->_cache()->remove($lastId);
  }

  public function flush()
  {
    if( !$this->_cache() ) {
      return false;
    }
    //Zend_Cache::CLEANING_MODE_MATCHING_TAG
    $this->_cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, $this->_getTag());
  }

  public function flushAll()
  {
    if( !$this->_cache() ) {
      return false;
    }
    //Zend_Cache::CLEANING_MODE_MATCHING_TAG
    $this->_cache()->clean(Zend_Cache::CLEANING_MODE_ALL);
  }

  protected function _hasEnable()
  {
    return $this->_enable && $this->_cache();
  }

  protected function _getTag()
  {
    $tags = $this->_tags;
    return $tags;
  }

  protected function _makeId($id)
  {
    if( !$id ) {
      return;
    }
    if( isset($this->_ids[$id]) ) {
      return $this->_ids[$id];
    }
    $isCommon = false;
    foreach( $this->_allowCommon as $commonText ) {
      if( strpos($id, $commonText) !== false ) {
        $isCommon = true;
        break;
      }
    }
    $convertId = $id;
    if( !$isCommon ) {
      $convertId = $this->_makePartialId($convertId);
    }
    $translate = Zend_Registry::get('Zend_Translate');
    if( $translate ) {
      $convertId .= '_' . $translate->getLocale();
    }
    $this->_ids[$id] = $this->_cachePrefix . '_' . md5($convertId);
    return $this->_ids[$id];
  }

  protected function _makePartialId($id)
  {
    if( Engine_Api::_()->core()->hasSubject() ) {
      $id .= Engine_Api::_()->core()->getSubject()->getGuid();
    } else {
      $id .= 'nosubject_0';
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $id .= $viewer->getIdentity() ? $viewer->getGuid() : 'user_0';
    return $id;
  }

  protected function _cache()
  {
    return Zend_Registry::isRegistered('Zend_Cache') ? Zend_Registry::get('Zend_Cache') : null;
  }

}
