<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: FeelingController.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_FeelingController extends Core_Controller_Action_User
{
  public function getpreChoicesAction()
  {
    $response = Engine_Api::_()->getApi('cache', 'advancedactivity')->test(Advancedactivity_Api_Cache::FEELINGLIST_RESPONSE);
    if( $response ) {
      echo Engine_Api::_()->getApi('cache', 'advancedactivity')->load(Advancedactivity_Api_Cache::FEELINGLIST_RESPONSE);
      die;
    }
    $table = Engine_Api::_()->getDbtable('feelingtypes', 'advancedactivity');
    $select = $table->select($table->info('name'))
      ->where("enabled =?", 1)
      ->order('order ASC');
    try {
      $searchApi = Engine_Api::_()->getDbtable('search', 'core');
      $feelingtypes = $table->fetchAll($select);
      $feelingtype_json = array();
      $feeling_json = array();
      foreach( $feelingtypes as $feelingtype ) {
        $feelingContentTypes = array();
        $listingTypes = array();
        if( !empty($feelingtype->type) ) {
          $contentTypes = Zend_Json::decode($feelingtype->type);
          foreach( $contentTypes as $itemType ) {
            if( Engine_Api::_()->hasItemType($itemType) ) {
              $feelingContentTypes[] = $itemType;
            } elseif( stripos($itemType, '_listingtype_') !== false ) {
              $listingTypes[] = str_replace('sitereview_listingtype_', '', $itemType);
            }
          }
          if( count($feelingContentTypes) <= 0 && count($listingTypes) <= 0 ) {
            continue;
          }
        }
        if( !empty($feelingContentTypes) || !empty($listingTypes) ) {
          if( !empty($feelingContentTypes) ) {
            $searchSelect = $searchApi->select()
              ->where('type IN (?)', $feelingContentTypes);
            $searchFetchedObject = $searchApi->fetchAll($searchSelect);
            $feeling_json = $this->setJsonArray($feelingtype, $searchFetchedObject, $feeling_json);
          }
          if( !empty($listingTypes) && Engine_Api::_()->hasModuleBootstrap('sitereview') ) {
            $listingTable = Engine_Api::_()->getDbTable('listings', 'sitereview');
            $sitereviewTableName = $listingTable->info('name');
            $searchTableName = $searchApi->info('name');
            $select = $searchApi->select()
              ->from($searchApi->info('name'))
              ->join($sitereviewTableName, "$searchTableName.id = $sitereviewTableName.listing_id", array())
              ->where("$sitereviewTableName.listingtype_id IN (?)", $listingTypes)
              ->where("$searchTableName.type = ? ", 'sitereview_listing');
            $searchFetchedObject = $searchApi->fetchAll($select);
            $feeling_json = $this->setJsonArray($feelingtype, $searchFetchedObject, $feeling_json);
          }
        } else {
          foreach( $feelingtype->getFeelings() as $feelings ) {
            $feeling_json[$feelingtype->getIdentity()][] = array(
              'id' => $feelings->getIdentity(),
              'title' => $this->view->translate($feelings->getTitle()),
              'photo' => $this->view->itemPhoto($feelings, 'thumb.icon'),
              'url' => $feelings->getPhotoUrl(),
              'type' => ''
            );
          }
        }
        if( !empty($feeling_json[$feelingtype->getIdentity()]) ) {
          $feelingtype_json[] = array(
            'id' => $feelingtype->getIdentity(),
            'title' => $this->view->translate($feelingtype->getTitle()),
            'photo' => $this->view->itemPhoto($feelingtype, 'thumb.icon'),
            'url' => $feelingtype->getPhotoUrl(),
            'tagline' => $this->view->translate($feelingtype->tagline)
          );
        }
      }
      $response = json_encode(array('parent' => $feelingtype_json, 'child' => $feeling_json));
      Engine_Api::_()->getApi('cache', 'advancedactivity')->save($response, Advancedactivity_Api_Cache::FEELINGLIST_RESPONSE);
      echo $response;
    } catch( Exception $e ) {
      die("Exception " . $e);
    }
    die;
  }

  public function setJsonArray($feelingtype, $searchFetchedObject, $feeling_json)
  {
    foreach( $searchFetchedObject as $searchItem ) {
      $item = Engine_Api::_()->getItem($searchItem->type, $searchItem->id);
      if( empty($item) ) {
        continue;
      }
      $feeling_json[$feelingtype->getIdentity()][] = array(
        'id' => $item->getIdentity(),
        'title' => $item->getTitle(),
        'photo' => $this->view->itemPhoto($item, 'thumb.icon'),
        'url' => $item->getPhotoUrl(),
        'type' => $searchItem->type
      );
    }
    return $feeling_json;
  }

}
