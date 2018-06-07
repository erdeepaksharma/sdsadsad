<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Action.php 10005 2013-03-26 23:00:09Z john $
 * @author     John
 * @todo       documentation
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Advancedactivity_Model_Action extends Activity_Model_Action
{

  protected $_type = 'activity_action';
  // Comments linked to object
  protected $_allowCommentableForSimilar = array(3);
  // Share linked to object or not linked to any
  protected $_allowShareableForSimilar = array(0, 3);
  protected $_customAttachments = null;
  protected $_canEdit = null;

  public function getType($inflect = false)
  {
    if( $inflect ) {
      return str_replace(' ', '', ucwords(str_replace('_', ' ', $this->_type)));
    }

    return $this->_type;
  }

  // General
  public function getHref($params = array())
  {
    $displayable = $this->getTypeInfo()->displayable;
    $params['action_id'] = $this->getIdentity();
    if( $displayable & 2 ) {
      $obj = $this->getObject();
      return !$obj ? null : $obj->getHref($params);
    } elseif( $displayable & 1 ) {
      $obj = $this->getSubject();
      return !$obj ? null : $obj->getHref($params);
    } elseif( $displayable & 4 ) {
      $params['action'] = 'home';
      return Zend_Controller_Front::getInstance()->getRouter()->assemble($params, 'user_general', true);
    } else {
      return null;
    }
  }

  /**
   * Return a category
   * */
  public function getCategory()
  {
    if( isset($this->category_id) && Engine_Api::_()->hasItemType('advancedactivity_category') && $this->category_id ) {
      return Engine_Api::_()->getItem("advancedactivity_category", $this->category_id);
    }
  }

  public function getLikesReactionPopularity()
  {
    $likes = $this->likes();
    $table = $likes->getReceiver();
    $select = new Zend_Db_Select($table->getAdapter());
    $select->from($table->info('name'), array('reaction', 'count(*) as reaction_count'))
      ->group('reaction')
      ->order('reaction_count desc')
      ->order('like_id desc');
    if( get_class($table) === 'Core_Model_DbTable_Likes' ) {
      $select->where('resource_type = ?', $likes->getSender()->getType());
    }
    $select->where('resource_id = ?', $likes->getSender()->getIdentity());
    return $select->query()->fetchAll();
  }

  public function getCommentObject()
  {
    $commentable = $this->getTypeInfoCommentable();
    if( $commentable === 4 ) {
      $attachments = $this->getAttachments();
      $commentable = $attachments[0]->item instanceof Core_Model_Link ? 1 : 4;
    }

    switch( $commentable ) {
      // Comments linked to action item
      default: case 0: case 1:case 2:case 3:
        return $this->getObject();
        break;

      // Comments linked to the first attachment
      case 4:
        $attachments = $this->getAttachments();
        if( !isset($attachments[0]) || !($attachments[0]->item instanceof Core_Model_Item_Abstract) ) {
          return;
        }
        return $attachments[0]->item;
        break;
    }
  }

  public function getAttachments()
  {
    if( null !== $this->_attachments ) {
      return $this->_attachments;
    }

    if( $this->attachment_count <= 0 ) {
      return null;
    }

    $table = Engine_Api::_()->getDbtable('attachments', 'activity');
    $select = $table->select()
      ->where('action_id = ?', $this->action_id);

    foreach( $table->fetchAll($select) as $row ) {
      if( !Engine_Api::_()->hasItemType($row->type) )
        continue;
      $item = Engine_Api::_()->getItem($row->type, $row->id);
      if( $item instanceof Core_Model_Item_Abstract ) {
        $val = new stdClass();
        $val->meta = $row;
        $val->item = $item;
        $this->_attachments[] = $val;
      }
    }

    return $this->_attachments;
  }

  public function getCustomAttachments()
  {

    if( $this->attachment_count > 0 ) {
      return null;
    }
    if( null !== $this->_customAttachments ) {
      return $this->_customAttachments;
    }
    $params = $this->params;
    $this->_customAttachments = array();
    if( !empty($params['feelings']['type']) && Engine_Api::_()->hasItemType($params['feelings']['type']) ) {
      $item = Engine_Api::_()->getItem($params['feelings']['type'], $params['feelings']['child']);
      if( $item instanceof Core_Model_Item_Abstract ) {
        $val = new stdClass();
        $val->meta = new stdClass();
        $val->meta->type = $item->getType();
        $val->meta->id = $item->getIdentity();
        $val->meta->action_id = $this->getIdentity();
        $val->meta->mode = 1;
        $val->meta->attachment_id = 0;
        $val->item = $item;
        $this->_customAttachments[] = $val;
      }
    }
    return $this->_customAttachments;
  }

  /**
   * Get the type info
   *
   * @return Engine_Db_Table_Row
   */
  public function getTypeInfoCommentable()
  {
    $info = Engine_Api::_()->getDbtable('actionTypes', 'activity')->getActionType($this->type);
    if( $info && in_array($this->type, $this->getCommentOnAttachmentType()) ) {
      $attachments = $this->getAttachments();

      if( count($attachments) > 0 && isset($attachments[0]) && $attachments[0]->item instanceof Core_Model_Item_Abstract &&
        (method_exists($attachments[0]->item, 'comments') || method_exists($attachments[0]->item, 'likes')) ) {
        return 4;
      } else {
        return $info->commentable == 4 ? 1 : $info->commentable;
      }
    }
    return $info->commentable;
  }

  public function getCommentsLikes($comments, $viewer)
  {
    if( empty($comments) ) {
      return array();
    }

    $likes = $comments[0]->likes();
    $table = $likes->getReceiver();

    $ids = array();

    foreach( $comments as $c ) {
      $ids[] = $c->comment_id;
    }

    $resourceType = null;
    $commentable = $this->getTypeInfoCommentable();
    switch( $commentable ) {
      // Comments linked to action item
      default: case 0: case 1:
        $resourceType = 'activity_comment';
        break;

      // Comments linked to subject
      case 2:
        $resourceType = $this->getSubject()->getType();
        break;

      // Comments linked to object
      case 3:
        $resourceType = $this->getObject()->getType();
        break;

      // Comments linked to the first attachment
      case 4:
        $attachments = $this->getAttachments();
        if( !isset($attachments[0]) ) {
          return array();
        }
        return $attachments[0]->item->getType();
        break;
    }

    $select = $table
      ->select()
      ->from($table, 'resource_id')
      ->where('resource_type = ?', $resourceType)
      ->where('resource_id IN (?)', $ids)
      ->where('poster_type = ?', $viewer->getType())
      ->where('poster_id = ?', $viewer->getIdentity());

    $isLiked = array();

    $rs = $table->fetchAll($select);

    foreach( $rs as $r ) {
      $isLiked[$r->resource_id] = true;
    }

    return $isLiked;
  }

  public function canEdit()
  {
    if( $this->_canEdit != null ) {
      return $this->_canEdit;
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $editable = (int) $this->getTypeInfo()->editable;
    $editableFeeds = array("post", "post_self", "share", "status", 'sitetagcheckin_status', 'sitetagcheckin_post_self', 'sitetagcheckin_post', 'post_self_photo', 'post_self_video', 'post_self_music', 'post_self_link', 'sitegroup_post', 'sitepage_post', 'sitebusiness_post', 'sitegroup_post_self', 'siteevent_post', 'siteevent_post_parent', 'sitebusiness_post_self', 'sitepage_post_self', 'post_self_sell');
    if( !$viewer->getIdentity() || (!$editable && !in_array($this->type, $editableFeeds)) ) {
      $this->_canEdit = false;
      return false;
    }

    $maxEditTime = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'activity', 'edit_time');
    $isEditable = true;
    if( $maxEditTime ) {
      $relDate = new Zend_Date($this->getTimeValue());
      $relDate->add((int) $maxEditTime, Zend_Date::MINUTE);
      $isEditable = $relDate->sub(Zend_Date::now())->toValue() > 0;
    }

    if( !$isEditable ) {
      $this->_canEdit = false;
      return false;
    }

    $activityModerate = Engine_Api::_()->getDbtable('permissions', 'authorization')
      ->getAllowed('user', $viewer->level_id, 'activity');
    $this->_canEdit = $activityModerate || 'user' == $this->subject_type && $viewer->getIdentity() == $this->subject_id;
    return $this->_canEdit;
  }

  public function comments()
  {
    $commentable = $this->getTypeInfoCommentable();
    switch( $commentable ) {
      // Comments linked to subject
      case 2:
        return $this->getSubject()->comments();
        break;

      // Comments linked to object
      case 3:
        return $this->getObject()->comments();
        break;

      // Comments linked to the first attachment
      case 4:
        $attachments = $this->getAttachments();
        if( isset($attachments[0]) && method_exists($attachments[0]->item, 'comments') ) {
          return $attachments[0]->item->comments();
          break;
        }
      // Comments linked to action item
      default: case 0: case 1:
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('comments', 'activity'));
        break;
    }

    throw new Activity_Model_Exception('Comment handler undefined');
  }

  public function likes()
  {
    $commentable = $this->getTypeInfoCommentable();
    switch( $commentable ) {
      // Comments linked to subject
      case 2:
        return $this->getSubject()->likes();
        break;

      // Comments linked to object
      case 3:
        return $this->getObject()->likes();
        break;

      // Comments linked to the first attachment
      case 4:
        $attachments = $this->getAttachments();
        if( isset($attachments[0]) && method_exists($attachments[0]->item, 'comments') ) {
          return $attachments[0]->item->likes();
          break;
        }
      // Comments linked to action item
      default: case 0: case 1:
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('likes', 'activity'));
        break;
    }

    throw new Activity_Model_Exception('Likes handler undefined');
  }

  public function dislikes()
  {
    $commentable = $this->getTypeInfoCommentable();

    switch( $commentable ) {
      // Comments linked to subject
      case 2:
        return Engine_Api::_()->getDbtable('dislikes', 'nestedcomment')->dislikes($this->getSubject());
        break;

      // Comments linked to object
      case 3:
        return Engine_Api::_()->getDbtable('dislikes', 'nestedcomment')->dislikes($this->getObject());
        ;
        break;

      // Comments linked to the first attachment
      case 4:
        $attachments = $this->getAttachments();
        if( isset($attachments[0]) ) {
          return Engine_Api::_()->getDbtable('dislikes', 'nestedcomment')->dislikes($attachments[0]->item);
          break;
        }

      // Comments linked to action item
      default: case 0: case 1:
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('dislikes', 'nestedcomment'));
        break;
    }

    throw new Activity_Model_Exception('Dislikes handler undefined');
  }

  public function getCommentOnAttachmentType()
  {
    return array('post', 'tagged', 'post_self',
      'post_self_photo', 'post_self_video', 'post_self_music', 'post_self_link',
      'user_cover_update', 'profile_photo_update',
      'list_change_photo', 'recipe_change_photo',
      'sitetagcheckin_post_self', 'sitetagcheckin_post',
      'sitetagcheckin_tagged_new', 'sitepage_admin_cover_update',
      'sitepage_cover_update', 'sitepage_profile_photo_update',
      'sitebusiness_profile_photo_update', 'sitebusiness_admin_cover_update',
      'sitebusiness_cover_update', 'sitegroup_admin_cover_update',
      'sitegroup_cover_update', 'sitegroup_profile_photo_update',
      'sitegroup_post', 'sitepage_post', 'sitebusiness_post', 'sitegroup_post_self',
      'sitebusiness_post_self', 'sitepage_post_self',
      'sitestoreproduct_admin_new', 'sitestoreproduct_new',
      'siteevent_post', 'siteevent_post_parent', 'siteevent_change_photo_parent',
      'siteevent_change_photo', 'siteeventdocument_new_parent', 'siteeventdocument_new',
      'siteevent_cover_update_parent', 'siteevent_cover_update', 'siteevent_topic_create',
      'siteevent_topic_create_parent', 'siteevent_video_new_parent', 'siteevent_video_new',
      'siteevent_topic_reply', 'siteevent_topic_reply_parent', 'video_siteevent_parent',
      'video_siteevent');
  }

  public function postAgent()
  {
    if( strpos($this->user_agent, 'SEAOSEAPP') !== false )
      $isApp = true;
    else
      $isApp = false;
    if( !isset($this->user_agent) || !$this->user_agent )
      return;
    $useragent = strtolower($this->user_agent);
    // Windows is (generally) not a mobile OS
    if( false !== stripos($useragent, 'windows') &&
      false !== stripos($useragent, 'windows phone') ) {
      return 'windows phone';
    }

    if( false !== stripos($useragent, 'blackberry') ) {
      return 'blackberry';
    }

    if( false !== stripos($useragent, 'iphone') ) {
      if( $isApp )
        return 'iOS App';
      else
        return 'iPhone';
    }
    if( false !== stripos($useragent, 'ipod') ) {
      if( $isApp )
        return 'iOS App';
      else
        return 'iPod';
    }
    if( false !== stripos($useragent, 'ipad') ) {
      if( $isApp )
        return 'iOS App';
      else
        return 'iPad';
    }

    $mobileEnable = false;
    if
    (
      preg_match('/imageuploader|android|compal|fennec|hiptop|iemobile/i', $useragent) ||
      preg_match('/kindle|lge|maemo|midp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\//i', $useragent) ||
      preg_match('/pocket|psp|symbian|treo|up\.(browser|link)|vodafone|windows (ce|phone)|xda/i', $useragent)
    )
      $mobileEnable = true;
    if( !$mobileEnable && (preg_match('/avantgo|blazer|compal|elaine|fennec|hiptop|iemobile|iris|kindle|lge |maemo|midp|mmp|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-
w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))) )
      $mobileEnable = true;

    if( $mobileEnable ) {
      if( $isApp )
        return 'Android App';
      else
        return 'mobile';
    }
  }

  public function getReplies($comment_id, $replyViewAll = false, $showCount = false)
  {

    $comments = $this->comments();
    $table = $comments->getReceiver();
    $select = $comments->getCommentSelect();
    $select->where('parent_comment_id =?', $comment_id);

    if( !Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode') ) {
      $reverseOrder = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.commentreverseorder', false);

      $reply_count = count($select->query()->fetchAll());
      if( $showCount ) {
        return $reply_count;
      }

      if( $reply_count <= 5 ) {
        $select->limit(5);
      } else if( !$replyViewAll ) {
        if( $reverseOrder )
          $select->limit(5);
        else
          $select->limit(5, $reply_count - 5);
      }
    }
    $select->reset('order');
    $select->order(array('comment_id ASC'));
    return $table->fetchAll($select);
  }

  public function getComments($commentViewAll, $showCount = false)
  {
    if( null !== $this->_comments ) {
      return $this->_comments;
    }

    $comments = $this->comments();
    $table = $comments->getReceiver();
    $comment_count = $comments->getCommentCount();

    if( $comment_count <= 0 ) {
      return;
    }

    $reverseOrder = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.commentreverseorder', false);

    // Always just get the last three comments
    $select = $comments->getCommentSelect();

    if( Engine_Api::_()->seaocore()->checkEnabledNestedComment('advancedactivity') ) {
      $select->where('parent_comment_id =?', 0);
      $comment_count = count($select->query()->fetchAll());
      if( $showCount ) {
        return $comment_count;
      }
      if( $comment_count <= 5 ) {
        $select->limit(5);
      } else if( !$commentViewAll ) {
        if( $reverseOrder )
          $select->limit(5);
        else
          $select->limit(5, $comment_count - 5);
      }
    } else {
      if( $showCount ) {
        return $comment_count;
      }

      if( $comment_count <= 5 ) {
        $select->limit(5);
      } else if( !$commentViewAll ) {
        if( $reverseOrder )
          $select->limit(5);
        else
          $select->limit(5, $comment_count - 5);
      }
    }
    return $this->_comments = $table->fetchAll($select);
  }

  public function canMakeSimilar()
  {
    $typeInfo = $this->getTypeInfo();
    return ( in_array($typeInfo->commentable, $this->_allowCommentableForSimilar) && in_array($typeInfo->shareable, $this->_allowShareableForSimilar) );
  }

  public function getShareableItem()
  {
    $shareable = (int) $this->getTypeInfo()->shareable;
    if( !$shareable ) {
      return;
    }
    if( $shareable === 2 ) {
      return $this->getSubject();
    }
    if( $shareable === 3 ) {
      return $this->getObject();
    }
    if( $shareable === 4 || $this->getTypeInfo()->type === 'status' ) {
      return $this;
    }

    if( $shareable === 1 && $this->attachment_count === 1 && $this->getFirstAttachment() ) {
      if( in_array($this->getFirstAttachment()->item->getType(), array("sitereaction_sticker", "sitegifplayer_photo")) ) {
        return $this;
      }
      return $this->getFirstAttachment()->item;
    }
    if( $shareable === 1 && $this->attachment_count > 1 ) {
      return $this;
    }
  }

}
