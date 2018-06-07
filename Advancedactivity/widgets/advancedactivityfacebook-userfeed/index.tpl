<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php
$this->headTranslate(array('Disconnect from Facebook', 'Your Facebook status could not be updated. Please try again.', 'What\'s on your mind?', 'Write a comment...', 'Unlike', 'Like this item', 'Like', 'You need to be logged into Facebook to see your Facebook News Feed.'));
?>
<script type="text/javascript"> 
  update_freq_fb = <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.update.frequency', 120000); ?>;
</script>


<?php if (empty($this->isajax) && empty($this->checkUpdate)) : ?>

  <?php if ($this->session_id) { ?>

    <!--THIS DIV SHOWS ALL RECENT POSTS.-->

    <div id="feed-update-fb">
    </div>
    <script type='text/javascript'>
      action_logout_taken_fb = 0;

    </script>

  <?php } else { ?>
    <div class="white">
      <?php if (!empty($this->loginUrl)) {
        echo '<div class="aaf_feed_tip">' . $this->translate('You need to be logged into Facebook to see your Facebook News Feed.') . ' <a href="javascript:void(0);" onclick= "AAF_ShowFeedDialogue_FB(\'' . $this->loginUrl . '\')" >' . $this->translate('Click here') . '</a>.</div>'; ?>
        <script type='text/javascript'>
          action_logout_taken_fb = 1;
      						
        </script>

      <?php } ?>
    </div>

    <?php return;
  } ?>

  <ul id="FB_activity-feed" class="feed">
  <?php endif; ?>

  <?php
  $execute_script = 1;
//MAKING THE FACEBOOK FEED HTML
  if (!empty($this->logged_userfeed) && count($this->logged_userfeed['data']) && empty($this->checkUpdate)) :
    if (empty($this->getFacebokStream)): return;
    endif;
    foreach ($this->data as $key => $feed) {
      $actions = explode("_", $feed['id']);
      if (empty($actions[0]) && !empty($actions[1]))
        continue;

      if (isset($feed['application']) && isset($feed['application']['name']) && $feed['type'] == 'status' && !isset($feed['actions']))
        continue;
      ?>


      <li>
        <div class="feed_item_photo">
          <a href="https://www.facebook.com/<?php echo $feed['from']['id']; ?>" target="_blank">
            <img src="https://graph.facebook.com/<?php echo $feed['from']['id']; ?>/picture" alt="" />
          </a>
        </div>
        <div class="feed_item_body">
          <div class="feed_item_generated">
            <?php
            //IF TYPE IS QUESTION TYPE THEN WE WILL NOT SHOW THE OPTION FOR LIKE AND COMMENT.
            $story = '';
            if (($feed['type'] == 'question' || @$feed['application']['name'] == 'Questions' || $feed['type'] == 'status') && !empty($feed['story'])) {
              $story_content = explode('asked: ', $feed['story']);
              if (isset($story_content[1]) && !empty($story_content[1])) {
                $story .= '<a href="https://www.facebook.com/' . $feed['from']['id'] . '" target="_blank" class="feed_item_username">' .
                        $feed['from']['name'] . '</a> asked: ' . '<a href="https://www.facebook.com/questions/' . $feed['object_id'] . '" target="_blank" class="feed_item_username">' . $story_content[1] . '</a>';
              } else if ($feed['type'] == 'status') {
                $substring_story = '<a href="https://www.facebook.com/' . $feed['from']['id'] . '" target="_blank" class="feed_item_username">' .
                        $feed['from']['name'] . '</a>';
                $story = str_replace($feed['from']['name'], $substring_story, $feed['story']);
              }
            } else {
              if ($feed['type'] != 'photo' || (($feed['type'] == 'photo') && empty($feed['story']))) {
                $story .= '<a href="https://www.facebook.com/' . $feed['from']['id'] . '" target="_blank" class="feed_item_username">' .
                        $feed['from']['name'] . '</a>';
              }
            }
            if (empty($story) && !empty($feed['story']) && !empty($feed['message'])) {
              $story = $feed['story'];
            }
            echo $story;
            ?>
            <?php
            if (empty($feed['message']) && !empty($feed['story'])) {

              if ($feed['type'] == 'link')
                echo $this->translate('shared') . ' <a href="' . $feed['link'] . '" target="_blank">' . $this->translate('a link') . ' . </a>';
              else if ($feed['type'] == 'photo') {
                $substring_story = '<a href="https://www.facebook.com/' . $feed['from']['id'] . '" target="_blank" class="feed_item_username">' .
                        $feed['from']['name'] . '</a>';
                $feed['story'] = str_replace($feed['from']['name'], $substring_story, $feed['story']);

                echo $feed['story'];
              }
            }
            ?>

          <?php if (!empty($feed['to']) && empty($feed['message_tags'])) : ?>
              <img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Advancedactivity/externals/images/arrow-right.png" alt="" /><?php echo ' <a href="https://www.facebook.com/' . $feed['to']['data'][0]['id'] . '" target="_blank" class="feed_item_username">' . $feed['to']['data'][0]['name'] . '</a>'; ?>
          <?php endif; ?>
          </div> 

            <?php $id_text = 0;

            if (!empty($feed['message'])) { ?>
            <div class="aaf_feed_item_des">
              <?php
              $Message_Length = strlen($feed['message']);

              if ($Message_Length > 200) {
                $message = substr($feed['message'], 0, 200);
                $id_text = $actions[1] . '_' . $key;
                $message = $message . '... <a href="javascript:void(0);" onclick="AAF_showText_More(1, \'' . $id_text . '\');" class="facebooksepage_seemore">' . $this->translate("See More") . '</a>';
              } else {
                $message = $feed['message'];
              }
              ?>

              <p id="fbmessage_text_short_<?php echo $id_text; ?>">
                <?php
                $message = Engine_Api::_()->advancedactivity()->getURLString($message);

                //CHECK IF A CONTENT IS TAGED IN THE MESSAGE OR NOT.

                if (!empty($feed['message_tags']) && !empty($feed['to'])) :
                  //FIND THE FIRST OCCURANCE OF THE TAGED CONTENT
                  foreach ($feed['to']['data'] as $to) :

                    $substring = '<a href="https://www.facebook.com/' . $to['id'] . '" target="_blank" class="feed_item_username">' . $to['name'] . '</a>';
                    $message = str_replace($to['name'], $substring, $message);
                  endforeach;
                endif;
                echo nl2br($message);
                ?>
              </p>
              <div id="fbmessage_text_full_<?php echo $id_text; ?>" style="display:none;">
                <?php
                $feed['message'] = Engine_Api::_()->advancedactivity()->getURLString($feed['message']);
                //CHECK IF A CONTENT IS TAGED IN THE MESSAGE OR NOT.

                if (!empty($feed['message_tags']) && !empty($feed['to'])) :
                  //FIND THE FIRST OCCURANCE OF THE TAGED CONTENT
                  foreach ($feed['to']['data'] as $to) :

                    $substring = '<a href="https://www.facebook.com/' . $to['id'] . '" target="_blank" class="feed_item_username">' . $to['name'] . '</a>';
                    $feed['message'] = str_replace($to['name'], $substring, $feed['message']);
                  endforeach;
                endif;

                echo nl2br($feed['message']);
                ?>
              </div>
            </div>
                  <?php } ?>

    <?php if (!empty($feed['picture']) || !empty($feed['name']) || !empty($feed['caption']) || !empty($feed['description']) || !empty($feed['properties'])) : ?>
            <div class="feed_item_attachments">
              <span class="aaf_feed_attachment_facebook">
                <div>
                      <?php if (!empty($feed['picture']) && !empty($feed['link'])) : ?>
                    <a href="<?php echo $feed['link']; ?>" target="_blank" <?php if ($feed['type'] == 'photo'): ?> class="aaf_feed_attachment_facebook_photo" <?php endif; ?> ><img src="<?php echo $feed['picture']; ?>" alt="" /></a> 
                        <?php endif; ?>
                  <div>   	     
                        <?php if (!empty($feed['name']) && !empty($feed['link'])) : ?>
                      <div class="feed_item_link_title">
                        <a href="<?php echo $feed['link']; ?>" target="_blank" class="title"><?php echo $feed['name']; ?></a> 
                      </div>  
                        <?php endif; ?>
                    <div class="feed_item_link_desc">
                        <?php if (!empty($feed['caption'])) : ?>
                        <span><?php
                  $feed['caption'] = Engine_Api::_()->advancedactivity()->getURLString($feed['caption']);
                  $caption_Length = strlen($feed['caption']);
                  if ($caption_Length > 200) {
                    $caption = substr($feed['caption'], 0, 200);
                    $id_text = 'caption_' . $actions[1] . '_' . $key;
                    $caption = $caption . '... <a href="javascript:void(0);" onclick="AAF_showText_More(2, \'' . $id_text . '\');" class="facebooksepage_seemore">' . $this->translate("See More") . '</a>';
                  } else {
                    $caption = $feed['caption'];
                  }
                          ?> 
                          <div id="fbdescript_text_short_<?php echo $id_text; ?>">
                        <?php
                        $caption = Engine_Api::_()->advancedactivity()->getURLString($caption);
                        echo nl2br($caption);
                        ?>
                          </div>
                          <div id="fbdescript_text_full_<?php echo $id_text; ?>" style="display:none;">
                        <?php
                        $feed['caption'] = Engine_Api::_()->advancedactivity()->getURLString($feed['caption']);
                        echo nl2br($feed['caption']);
                        ?>
                          </div>       	
                        </span> 
                        <?php endif; ?>
                        <?php
                        if (!empty($feed['description'])) :
                          $Description_Length = strlen($feed['description']);
                          if ($Description_Length > 200) {
                            $description = substr($feed['description'], 0, 200);
                            $id_text = $actions[1] . '_' . $key;
                            $description = $description . '... <a href="javascript:void(0);" onclick="AAF_showText_More(2, \'' . $id_text . '\');" class="facebooksepage_seemore">' . $this->translate("See More") . '</a>';
                          } else {
                            $description = $feed['description'];
                          }
                          ?> 
                        <div id="fbdescript_text_short_<?php echo $id_text; ?>">
                        <?php
                        $description = Engine_Api::_()->advancedactivity()->getURLString($description);
                        echo nl2br($description);
                        ?>
                        </div>
                        <div id="fbdescript_text_full_<?php echo $id_text; ?>" style="display:none;">
        <?php
        $feed['description'] = Engine_Api::_()->advancedactivity()->getURLString($feed['description']);
        echo nl2br($feed['description']);
        ?>
                        </div>
      <?php endif; ?>
      <?php if (!empty($feed['properties']) && isset($feed['properties'][0]) && isset($feed['properties'][0]['name'])) : ?>

                <?php
                if (!empty($feed['properties'][0]['href']))
                  echo '<br /> &#160 ' . $feed['properties'][0]['name'] . ' :<a href="' . $feed['properties'][0]['href'] . '" target="_blank"> ' . $feed['properties'][0]['text'] . '</a><br />';
                else
                  echo '<br /> &#160 ' . $feed['properties'][0]['name'] . ' : ' . $feed['properties'][0]['text'] . '<br />';
                ?>
              <?php endif; ?>
                    </div>	
                  </div>
                </div>
              </span>
            </div>
            <?php endif; ?>

          <div class="aaf_feed_item_stats" >

            <?php
            //SHOWING THE NO OF LIKES.............//
            if ($feed['type'] != 'question' && @$feed['application']['name'] != 'Questions') :
              $current_user_like = 0;
              $FB_action = 'post';
              $like_unlike = $this->translate('Like');
              if (!empty($feed['likes']['count'])) :
                $post_count = $feed['likes']['count'];
                if (!empty($feed['likes']['data'])) :
                  foreach ($feed['likes']['data'] as $like_uid) {
                    if ($like_uid['id'] == $this->FBuid) :
                      $current_user_like = 1;
                      $FB_action = 'delete';
                      $like_unlike = $this->translate('Unlike');
                    else :

                      continue;
                    endif;
                  }
                endif;
              else:
                $post_count = 0;
              endif;
              ?>

              <?php
              if ($feed['type'] == 'status')
                $post_url = "https://www.facebook.com/" . $actions[1];
              else
                $post_url = "https://www.facebook.com/" . $actions[0] . "/posts/" . $actions[1];
              $feed_icon = '';
              if (!empty($feed['icon'])) {
                $feed_icon = '<span><img src="' . @$feed['icon'] . '" alt="" class="fleft" /></span>';
              }

              if ($feed['type'] == 'photo' || $feed['type'] == 'link' || $feed['type'] == 'note' || $feed['type'] == 'status' || $feed['type'] == 'music' || $feed['type'] == 'video' || 1) {
                $like_html = '<a href="' . $post_url . '" target="_blank" title="' . $this->translate('Like this item') . '">';
                $comment_html = '<a href="' . $post_url . '" target="_blank" title="' . $this->translate('Leave a comment') . '">';
                $showhide_likecomment_html = '<a href="' . $post_url . '" target="_blank" >';
                $showhide_likecomment_html_span = '<span class="colibox" title="' . $this->translate('Show/Hide comments and likes') . '" >' . $showhide_likecomment_html;

                $show_hide_temp = '</a>';
              } else {
                $like_html = '<a href="javascript:void(0);" onclick="Post_Like(\'' . $actions[1] . '\',\'' . $FB_action . '\',' . @$post_count . ',\'' . $post_url . '\'' . ' )" title="' . $this->translate('Like this item') . '">';
                $comment_html = '<a href="javascript:void(0);" onclick="Post_Comment_focus(\'' . $actions[1] . '\')" title="' . $this->translate('Leave a comment') . '">';
                $showhide_likecomment_html_span = '<span class="colibox" title="' . $this->translate('Show/Hide comments and likes') . '" onclick="toggle_likecommentbox(\'' . $actions[1] . '\');">';
                $show_hide_temp = '';
              }


              echo $feed_icon . '<span id="FB_Likes_' . $actions[1] . '">' .
              $like_html . $this->translate('%s', $like_unlike) . '</a>
  		 				</span>	
  		 				<span>&#183;</span>
  		 				<span>' .
              $comment_html . $this->translate('Comment') . '</a>
  		 				</span>';
              ?>


              <?php $like_comment = 0; ?>
              <?php
              if (!empty($feed['comments']) && !empty($feed['comments']['count'])) :
                $like_comment = 1;

                echo '<span>&#183;</span>' . $showhide_likecomment_html_span . '<i class="aaf_feed_fb_comment_icon aaf_feed_fb_icon"></i><span>' . $feed['comments']['count'] . '</span>';

              endif;
              ?>

              <?php
              if (!empty($feed['likes']) && !empty($feed['likes']['count'])) :

                if ($like_comment == 0) {

                  echo '<span>&#183;</span>' . $showhide_likecomment_html_span;
                }
                $like_comment = 2;
                echo '<i class="aaf_feed_fb_like_icon aaf_feed_fb_icon"></i><span id="count_fblike_' . $actions[1] . '">' . $feed['likes']['count'] . '</span>';
              endif;
              ?>

              <?php
              if ($like_comment == 1) :
                echo $show_hide_temp . '</span>';
              elseif ($like_comment == 2):
                echo $show_hide_temp . '</span>';

              endif;

              echo '<span>&#183;</span>';
              ?>

    <?php
    else:
      $feed_icon = '';
      if (!empty($feed['icon'])) {
        $feed_icon = '<span><img src="' . @$feed['icon'] . '" alt="" class="fleft" /></span>';
      }

      if (!empty($feed['object_id'])):
        echo $feed_icon . '<span>
  		 					<a href="https://www.facebook.com/questions/' . $feed['object_id'] . '" target="_blank" title="' . $this->translate('Ask specific people to answer') . '">' . $this->translate('Ask Friends') . '</a>
  		 				</span>	
  		 				<span>&#183;</span>';
        ?>
                <?php endif; ?>


                <?php endif; ?>


                <?php echo '<span><a href="https://www.facebook.com/' . $actions[0] . '/posts/' . $actions[1] . '" target="_blank" class="timesep">' . $this->timestamp(strtotime($feed['created_time'])) . '</a></span>'; ?>

          </div>



                <?php if ($feed['type'] != 'question') : ?>	
            <div class="comments aaf_feed_comment_commentbox" id="fbcomments_<?php echo $actions[1]; ?>" <?php if (empty($feed['likes']['count']) && empty($feed['comments']['count'])): ?> style="display:none;" <?php else : ?> style="display:block;" <?php endif; ?>>
              <ul id="postcomment_fb-<?php echo $actions[1]; ?>">

                  <?php //SHOWING THE NO OF LIKES.............//

                  if (!empty($feed['likes']['count'])) : ?>
                  <li class="aaf_feed_comment_likes_count">
                    <i class="aaf_feed_fb_like_icon aaf_feed_fb_icon"></i>
                    <?php
                    if ($current_user_like == 0) :
                      $like_people_total = (!empty($feed['object_id'])) ? $feed['object_id'] : $actions[1];
                      $like_people = '<a href="https://www.facebook.com/browse/likes/?id=' . $like_people_total . '" target="_blank" title="' . $this->translate('See people who like this item') . '">' . $feed['likes']['count'] . '  ' . $this->translate('people') . '</a>';
                      echo '<div id="FB_LikesCount_' . $actions[1] . '">' . $like_people . ' ' . $this->translate('like this') . '</div>';

                    else :
                      if ($feed['likes']['count'] > 1) :
                        $like_people_total = (isset($feed['object_id'])) ? $feed['object_id'] : $actions[1];
                        $like_people = '<a href="https://www.facebook.com/browse/likes/?id=' . $like_people_total . '" target="_blank" title="' . $this->translate('See people who like this item') . '">' . --$feed['likes']['count'] . '  ' . $this->translate('others') . '</a>';
                        echo '<div id="FB_LikesCount_' . $actions[1] . '">' . $this->translate('You and %s like this.', $like_people) . ' </div>';
                      else :
                        echo '<div id="FB_LikesCount_' . $actions[1] . '">' . $this->translate('You like this.') . '</div>';
                      endif;
                    endif;
                    ?>

                  <?php endif;
                  if (!empty($feed['likes']['count'])) : ?>
                  </li>
                  <?php endif; ?>



                  <?php if ((!empty($feed['comments']['count']) && count(@$feed['comments']['data']) != @$feed['comments']['count'] ) || !empty($feed['shares']['count'])): ?>	
                  <li class="aaf_feed_comment_likes_count">
                  <?php
                  //SHOWING ALL THE COMMENTS COMING FROM FACEBOOK..............//
                  if (!empty($feed['comments']['count'])) :
                    if (!empty($feed['comments']['data']) && count($feed['comments']['data']) != @$feed['comments']['count']) :

                      echo '<div><a href="https://www.facebook.com/' . $actions[0] . '/posts/' . $actions[1] . '" target="_blank"><i class="aaf_feed_fb_comment_icon aaf_feed_fb_icon"></i>' . $this->translate('View all %s Comments', $feed['comments']['count']) . '</a></div>';
                    endif;
                  endif;
                  ?>

        <?php
        //SHOWING ALL THE SHARE COUNTS COMING FROM FACEBOOK..............//
        if (!empty($feed['shares']['count'])) :
          $shares = (!empty($feed['object_id'])) ? $feed['object_id'] : $actions[1];
          echo '<div><a href="https://www.facebook.com/shares/view?id=' . $shares . '" target="_blank"><i class="aaf_feed_fb_share_icon aaf_feed_fb_icon"></i>' . $this->translate('%s shares', $feed['shares']['count']) . '</a></div>';
        endif;
        ?>
                  </li>
                      <?php endif; ?>
                      <?php
                      if (!empty($feed['comments']['data'])) :
                        foreach ($feed['comments']['data'] as $comment) :
                          ?>
                    <li>
                      <div class="comments_author_photo">
                        <a href="https://www.facebook.com/<?php echo $comment['from']['id']; ?>" target="_blank">
                          <img src="https://graph.facebook.com/<?php echo $comment['from']['id']; ?>/picture" alt="" class="thumb_icon item_photo_user" />
                        </a>
                      </div>
                      <div class="comments_info">
                        <span class="comments_author">
                          <a href="https://www.facebook.com/<?php echo $comment['from']['id']; ?>" target="_blank">
                            <?php echo $comment['from']['name']; ?> 
                          </a>
                        </span> 
                        <span id='comment_message'>
                            <?php if (!empty($comment['message'])) { ?>
                              <?php
                              $Message_Length = strlen($comment['message']);

                              if ($Message_Length > 250) {
                                $message = substr($comment['message'], 0, 250);
                                $id_text = 'postcomment_id_' . $comment['id'];
                                $message = $message . '... <a href="javascript:void(0);" onclick="AAF_showText_More(1, \'' . $id_text . '\');" class="facebooksepage_seemore">' . $this->translate("See More") . '</a>';
                              } else {
                                $message = $comment['message'];
                              }
                              ?>

                            <p id="fbmessage_text_short_<?php echo $id_text; ?>">
                              <?php
                              $message = Engine_Api::_()->advancedactivity()->getURLString($message);

                              //CHECK IF A CONTENT IS TAGED IN THE MESSAGE OR NOT.

                              if (!empty($comment['message_tags'])) :
                                //FIND THE FIRST OCCURANCE OF THE TAGED CONTENT
                                foreach ($comment['message_tags'] as $message_tags) :

                                  $substring = '<a href="https://www.facebook.com/' . $message_tags['id'] . '" target="_blank" class="feed_item_username">' . $message_tags['name'] . '</a>';
                                  $message = str_replace($message_tags['name'], $substring, $message);
                                endforeach;
                              endif;
                              echo nl2br($message);
                              ?>
                            </p>
                            <div id="fbmessage_text_full_<?php echo $id_text; ?>" style="display:none;">
                      <?php
                      $comment['message'] = Engine_Api::_()->advancedactivity()->getURLString($comment['message']);

                      //CHECK IF A CONTENT IS TAGED IN THE MESSAGE OR NOT.

                      if (!empty($comment['message_tags'])) :
                        //FIND THE FIRST OCCURANCE OF THE TAGED CONTENT
                        foreach ($comment['message_tags'] as $message_tags) :

                          $substring = '<a href="https://www.facebook.com/' . $message_tags['id'] . '" target="_blank" class="feed_item_username">' . $message_tags['name'] . '</a>';
                          $comment['message'] = str_replace($message_tags['name'], $substring, $comment['message']);
                        endforeach;
                      endif;
                      echo nl2br($comment['message']);
                      ?>
                            </div>
          <?php } ?>

                        </span>
                      </div>
                    </li>
        <?php
        endforeach;
      endif;
      ?>
              </ul>
              <form action=""  id="CommentonPost_<?php echo $actions[1]; ?>" style="display:none;" class="aaf_fb_comment">
                <div class="comments_author_photo" id="fbcomments_author_photo-<?php echo $actions[1]; ?>" style="display:none;"><img src="https://graph.facebook.com/<?php echo $this->FBuid; ?>/picture" alt="" width="32" id="fbuser_picture-<?php echo $actions[1]; ?>"  /></div><div class="comments_info"><textarea title="<?php echo $this->translate("Write a comment...") ?>" rows="1" cols="45" id="FBCommentonPost_submit-<?php echo $actions[1]; ?>" name="body" style="overflow-x: auto; overflow-y: hidden; resize: none; padding-bottom: 0px; padding-top: 4px; padding-left: 4px; height: 19px; max-height: 19px;" onfocus="showHideCommentbox($(this), '<?php echo $actions[1]; ?>', 1);" onblur="showHideCommentbox($(this), '<?php echo $actions[1]; ?>', 2);" class="aaf_color_light"><?php echo $this->translate('Write a comment...'); ?> </textarea>
                  <button type="submit" id="FB_activity-comment-submit-<?php echo $actions[1]; ?>" name="submit" style="display: none;" onclick="post_comment_onfb('<?php echo $actions[1]; ?>', 'post', '<?php echo $post_url; ?>');return false;"><?php echo $this->translate('Post Comment'); ?></button></div>
              </form>
            </div>
    <?php endif; ?> 
        </div>
      </li>
      <script type="text/javascript">
        (function(){
            
          en4.core.runonce.add(function(){
            $("FBCommentonPost_submit-<?php echo $actions[1]; ?>").autogrow();
              
          });
        })();
      </script> 

  <?php } ?>

    <script type="text/javascript">

    <?php
    if (!empty($this->paging['next']) && empty($this->getUpdate)) :

      $last_fbid = explode("&until=", $this->paging['next']);
      //$last_fbid = explode("&",$last_fbid[1]);
      ?>
        //window.onscroll = doOnScrollLoadActivity;
        url_param_time_until = "<?php echo $last_fbid[1]; ?>";
        lastOldFB = "<?php echo $last_fbid[1]; ?>";
      
    <?php else: ?>
          feed_no_more_fb.style.display = 'block';
    <?php endif; ?>
    
    <?php
    if (!empty($this->paging['previous']) && !empty($this->changefirstid)) :
      $first_fbid = explode("&since=", $this->paging['previous']);
      $first_fbid = explode("&", $first_fbid[1]);
      ?>
        url_param_time_since = "<?php echo $first_fbid[0]; ?>";
        firstfeedid_fb = "<?php echo $first_fbid[0]; ?>";

    <?php endif; ?>

    </script>
  <?php
  else:
    if (empty($this->next_previous) && empty($this->checkUpdate) && empty($this->getUpdate) && $this->changefirstid == 1) :
      $execute_script = 0;
      echo '<div class="aaf_feed_tip">' . $this->translate('No Feed items to display. Try') . ' <a href="javascript:void(0);" onclick= "AAF_ShowFeedDialogue_FB(\'' . $this->loginUrl . '\')" >' . $this->translate('refreshing') . '</a></div>';

    endif;
  endif;
  ?>

<?php
if (!empty($this->checkUpdate)): // if this is for the live update
  if ($this->Facebook_FeedCount)
    echo "<script type='text/javascript'>
          
          if($('update_advfeed_fbblink') && activity_type != 3)
           $('update_advfeed_fbblink').style.display = 'block';
           activityUpdateHandler_FB.options.last_id =" . $this->last_current_id . ";
        </script>

        <div class='aaf_feed_tip_more'>
          <a href='javascript:void(0);' onclick='javascript:activityUpdateHandler_FB.getFeedUpdate(activityUpdateHandler_FB.options.last_id);'>
          	<i class='aaf_feed_more_arrow'></i><b>{$this->translate(array('%d new update is available - click this to show it.', '%d new updates are available - click this to show them.', $this->Facebook_FeedCount), $this->Facebook_FeedCount)}
          </b></a>
        </div>";
  return; // Do no render the rest of the script in this mode
endif;
?>

<?php if (!empty($this->getUpdate) || empty($this->next_previous)): // if this is for the get live update  ?>
    <script type="text/javascript">
      if (typeof activityUpdateHandler_FB != 'undefined' && $type (activityUpdateHandler_FB) && url_param_time_since != '')
        activityUpdateHandler_FB.options.last_id = url_param_time_since;
    </script>
  <?php endif; ?>
  <?php if (empty($this->isajax) && empty($this->checkUpdate)) : ?>
  </ul>
  <script type="text/javascript">

    window.addEvent('domready', function ()  { 
    
      if (typeof activityUpdateHandler_FB == 'undefined' || !$type(activityUpdateHandler_FB))
      //Call_fbcheckUpdate ();
        
      if ($$('.adv_post_add_user')) 
        $$('.adv_post_add_user').setStyle('display', 'none');
    if ($('emoticons-button'))
      $('emoticons-button').setStyle('display', 'none');      
        
    if ($('composer_twitter_toggle')) {
      $('composer_twitter_toggle').style.display = 'none';
    }
    if ($('composer_facebook_toggle')) {
      $('composer_facebook_toggle').style.display = 'none';
    }    

  });

  var userfeed_url = en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>;
  </script>

  <div class="seaocore_view_more" id="feed_viewmore_fb" style="display: none;">
  <?php
  echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array(
      'id' => 'feed_viewmore_fb_link',
      'class' => 'buttonlink icon_viewmore',
  ))
  ?>
  </div>



  <div id="feed_loading_fb" style="display: none;" class="aaf_feed_loading">
    <img src='<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/core/loading.gif' alt="Loading" />
  <?php echo $this->translate("Loading ...") ?>
  </div>
  <div class="aaf_feed_tip" id="feed_no_more_fb" style="display: none;"> 
  <?php echo $this->translate("There are no more posts to show.") ?>
  </div>
<?php endif; ?>	

<script type="text/javascript">

<?php if (empty($this->getUpdate)) : ?>      
   view_morefeed = '<?php echo $this->view_morefeed; ?>';
      
   window.addEvent('domready', function ()  { 
     // if (activity_type == 3) {  
        
  <?php if ($execute_script == 0) : ?>
          feed_loading_fb.style.display = 'none';
  <?php endif; ?>   
          //if (typeof activityUpdateHandler_FB == 'undefined' || !$type(activityUpdateHandler_FB))
          //Call_fbcheckUpdate ();
  <?php if ($execute_script == 1) : ?>
          if( view_morefeed ) {  
            //if(autoScrollFeedAAFEnable) {
            window.onscroll = FB_doOnScrollLoadActivity;
            // }
            
            if ($('feed_viewmore_fb')) {
              feed_viewmore_fb.style.display = 'block';
              feed_loading_fb.style.display = 'none'; 
              feed_view_more_fb_link.removeEvents('click').addEvent('click', function(event){ 
                event.stop();
                facebook_ActivityViewMore();
              });
            }
          } else {
            if(autoScrollFeedAAFEnable)
              window.onscroll="";
            if ($('feed_viewmore_fb')) {
              feed_viewmore_fb.style.display = 'none';
              feed_loading_fb.style.display = 'none';
              feed_no_more_fb.style.display = '';
            }
          }
  <?php endif; ?>   
         // }
       });

<?php endif; ?> 
</script>