<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: ActionTypes.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Model_DbTable_Tags extends Core_Model_DbTable_Tags
{

  // Tagging
  /**
   * Tag a resource
   *
   * @param Core_Model_Item_Abstract $resource The resource being tagged
   * @param Core_Model_Item_Abstract $tagger The resource doing the tagging
   * @param string|Core_Model_Item_Abstract $tag What is tagged in resource
   * @param array|null $extra
   * @return Engine_Db_Table_Row|null
   */
  public function addTagMap(Core_Model_Item_Abstract $resource, Core_Model_Item_Abstract $tagger, $tag, $extra = null)
  {
    $tag = $this->_getTag($tag, true);

    if( !$tag ) {
      return false;
    }

    // Check if resource was already tagged with this
    if( null !== ($tagmap = $this->getTagMap($resource, $tag)) ) {
      return false; // return $tagmap;
      //throw new Core_Model_Exception('Resource was already tagged as this');
    }

    // Do the tagging
    $table = $this->getMapTable();
    $tagmap = $table->createRow();
    $tagmap->setFromArray(array(
      'resource_type' => $resource->getType(),
      'resource_id' => $resource->getIdentity(),
      'tagger_type' => $tagger->getType(),
      'tagger_id' => $tagger->getIdentity(),
      'tag_type' => $tag->getType(),
      'tag_id' => $tag->getIdentity(),
      'creation_date' => new Zend_Db_Expr('NOW()'),
      'extra' => $extra
    ));
    $tagmap->save();

    if (isset($tag->tag_count) && isset($tag->modified_date)) {
      $tag->tag_count = $tag->tag_count + 1;
      $tag->modified_date = date('Y-m-d H:i:s');
      $tag->save();
    }
    return $tagmap;
  }

  public function setTagMaps(Core_Model_Item_Abstract $resource, $tagger, array $tags)
  {
    $existingTagMaps = $this->getTagMaps($resource);
    $added = array();
    $setTagIndex = array();
    $tagObjects = array();
    foreach( $tags as $tag )
    {
      if(empty($tag)) {
        continue;
      }

      $tagObject = $this->_getTag($tag, true);
      if (empty($tagObject)) {
        continue;
      }

      $tagObjects[] = $tagObject;
      $setTagIndex[$tagObject->getGuid()] = $tagObject;
    }

    // Check for new tags
    foreach( $tagObjects as $tag )
    {
      if( !$existingTagMaps->getRowMatching(array(
          'tag_type' => $tag->getType(),
          'tag_id' => $tag->getIdentity(),
        )) ) {
        $added[] = $this->addTagMap($resource, $tagger, $tag);
      }
    }

    // Check for removed tags
    foreach( $existingTagMaps as $tagmap )
    {
      $key = $tagmap->tag_type . '_' . $tagmap->tag_id;
      if( empty($setTagIndex[$key]) )
      {
        $item = Engine_Api::_()->getItem($tagmap->tag_type, $tagmap->tag_id);
        if (isset($item->text) && isset($item->tag_count) && isset($item->modified_date)) {
          $this->removeTagMap($resource, $item->text);
        } else {
          $tagmap->delete();
        }
      }
    }

    return $added;
  }
}