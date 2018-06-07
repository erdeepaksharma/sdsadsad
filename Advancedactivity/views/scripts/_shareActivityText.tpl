<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: _shareActivityText.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>
<?php $sharesTable=Engine_Api::_()->getDbtable('shares', 'advancedactivity');?>
<?php if( empty($this->actions) ) {
  echo $this->translate("The action you are looking for does not exist.");
  return;
} else {
   $actions = $this->actions;
} ?>

<?php $this->headScript()
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Activity/externals/scripts/core.js')
         ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/core.js');

$this->videoPlayerJs();

$this->headLink()->appendStylesheet($this->layout()->staticBaseUrl
            . 'application/modules/Seaocore/externals/styles/style_infotooltip.css');

?>
<script type="text/javascript">
  var CommentLikesTooltips;
  en4.core.runonce.add(function() {
     adfShare=1;
    // Add hover event to get likes
    $$('.comments_comment_likes').addEvent('mouseover', function(event) {
      var el = $(event.target);
      if( !el.retrieve('tip-loaded', false) ) {
        el.store('tip-loaded', true);
        el.store('tip:title', '<?php echo  $this->string()->escapeJavascript($this->translate('Loading...')) ?>');
        el.store('tip:text', '');
        var id = el.get('id').match(/\d+/)[0];
        // Load the likes
        var url = '<?php echo $this->url(array('module' => 'activity', 'controller' => 'index', 'action' => 'get-likes'), 'default', true) ?>';
        var req = new Request.JSON({
          url : url,
          data : {
            format : 'json',
            //type : 'core_comment',
            action_id : el.getParent('li').getParent('li').getParent('li').get('id').match(/\d+/)[0],
            comment_id : id
          },
          onComplete : function(responseJSON) {
            el.store('tip:title', responseJSON.body);
            el.store('tip:text', '');
            CommentLikesTooltips.elementEnter(event, el); // Force it to update the text
          }
        });
        req.send();
      }
    });
    // Add tooltips
    CommentLikesTooltips = new Tips($$('.comments_comment_likes'), {
      fixed : true,
      className : 'comments_comment_likes_tips',
      offset : {
        'x' : 20,
        'y' : 10
      }
    });
     // Enable links in comments
    $$('.comments_body').enableLinks();
   if(feedToolTipAAFEnable){
     // Add hover event to get tool-tip
   var show_tool_tip=false;
   var counter_req_pendding=0;
    $$('.sea_add_tooltip_link').addEvent('mouseover', function(event) {  
      var el = $(event.target); 
      ItemTooltips.options.offset.y=el.offsetHeight;
      ItemTooltips.options.showDelay=100;
        if(!el.hasAttribute("rel")){
                  el=el.parentNode;      
           } 
       show_tool_tip=true;
      if( !el.retrieve('tip-loaded', false) ) {
       counter_req_pendding++;
       var resource='';
      if(el.hasAttribute("rel"))
         resource=el.rel;
       if(resource =='')
         return;
      
        el.store('tip-loaded', true);
       el.store('tip:title', '<div class="" style="">'+
 ' <div class="uiOverlay info_tip" style="width: 300px; top: 0px; ">'+
    '<div class="info_tip_content_wrapper" ><div class="info_tip_content"><div class="info_tip_content_loader">'+
  '<img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/core/loading.gif" alt="Loading" /><?php echo $this->translate("Loading ...") ?></div>'+
'</div></div></div></div>'  
);
        el.store('tip:text', '');       
        // Load the likes
        var url = en4.core.baseUrl+'/seaocore/feed/show-tooltip-info';
        el.addEvent('mouseleave',function(){
         show_tool_tip=false;  
        });       
     
        var req = new Request.HTML({
          url : url,
          data : {
          format : 'html',
          'resource':resource
        },
        evalScripts : true,
        onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {          
            el.store('tip:title', '');
            el.store('tip:text', responseHTML);
            ItemTooltips.options.showDelay=0;
            ItemTooltips.elementEnter(event, el); // Force it to update the text 
             counter_req_pendding--;            
              if(!show_tool_tip || counter_req_pendding>0){             
              //ItemTooltips.hide(el);
              ItemTooltips.elementLeave(event,el);
             }
            var tipEl=ItemTooltips.toElement();          
            tipEl.addEvents({
              'mouseenter': function() {
               ItemTooltips.options.canHide = false;
               ItemTooltips.show(el);
              },
              'mouseleave': function() {                
              ItemTooltips.options.canHide = true;
              ItemTooltips.hide(el);                    
              }
            });
           Smoothbox.bind($$(".sea_add_tooltip_link_tips"));
          }
        });
        req.send();
      }
    });
    // Add tooltips
   var window_size = window.getSize()
   var ItemTooltips = new SEATips($$('.sea_add_tooltip_link'), {
      fixed : true,
      title:'',
      className : 'sea_add_tooltip_link_tips',
      hideDelay :0,
      offset : {'x' : 0,'y' : 0},
      windowPadding: {'x':370, 'y':(window_size.y/2)}
    }
    );   
  }
    
 });
</script>

<?php if( !$this->getUpdate ): ?>
<ul class='feed' id="activity-feed">
<?php endif ?>
<?php $advancedactivityCoreApi= Engine_Api::_()->advancedactivity();?> 
<?php
  foreach( $actions as $action ): // (goes to the end of the file)
    try { // prevents a bad feed item from destroying the entire page
      // Moved to controller, but the items are kept in memory, so it shouldn't hurt to double-check
      if( !$action->getTypeInfo()->enabled ) continue;
      if( !$action->getSubject() || !$action->getSubject()->getIdentity() ) continue;
      if( !$action->getObject() || !$action->getObject()->getIdentity() ) continue;
      
      ob_start();
    ?>
  <?php if( !$this->noList ): ?><li id="activity-item-<?php echo $action->action_id ?>"><?php endif; ?>
    <?php $this->commentForm->setActionIdentity($action->action_id) ?>
    <script type="text/javascript">
      (function(){
        var action_id = '<?php echo $action->action_id ?>';
        en4.core.runonce.add(function(){
          $('activity-comment-body-' + action_id).autogrow();
          en4.advancedactivity.attachComment($('activity-comment-form-' + action_id));
        });
      })();
    </script>

    <?php // User's profile photo ?>
    <div class='feed_item_photo'><?php echo $this->htmlLink($action->getSubject()->getHref(),
      $this->itemPhoto($action->getSubject(), 'thumb.icon', $action->getSubject()->getTitle()),
         array('class'=>'sea_add_tooltip_link', 'rel'=>$action->getSubject()->getType().' '.$action->getSubject()->getIdentity())
    ) ?></div>


    <div class='feed_item_body'>
        <?php if( $this->viewer()->getIdentity() && (
            $this->activity_moderate || (
              $this->allow_delete && (
                ('user' == $action->subject_type && $this->viewer()->getIdentity() == $action->subject_id) ||
                ('user' == $action->object_type && $this->viewer()->getIdentity()  == $action->object_id)
              )
            )
        ) ): ?>
        <div class="feed_item_option_delete aaf_feed_delete_btn">             
          <?php echo $this->htmlLink(array(
            'route' => 'default',
            'module' => 'advancedactivity',
            'controller' => 'index',
            'action' => 'delete',
            'action_id' => $action->action_id
         ), $this->translate('X'), array('class' => 'smoothbox','title'=>$this->translate('Delete Post'))) ?>

        </div>
      <?php endif; ?> 
      <?php // Main Content ?>
      <span class="<?php echo ( empty($action->getTypeInfo()->is_generated) ? 'feed_item_posted' : 'feed_item_generated' ) ?>">
        <?php //echo nl2br($action->getContent()) // converting line-breaks to br tags?>
         <?php echo $this->getContent($action); ?>
      </span>
      <?php // Icon, time since, action links ?>
      <?php
        $icon_type = 'activity_icon_'.$action->type;
        list($attachment) = $action->getAttachments();
        if( is_object($attachment) && $action->attachment_count > 0 && $attachment->item ):
          $icon_type .= ' item_icon_'.$attachment->item->getType() . ' ';
        endif;
        $canComment = ( $action->getTypeInfo()->commentable && $action->commentable &&
            $this->viewer()->getIdentity() &&
            Engine_Api::_()->authorization()->isAllowed($action->getCommentObject(), null, 'comment') &&
            !empty($this->commentForm) );
      ?>
      <div class='feed_item_date feed_item_icon <?php echo $icon_type ?>'>
        <ul>          
          <?php if( $canComment ): ?>
            <?php if( $action->likes()->isLike($this->viewer()) ): ?>
              <li class="feed_item_option_unlike">              	
                <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Unlike'), array('onclick'=>'javascript:en4.advancedactivity.unlike('.$action->action_id.');')) ?>
                 
              </li>
            <?php else: ?>
              <li class="feed_item_option_like">              	
                <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Like'), array('onclick'=>'javascript:en4.advancedactivity.like('.$action->action_id.');')) ?>
                 
              </li>
            <?php endif; ?>
            <?php if( Engine_Api::_()->getApi('settings', 'core')->core_spam_comment ): // Comments - likes ?>
              <li class="feed_item_option_comment">                
                <?php echo $this->htmlLink(array('route'=>'default','module'=>'activity','controller'=>'index','action'=>'viewcomment','action_id'=>$action->getIdentity(),'format'=>'smoothbox'), $this->translate('Comment'), array(
                  'class'=>'smoothbox',
                )) ?>
               
              </li>
            <?php else: ?>
              <li class="feed_item_option_comment">               
                <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Comment'), array('onclick'=>'document.getElementById("'.$this->commentForm->getAttrib('id').'").style.display = ""; document.getElementById("'.$this->commentForm->submit->getAttrib('id').'").style.display = "block"; document.getElementById("'.$this->commentForm->body->getAttrib('id').'").focus();')) ?>
              
              </li>
            <?php endif; ?>
            <?php if( $this->viewAllComments ): ?>
              <script type="text/javascript">
                en4.core.runonce.add(function() {
                  document.getElementById('<?php echo $this->commentForm->getAttrib('id') ?>').style.display = "";
                  document.getElementById('<?php echo $this->commentForm->submit->getAttrib('id') ?>').style.display = "block";
                  document.getElementById('<?php echo $this->commentForm->body->getAttrib('id') ?>').focus();
                });
              </script>
            <?php endif ?>
          <?php endif; ?>       
             <?php if( $this->viewer()->getIdentity() && (
                    'user' == $action->subject_type && $this->viewer()->getIdentity() == $action->subject_id) &&                  $advancedactivityCoreApi->hasFeedTag($action)                  
                  ): ?>
            <li class="feed_item_option_add_tag">             
              <?php echo $this->htmlLink(array(
                'route' => 'default',
                'module' => 'advancedactivity',
                'controller' => 'feed',
                'action' => 'tag-friend',
                'id' => $action->action_id
              ), $this->translate('Tag Friends'), array('class' => 'smoothbox')) ?>
               
            </li>
          <?php elseif($this->viewer()->getIdentity() && $advancedactivityCoreApi->hasMemberTagged($action, $this->viewer())): ?>  
             <li class="feed_item_option_remove_tag">             
              <?php echo $this->htmlLink(array(
                'route' => 'default',
                'module' => 'advancedactivity',
                'controller' => 'feed',
                'action' => 'remove-tag',
                'id' => $action->action_id
              ), $this->translate('Remove Tag'), array('class' => 'smoothbox')) ?>
               
            </li>
          <?php endif; ?>
            <li>
            <?php echo $this->timestamp($action->getTimeValue()) ?>
            </li>
        </ul>
      </div>

      <?php if( $action->getTypeInfo()->commentable && $action->commentable): // Comments - likes ?>
        <div class='comments'>
          <ul>
            <?php if( $action->likes()->getLikeCount() > 0 && (count($action->likes()->getAllLikesUsers())>0) ): ?>
              <li>
                <div></div>
                <div class="comments_likes">
                  <?php if( $action->likes()->getLikeCount() <= 3 || $this->viewAllLikes ): ?>
                    <?php echo $this->translate(array('%s likes this.', '%s like this.', $action->likes()->getLikeCount()), $this->aafFluentList($action->likes()->getAllLikesUsers()) )?>

                  <?php else: ?>
                    <?php echo $this->htmlLink($action->getSubject()->getHref(array('action_id' => $action->action_id, 'show_likes' => true)),
                      $this->translate(array('%s person likes this', '%s people like this', $action->likes()->getLikeCount()), $this->locale()->toNumber($action->likes()->getLikeCount()) )
                    ) ?>
                  <?php endif; ?>
                </div>
              </li>
            <?php endif; ?>
            <?php if( $action->comments()->getCommentCount() > 0 ): ?>
              <?php if(!$this->viewAllComments): ?>
                <li>
                  <div></div>
                  <div class="comments_viewall">
                    <?php if( $action->comments()->getCommentCount() > 4): ?>
                      <?php echo $this->htmlLink($action->getSubject()->getHref(array('action_id' => $action->action_id, 'show_comments' => true)),
                          $this->translate(array('View all %s comment', 'View all %s comments', $action->comments()->getCommentCount()),
                          $this->locale()->toNumber($action->comments()->getCommentCount()))) ?>
                    <?php else: ?>
                      <?php echo $this->htmlLink('javascript:void(0);',
                          $this->translate(array('View all %s comment', 'View all %s comments', $action->comments()->getCommentCount()),
                          $this->locale()->toNumber($action->comments()->getCommentCount())),
                          array('onclick'=>'en4.advancedactivity.viewComments('.$action->action_id.');')) ?>
                    <?php endif; ?>
                  </div>
                </li>
              <?php endif; ?>
               <?php if($this->viewAllComments): ?>  
              <?php foreach( $action->getComments($this->viewAllComments) as $comment ): ?>
                <li id="comment-<?php echo $comment->comment_id ?>">
                   <div class="comments_author_photo">
                      <?php echo $this->htmlLink($this->item($comment->poster_type, $comment->poster_id)->getHref(),
                        $this->itemPhoto($this->item($comment->poster_type, $comment->poster_id), 'thumb.icon', $action->getSubject()->getTitle()),
                              array('class'=>'sea_add_tooltip_link', 'rel'=>$this->item($comment->poster_type, $comment->poster_id)->getType().' '.$this->item($comment->poster_type, $comment->poster_id)->getIdentity())
                      ) ?>
                   </div>
                   <div class="comments_info">
                     <span class='comments_author'>
                       <?php echo $this->htmlLink($this->item($comment->poster_type, $comment->poster_id)->getHref(), $this->item($comment->poster_type, $comment->poster_id)->getTitle(),
                               array('class'=>'sea_add_tooltip_link', 'rel'=>$this->item($comment->poster_type, $comment->poster_id)->getType().' '.$this->item($comment->poster_type, $comment->poster_id)->getIdentity())
                               
                               ); ?>
                     </span>
                     <span class="comments_body">
                        <?php 
                            include APPLICATION_PATH . '/application/modules/Seaocore/views/scripts/_commentBody.tpl';
                        ?>
                     </span>
                     <ul class="comments_date">
                       <li class="comments_timestamp">
                         <?php echo $this->timestamp($comment->creation_date); ?>
                       </li>
                       <?php if ( $this->viewer()->getIdentity() &&
                                 (('user' == $action->subject_type && $this->viewer()->getIdentity() == $action->subject_id) ||
                                ("user"==$comment->poster_type && $this->viewer()->getIdentity() == $comment->poster_id) || 
                                ( "user" !==$comment->poster_type && Engine_Api::_()->getItemByGuid($comment->poster_type . "_" . $comment->poster_id)->isOwner($this->viewer()))||
                                  $this->activity_moderate ) ): ?>
                       <li class="comments_delete">
                         - <?php echo $this->htmlLink(array(
                              'route'=>'default',
                              'module'    => 'activity',
                              'controller'=> 'index',
                              'action'    => 'delete',
                              'action_id' => $action->action_id,
                              'comment_id'=> $comment->comment_id,
                              ), $this->translate('delete'), array('class' => 'smoothbox')) ?>
                       </li>
                        <?php endif; ?>
                        <?php if( $canComment ):
                          $isLiked = $comment->likes()->isLike($this->viewer());
                        ?>
                          <li class="comments_like">
                            -
                            <?php if( !$isLiked ): ?>
                              <a href="javascript:void(0)" onclick="en4.advancedactivity.like(<?php echo sprintf("'%d', %d", $action->getIdentity(), $comment->getIdentity()) ?>)">
                                <?php echo $this->translate('like') ?>
                              </a>
                            <?php else: ?>
                              <a href="javascript:void(0)" onclick="en4.advancedactivity.unlike(<?php echo sprintf("'%d', %d", $action->getIdentity(), $comment->getIdentity()) ?>)">
                                <?php echo $this->translate('unlike') ?>
                              </a>
                            <?php endif ?>
                          </li>
                        <?php endif ?>
                        <?php if( $comment->likes()->getLikeCount() > 0 ): ?>
                          <li class="comments_likes_total">
                            -
                            <a href="javascript:void(0);" id="comments_comment_likes_<?php echo $comment->comment_id ?>" class="comments_comment_likes" title="<?php echo $this->translate('Loading...') ?>">
                              <?php echo $this->translate(array('%s likes this', '%s like this', $comment->likes()->getLikeCount()), $this->locale()->toNumber($comment->likes()->getLikeCount())) ?>
                            </a>
                          </li>
                        <?php endif ?>
                     </ul>
                   </div>
                </li>
              <?php endforeach; ?>
              <?php endif; ?>  
            <?php endif; ?>
          </ul>
          <?php if( $canComment ) echo $this->commentForm->render();  ?>
        </div>
      <?php endif; ?>

    </div>
  <?php if( !$this->noList ): ?></li><?php endif; ?>

<?php
      ob_end_flush();
    } catch (Exception $e) {
      ob_end_clean();
      if( APPLICATION_ENV === 'development' ) {
        echo $e->__toString();
      }
    };
  endforeach;
?>

<?php if( !$this->getUpdate ): ?>
</ul>
<?php endif ?>
