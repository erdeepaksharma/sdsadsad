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

<?php $sessionId = $this->session_id;
$this->headTranslate(array('Disconnect from Twitter', 'Your Tweet was over 140 characters. You\'ll have to be more clever.', 'Tweet', 'Shared on Twitter as reply to', 'We were unable to process your request. Wait a few moments and try again.', 'Updating...', 'Are you sure that you want to delete this tweet? This action cannot be undone.', 'Delete', 'cancel', 'Close', 'You need to be logged into Twitter to see your Twitter tweets.', 'Click here', 'Favorite', 'Unfavorite', 'Retweeted by'));
?>
<?php
$this->headScript()
  ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/core.js')
  ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/advancedactivity-twitter.js');
?>
<script type="text/javascript">
  update_freq_tweet = <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.update.frequency', 120000); ?>;
</script>
<?php if( empty($this->isajax) && empty($this->checkUpdate) && empty($this->getUpdate) ) : ?>
  <?php if( $sessionId ) : ?>
    <?php echo $this->partial('_twitterPostComposer.tpl', 'advancedactivity', array(
      'userImageSrc' => $this->image_CurrentLoggedTweetUser,
      'screenName' => $this->screenname_CurrentLoggedTweetUser
    )); ?>
    <!--THIS DIV SHOWS ALL RECENT POSTS.-->
    <div id="feed-update-tweet"></div>
    <script type='text/javascript'>
      action_logout_taken_tweet = 0;
    </script>

  <?php else : ?>

    <div class="white" style="clear: both;">
      <?php
      if( !empty($this->TwitterLoginURL) ) :
        echo '<div class="aaf_feed_tip">' . $this->translate('You need to be logged into Twitter to see your Twitter tweets.') . ' <a href="javascript:void(0);" onclick= "AAF_ShowFeedDialogue_Tweet()" >' . $this->translate('Click here') . '</a>.</div>';
        ?>
        <script type='text/javascript'>
          action_logout_taken_tweet = 1;
        </script>
      <?php endif; ?>
    </div>

    <?php
    return;
  endif;
  ?>

  <ul id="Tweet_activity-feed" class="feed">
  <?php endif; ?>

  <?php
  if (!empty($this->checkUpdate)): // if this is for the live update
    if ($this->Tweet_count)
      echo "<script type='text/javascript'>

          if($('update_advfeed_tweetblink') && activity_type != 2)
           $('update_advfeed_tweetblink').style.display = 'block';
         </script>
        <div class='aaf_feed_tip_more'>
          <a href='javascript:void(0);' onclick='javascript:activityUpdateHandler_Tweet.getFeedUpdate(" . $this->current_tweet_statusid . ");feedUpdate_tweet.empty();'>
            <i class='aaf_feed_more_arrow'></i><b>{$this->translate(array('%d new update is available - click this to show it.', '%d new updates are available - click this to show them.', $this->Tweet_count), $this->Tweet_count)}
          </b></a>
        </div>";
    return; // Do no render the rest of the script in this mode
  endif;
  ?>

  <?php
  $execute_script = 1;
  $current_tweet_statusid = 0;
  ?>
  <?php
  if( count($this->logged_TwitterUserfeed) ) :

    foreach( $this->logged_TwitterUserfeed as $key => $Twitter ) :
      ?>
      <?php if( !empty($Twitter->retweeted_status) ) : ?>
        <?php
        if( $key == 0 ) :
          $current_tweet_statusid = $Twitter->retweeted_status->id_str;
        endif;
        ?>
        <?php $Screen_Name = $Twitter->retweeted_status->user->screen_name; ?>
        <?php $Tweet_description = Engine_Api::_()->advancedactivity()->getTwitterDescription($Twitter->retweeted_status->text); ?>
        <li id="post_delete_tweet_<?php echo $Twitter->retweeted_status->id_str; ?>">
          <div class="feed_item_photo">
            <a href= "https://twitter.com/<?php echo $Twitter->retweeted_status->user->screen_name; ?>" target="_blank" title="<?php echo $Twitter->retweeted_status->user->name; ?>">
              <img src="<?php echo str_replace("http://", "https://", $Twitter->retweeted_status->user->profile_image_url); ?>" alt="" /> 
            </a>  
          </div>
          <div class="feed_item_body" >
            <span class="feed_item_generated"> 
              <a href= "https://twitter.com/<?php echo $Twitter->retweeted_status->user->screen_name; ?>" target="_blank" title="<?php echo $Twitter->retweeted_status->user->name; ?>" class="feed_item_username">  
                <?php echo $Twitter->retweeted_status->user->screen_name; ?>
              </a>
              <?php echo $Twitter->retweeted_status->user->name; ?>
            </span>
            <div class="aaf_tweet_body">
              <?php
              $Tweet_description = Engine_Api::_()->advancedactivity()->getURLString($Tweet_description);
              echo $Tweet_description;
              ?>
            </div>

            <div class="aaf_tweet_options_row" id="reply_retweet_<?php echo $Twitter->retweeted_status->id_str; ?>">
              <span class="timestamp">
                <?php echo '<a href="https://twitter.com/#!/' . $Twitter->retweeted_status->user->screen_name . '/status/' . $Twitter->retweeted_status->id_str . '" target="_blank">' . $this->timestamp(strtotime($Twitter->retweeted_status->created_at)) . '</a>';
                ?>
              </span>

              <!--MAKING THE TWEET AS MY FAVORITE TWEET-->
              <span id="favorite_tweet_<?php echo $Twitter->retweeted_status->id_str; ?>">
                <?php if( !empty($Twitter->favorited) ) : ?>
                  <a href="javascript:void(0);" onclick="favorite_Tweet('<?php echo $Twitter->retweeted_status->id_str; ?>', '0');" title="Unfavorite" class="aaf_tweet_icon aaf_tweet_icon_unfav"><?php echo $this->translate('Unfavorite') ?></a>
                <?php else: ?>
                  <a href="javascript:void(0);" onclick="favorite_Tweet('<?php echo $Twitter->retweeted_status->id_str; ?>', '1');" title="Favorite" class="aaf_tweet_icon aaf_tweet_icon_fav"><?php echo $this->translate('Favorite') ?></a>
                <?php endif; ?>  
              </span>

              <!--RETWEET THE TWEET-->
              <?php if( ($this->id_CurrentLoggedTweetUser != $Twitter->retweeted_status->user->id_str) && !in_array($Twitter->retweeted_status->id_str, $this->retweets_by_me) ) : ?>
                <span id="retweet_tweet_<?php echo $Twitter->retweeted_status->id_str; ?>" >
                  <a href="javascript:void(0);" class="aaf_tweet_icon_retweet aaf_tweet_icon" onclick="reTweet('<?php echo $Twitter->retweeted_status->id_str; ?>');"><?php echo $this->translate('Retweet') ?></a>
                </span>
              <?php endif; ?>

              <span>
                <a href="javascript:void(0);" onclick="reply_Tweet('<?php echo $Twitter->retweeted_status->id_str; ?>', '<?php echo $this->string()->escapeJavascript($Twitter->retweeted_status->user->screen_name); ?>');" class="aaf_tweet_icon aaf_tweet_icon_reply"><?php echo $this->translate('Reply') ?></a>
              </span>

              <?php if( ($this->id_CurrentLoggedTweetUser == $Twitter->retweeted_status->user->id_str ) ) : ?>
                <span id="delete_tweet_<?php echo $Twitter->retweeted_status->id_str; ?>" class="aaf_tweet_icon_delete aaf_tweet_icon">
                  <a href="javascript:void(0);" onclick="confirm_deletecommenttweet('<?php echo $Twitter->retweeted_status->id_str; ?>');" title="Delete"><?php echo $this->translate('Delete') ?></a>
                </span>
              <?php endif; ?>
            </div>
            <div class="feed_item_date feed_item_icon aaf_tweet_icon_retweeted">
              <?php echo $this->translate('Retweeted by'); ?> <a href= "https://twitter.com/<?php echo $Twitter->user->screen_name; ?>" target="_blank" title="<?php echo $Twitter->user->name; ?>"><?php echo $Twitter->user->screen_name; ?></a>
            </div> 
          </div>
        </li>
      <?php else : ?>
        <?php
        if( $key == 0 ) :
          $current_tweet_statusid = $Twitter->id_str;
        endif;
        ?>     
        <?php $Screen_Name = @$Twitter->retweeted_status->user->screen_name; ?>
        <?php $Tweet_description = Engine_Api::_()->advancedactivity()->getTwitterDescription($Twitter->text); ?>
        <li id="post_delete_tweet_<?php echo $Twitter->id_str; ?>">
          <div class="feed_item_photo"> 
            <a href= "https://twitter.com/<?php echo $Twitter->user->screen_name; ?>" target="_blank" title="<?php echo $Twitter->user->name; ?>">
              <img src="<?php echo str_replace("http://", "https://", $Twitter->user->profile_image_url); ?>" alt="" /> 
            </a>
          </div>
          <div class="feed_item_body" >
            <span class="feed_item_generated">
              <a href= "https://twitter.com/<?php echo $Twitter->user->screen_name; ?>" target="_blank" title="<?php echo $Twitter->user->name; ?>" class="feed_item_username">  
                <?php echo $Twitter->user->screen_name; ?>
              </a>
              <?php echo $Twitter->user->name; ?>
            </span>
            <div class="aaf_tweet_body">
              <?php
              $Tweet_description = Engine_Api::_()->advancedactivity()->getURLString($Tweet_description);
              echo $Tweet_description;
              ?>
            </div>
            <div class="aaf_tweet_options_row" id="reply_retweet_<?php echo $Twitter->id_str; ?>">
              <span class="timestamp"><?php echo '<a href="https://twitter.com/#!/' . $Twitter->user->screen_name . '/status/' . $Twitter->id_str . '" target="_blank">' . $this->timestamp(strtotime($Twitter->created_at)) . '</a>'; ?></span>
              <span id="favorite_tweet_<?php echo $Twitter->id_str; ?>">
                <?php if( !empty($Twitter->favorited) ) : ?>
                  <a href="javascript:void(0);" onclick="favorite_Tweet('<?php echo $Twitter->id_str; ?>', '0');" title="Unfavorite" class="aaf_tweet_icon aaf_tweet_icon_unfav"><?php echo $this->translate('Unfavorite') ?></a>
                <?php else: ?>
                  <a href="javascript:void(0);" onclick="favorite_Tweet('<?php echo $Twitter->id_str; ?>', '1');" title="Favorite" class="aaf_tweet_icon aaf_tweet_icon_fav"><?php echo $this->translate('Favorite') ?></a>
                <?php endif; ?>  
              </span>

              <?php if( ($this->id_CurrentLoggedTweetUser != $Twitter->user->id_str) && !in_array($Twitter->id_str, $this->retweets_by_me) ) : ?>
                <span id="retweet_tweet_<?php echo $Twitter->id_str; ?>">
                  <a href="javascript:void(0);" class="aaf_tweet_icon_retweet aaf_tweet_icon" onclick="reTweet('<?php echo $Twitter->id_str; ?>')"><?php echo $this->translate('Retweet') ?></a>
                </span>
              <?php endif; ?>

              <span>
                <a href="javascript:void(0);" onclick="reply_Tweet('<?php echo $Twitter->id_str; ?>', '<?php echo $this->string()->escapeJavascript($Twitter->user->screen_name); ?>');" class="aaf_tweet_icon aaf_tweet_icon_reply"><?php echo $this->translate('Reply') ?></a>
              </span>

              <?php if( ($this->id_CurrentLoggedTweetUser == $Twitter->user->id_str ) ) : ?>
                <span id="delete_tweet_<?php echo $Twitter->id_str; ?>">
                  <a href="javascript:void(0);" onclick="confirm_deletecommenttweet('<?php echo $Twitter->id_str; ?>');" title="Delete" class="aaf_tweet_icon_delete aaf_tweet_icon"><?php echo $this->translate('Delete') ?></a>
                </span>
              <?php endif; ?> 
            </div>
          </div>
        </li>
      <?php endif; ?>
    <?php endforeach; ?>
    <?php if( empty($this->isajax) && empty($this->checkUpdate) && empty($this->getUpdate) ) : ?>
    </ul>
  <?php endif; ?> 
  <?php
  // MAKING A TEXTAREA HTML FOR REPLYING TO A TWEET:
  if( empty($this->task) && empty($this->getUpdate) ) :
    ?>
    <div style="display:none" id="reply_textarea">
      <ul>
        <li>
          <div class="reply_headingid comments_likes" id='reply_headingid'></div>
        </li>
      </ul>
      <form action="">
        <?php $viewer = Engine_Api::_()->user()->getViewer(); ?>
        <div id="User_Photo" class="comments_author_photo">
          <a href="https://twitter.com/<?php echo $this->screenname_CurrentLoggedTweetUser; ?>" target="_blank" title="<?php echo $this->screenname_CurrentLoggedTweetUser; ?>"> <img src="<?php echo str_replace("http://", "https://", $this->image_CurrentLoggedTweetUser); ?>" width="32" alt="" /></a> 
        </div>
        <div class="comments_info">
          <textarea rows="1" cols="45" id="activity-comment-body-twitter" class="activity-comment-body-twitter" name="body" onKeyDown="limitText($('activity-comment-body-twitter'), 140);"></textarea>
          <button type="submit" id="activity-comment-body-twitter-submit" name="submit" style="display: block;" onclick="post_status();
              return false;"><?php echo $this->translate('Tweet') ?></button>
          <div id="show_loading" class="show_loading"></div>
        </div> 
      </form>
    </div>
  <?php endif; ?>
<?php else: ?>
  <?php
  if( !empty($this->TwitterLoginURL) && empty($this->checkUpdate) && empty($this->getUpdate) ) :
    $execute_script = 0;
    ?>
    <div class="aaf_feed_tip"><?php echo $this->translate('Twitter is currently experiencing technical issues, please try again later.'); ?></div>

  <?php endif; ?>
<?php endif; ?>

<?php if( !empty($this->TwitterLoginURL) ) : ?>
  <script type="text/javascript">
    $('Twitter_activityfeed').href = '<?php echo $this->TwitterLoginURL; ?>';
    $('Twitter_activityfeed').removeEvents('click');
  </script>
<?php endif; ?>

<?php if( empty($this->getUpdate) && !empty($this->lastOldTweet) ) : ?>
  <script type="text/javascript">
    lastOldTweet = <?php echo $this->lastOldTweet; ?>;
  </script>
<?php endif; ?>

<script type="text/javascript">
<?php if( !empty($this->current_tweet_statusid) ) : ?>
    firstfeedid_Tweet = <?php echo $this->current_tweet_statusid ?>;
    if (activityUpdateHandler_Tweet) {
      activityUpdateHandler_Tweet.options.last_id = <?php echo $this->current_tweet_statusid ?>;
    }
<?php endif; ?>

  feedContentURL = en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivitytwitter-userfeed';
  var next_id_twitter = <?php echo sprintf('%d', $this->nextid_twitter) ?>;
  var endOfFeed_twitter = <?php echo sprintf('%d', $this->endOfFeed_twitter); ?>;

  en4.core.runonce.add(function () {

    if ($('feed_viewmore_tweet')) {
      feed_viewmore_tweet = $('feed_viewmore_tweet');
      feed_loading_tweet = $('feed_loading_tweet');
      feed_no_more_tweet = $('feed_no_more_tweet');
      feed_view_more_tweet_link = $('feed_viewmore_tweet_link');
    }

    if (!$type(activityUpdateHandler_Tweet))
      Call_TweetcheckUpdate(firstfeedid_Tweet);

<?php if( $execute_script == 0 ) : ?>
      feed_loading_tweet.style.display = 'none';
      if ($('activity-post-container'))
        $('activity-post-container').style.display = 'none';

<?php endif; ?>

<?php if( $execute_script == 1 ) : ?>
      if (next_id_twitter >= endOfFeed_twitter) {
        //if(autoScrollFeedAAFEnable)
        window.onscroll = Tweet_doOnScrollLoadActivity;
        if (feed_viewmore_tweet) {
          feed_viewmore_tweet.style.display = '';
          feed_loading_tweet.style.display = 'none';

          feed_view_more_tweet_link.removeEvents('click').addEvent('click', function (event) {
            event.stop();
            twitter_ActivityViewMore(lastOldTweet);
          });
        }
      } else {
        if (autoScrollFeedAAFEnable)
          window.onscroll = "";
        if (feed_viewmore_tweet) {
          feed_viewmore_tweet.style.display = 'none';
          feed_loading_tweet.style.display = 'none';

  <?php if( empty($this->TwitterLoginURL) ) : ?>
            feed_no_more_tweet.style.display = '';
            feed_viewmore_tweet.style.display = 'none';
  <?php endif; ?>

        }
      }
<?php endif; ?>


  });


</script>



<?php if( empty($this->isajax) && empty($this->checkUpdate) && empty($this->getUpdate) ) : ?>

  <script type="text/javascript">


    window.addEvent('domready', function () {
      if (!$type(activityUpdateHandler_Tweet))
        Call_TweetcheckUpdate(firstfeedid_Tweet);
      if ($('adv_post_container_icons'))
        $('adv_post_container_icons').setStyle('display', 'none');
      if ($('show_loading_main'))
        $('show_loading_main').style.display = 'block';
      if ($('compose-submit'))
        $('compose-submit').innerHTML = en4.core.language.translate('Tweet');
  //      $('aaf_set_Tweetstatus').autogrow();
  //      $('aaf_set_Tweetstatus').blur();

    });

  //$('aaf_set_Tweetstatus').addEvent('focus', function () { 
  //     if (this.value == "") {
  //    	this.set('class', 'expend_textarea');
  //    	$('aaf_share_Tweetbutton').style.display = 'block';
  //			$('aaf_set_Tweetstatus').focus(); 
  //		}	 
  //     
  //   });  
  </script>
  <div class="seaocore_view_more" id="feed_viewmore_tweet" style="display: none;">
    <?php
    echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array(
      'id' => 'feed_viewmore_tweet_link',
      'class' => 'buttonlink icon_viewmore'
    ))
    ?>
  </div>

  <div id="feed_loading_tweet" style="display: none;" class="aaf_feed_loading">
    <img src='<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/core/loading.gif' alt="Loading" />
    <?php echo $this->translate("Loading ...") ?>
  </div>
  <div class="aaf_feed_tip" id="feed_no_more_tweet" style="display: none;"> 
    <?php echo $this->translate("There are no more posts to show.") ?>
  </div>
<?php endif; ?>