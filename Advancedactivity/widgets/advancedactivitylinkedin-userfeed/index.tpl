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
 $this->headTranslate(array('Disconnect from LinkedIn','We were unable to process your request. Wait a few moments and try again.','Updating...','Are you sure that you want to delete this comment? This action cannot be undone.','Delete','cancel','Close','You need to be logged into LinkedIn to see your LinkedIn Connections Feed.','Click here'));	
 
 ?>
<script type="text/javascript"> 
 update_freq_linkedin = <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.update.frequency', 120000);?>;
</script>

<?php if (empty($this->isajax) && empty($this->checkUpdate) && empty ($this->getUpdate)) : ?>
   
<?php if ($this->Linkedin_FeedCount > 0) { ?>
	
	<!--THIS DIV SHOWS ALL RECENT POSTS.-->
	
	<div id="feed-update-linkedin">
	
	</div>
<script type='text/javascript'>
  action_logout_taken_linkedin = 0;

</script>





<?php } else { ?>
       <div class="white">
          <?php if (!empty($this->LinkedinLoginURL )) { 
		echo '<div class="aaf_feed_tip">'. $this->translate('You need to be logged into LinkedIn to see your LinkedIn Connections Feed.') . ' <a href="javascript:void(0);" onclick= "AAF_ShowFeedDialogue_Linkedin(\''. $this->LinkedinLoginURL. '\')" >' . $this->translate('Click here') . '</a>.</div>'; ?>
						<script type='text/javascript'>
						action_logout_taken_linkedin = 1;
						
						</script>
					
					<?php return;}
          else { ?>
            <div class="aaf_feed_tip"> 
				<?php echo $this->translate("There are no posts to show.") ?>
			</div>
           <?php }
									
							?>
			</div>
		
<?php } ?>

<ul id="Linkedin_activity-feed" class="feed">
<?php endif; ?>

<?php if( !empty($this->checkUpdate) ): // if this is for the live update
  if ($this->Linkedin_FeedCount> 0)
  echo "<script type='text/javascript'>
                
          if($('update_advfeed_linkedinblink') && activity_type != 5)
           $('update_advfeed_linkedinblink').style.display = 'block';
         </script>
        <div class='aaf_feed_tip_more'>
          <a href='javascript:void(0);' onclick='javascript:activityUpdateHandler_Linkedin.getFeedUpdate();feedUpdate_linkedin.empty();'>
            <i class='aaf_feed_more_arrow'></i><b>{$this->translate(array('%d new update is available - click this to show it.', '%d new updates are available - click this to show them.', $this->Linkedin_FeedCount),
              $this->Linkedin_FeedCount)}
          </b></a>
        </div>";
  return; // Do no render the rest of the script in this mode
endif; ?>

<?php $view_moreconnection_linkedin = 0;
$last_linkedin_timestemp = '';

 if (empty($this->isajax) || !empty($this->next_previous) ) : 

   
if ($this->Linkedin_FeedCount > 1)
	$last_linkedin_timestemp = $this->LinkedinFeeds['update'][--$this->Linkedin_FeedCount]['timestamp'];
else
	$last_linkedin_timestemp = $this->LinkedinFeeds['update']['timestamp'];
	$view_moreconnection_linkedin = 1;
	
endif;?>



<?php 
$execute_script = 1;
$current_linkedin_timestemp = 0;  


?>
<?php if ($this->Linkedin_FeedCount> 0) :
    $Api_linkedin = new Seaocore_Api_Linkedin_Api();
    $prev_timestamp = 0;
    foreach ($this->LinkedinFeeds['update'] as $key => $Linkedin) :?>
					<?php 
					    
							if (!isset($this->LinkedinFeeds['update'][0])):
								$Linkedin = $this->LinkedinFeeds['update'];
						    $current_linkedin_timestemp= $Linkedin['timestamp'];?>
                <script type="text/javascript">
                 current_linkedin_timestemp = '<?php echo $current_linkedin_timestemp;?>'
                </script>
						<?php endif; ?>	
        <?php if ($key == 0 && empty($this->next_previous)) :
                $current_linkedin_timestemp= $Linkedin['timestamp'];?>
               
                
         <?php endif; ?>
          <?php //REMOVE THE DUPLICACY OF FEEDS.
              
               if($prev_timestamp == $Linkedin['timestamp'])
                 continue;
               $prev_timestamp = $Linkedin['timestamp'];
          ?>
          
         <?php if (!isset($Linkedin['update-content']['person']) || !isset($Linkedin['update-content']['person']['site-standard-profile-request']['url']))
                continue;?>
         <?php $Screen_Name_connected = '';?>       
        <?php $Screen_Name_connecter = $Linkedin['update-content']['person']['first-name'] . ' ' . $Linkedin['update-content']['person']['last-name']; ?>
        
        <li id="post_delete_linkedin_<?php echo $Linkedin['timestamp']?>">
          <div class="feed_item_photo">
            <a href= "<?php echo $Linkedin['update-content']['person']['site-standard-profile-request']['url'];?>" target="_blank" title="<?php echo $Linkedin['update-content']['person']['headline'];?>">
              
              <img src="<?php echo isset($Linkedin['update-content']['person']['picture-url']) ? $Linkedin['update-content']['person']['picture-url'] : $this->layout()->staticBaseUrl. 'application/modules/User/externals/images/nophoto_user_thumb_icon.png' ;?>" alt="" class="thumb_icon" /> 
            </a>  
          </div>
          <div class="feed_item_body" >
            <span class="feed_item_posted"> 
              <?php if (!isset($Linkedin['update-content']['person']['person-activities']['activity'])) { ?>
              <a href= "<?php echo $Linkedin['update-content']['person']['site-standard-profile-request']['url'];?>" target="_blank" title="<?php echo $Linkedin['update-content']['person']['headline'];?>" class="feed_item_username">  
                <?php echo $Screen_Name_connecter;?>
              </a>
              
              <?php } else {  
              
                   echo nl2br($Linkedin['update-content']['person']['person-activities']['activity']['body']);
                   
                   
              }?>
              
              <?php if (isset($Linkedin['update-content']['person']['connections']) && $Linkedin['update-content']['person']['connections']['@attributes']['total'] > 0) {
               
                     echo ' ' . $this->translate('is now connected to'). ' ';?>
                     
      <a href="<?php echo $Linkedin['update-content']['person']['connections']['person']['site-standard-profile-request']['url'];?>" target="_blank" title="<?php echo $Linkedin['update-content']['person']['connections']['person']['headline'];?>" class="feed_item_username">
                      
                         <?php $Screen_Name_connected = $Linkedin['update-content']['person']['connections']['person']['first-name'] . ' ' . $Linkedin['update-content']['person']['connections']['person']['last-name']; ?>
													<?php echo $Screen_Name_connected;?>
											</a>
											
											<?php echo ',  ' . Engine_Api::_()->advancedactivity()->getURLString($Linkedin['update-content']['person']['connections']['person']['headline']);?>
                    <?php } else if (isset($Linkedin['update-content']['person']['current-status'])) {
                    
                            echo  '<p class="aaf_linkedin_feed_user_status">' . Engine_Api::_()->advancedactivity()->getURLString($Linkedin['update-content']['person']['current-status']) .'</p>';?>
                    
                  <?php }  else if ($Linkedin['update-type'] == 'PICU') {
                    
                            echo  $this->translate(' has a new profile photo');?>
                    
                  <?php }  else if (isset($Linkedin['update-content']['person']['current-share'])) {
                    
                            $content =  '<p class="aaf_linkedin_feed_user_status">' . Engine_Api::_()->advancedactivity()->getURLString(@$Linkedin['update-content']['person']['current-share']['comment']). '</p>';
                            
                            if (isset($Linkedin['update-content']['person']['current-share']['content'])) {
                            
													$content = $content . '<div class="aaf_linkedin_share_object"><a href="'. @$Linkedin['update-content']['person']['current-share']['content']['submitted-url'].'" class="aaf_linkedin_share_object_photo"><img src="'. @$Linkedin['update-content']['person']['current-share']['content']['submitted-image-url'] .'" alt="" /> </a>'. ' <div class="aaf_linkedin_share_object_body"><span class="aaf_linkedin_share_object_title"><a href="'. @$Linkedin['update-content']['person']['current-share']['content']['submitted-url'].'">'. @$Linkedin['update-content']['person']['current-share']['content']['title'] . '</a></span><p class="aaf_linkedin_share_object_des">';
													
													if (is_array($Linkedin['update-content']['person']['current-share']['content']['description']) )
													
													  $content = $content . Engine_Api::_()->advancedactivity()->getURLString($Linkedin['update-content']['person']['current-share']['content']['description'][0]) . '</p></div></div>';
													
													else
															 $content = $content . Engine_Api::_()->advancedactivity()->getURLString($Linkedin['update-content']['person']['current-share']['content']['description']) . '</p></div></div>';
                            
                            }
                            
                            echo $content;
                            
                  
                  
                      } else if (isset($Linkedin['update-content']['person']['member-groups'])) {
                    
                            echo ' ' . $this->translate('joined the group') .  ' ' ;?>
                            
                             <a href="<?php echo $Linkedin['update-content']['person']['member-groups']['member-group']['site-group-request']['url'];?>" target="_blank" class="feed_item_username"> <?php echo $Linkedin['update-content']['person']['member-groups']['member-group']['name'];?></a>
                             
                 <?php  }  else if (isset($Linkedin['update-content']['person']['skills'], $Linkedin['update-content']['person']['skills']['skill']) && count ($Linkedin['update-content']['person']['skills']['skill']) > 0 ) {
                    
                            echo  ' ' . $this->translate('has added skills:') .  ' ' ; ?>
                            
                      <?php $count = count($Linkedin['update-content']['person']['skills']['skill']);
                         if ($count == 1) :?>
                           <a href="<?php echo $Linkedin['update-content']['person']['site-standard-profile-request']['url'];?>" target="_blank" > <?php echo $Linkedin['update-content']['person']['skills']['skill']['skill']['name'];?></a>
                        <?php else:    
                           
                              foreach ($Linkedin['update-content']['person']['skills']['skill'] as $key => $skill) : ?>
                            
									<a href="<?php echo $Linkedin['update-content']['person']['site-standard-profile-request']['url'];?>" target="_blank" > <?php echo $skill['skill']['name'];?></a>
												
												<?php if (($count - $key) > 1 && $key < 2) { 
																	echo ", ";
                             }
														 if ($key > 2){
												echo " and <a href='".  $Linkedin['update-content']['person']['site-standard-profile-request']['url'] . "' target='_blank'  >". (int)(count($Linkedin['update-content']['person']['skills']['skill']) - 3) . " " . $this->translate('more') . "</a>";
																break;
															} ?>
                 <?php endforeach; endif; 
                    
                     
												
                 
                 
                  } else if (isset($Linkedin['update-content']['person']['positions'], $Linkedin['update-content']['person']['positions']['position'])) {
                    
         echo ' ' . $this->translate('has an updated current title:') .  ' ' .$Linkedin['update-content']['person']['positions']['position']['title'] . ' at ' . $Linkedin['update-content']['person']['positions']['position']['company']['name'];?>
                    
						<?php } else if (isset($Linkedin['update-content']['person']['recommendations-given'], $Linkedin['update-content']['person']['recommendations-given']['recommendation'])) {
                    
           echo  ' ' . $this->translate('recommends') .  ' ';?>
          
          <a href="<?php echo $Linkedin['update-content']['person']['recommendations-given']['recommendation']['recommendee']['site-standard-profile-request']['url'];?>" target="_blank" title="<?php echo $Linkedin['update-content']['person']['recommendations-given']['recommendation']['recommendee']['headline'];?>" class="feed_item_username">
          
            <?php $Screen_Name_connected = $Linkedin['update-content']['person']['recommendations-given']['recommendation']['recommendee']['first-name'] . ' ' . $Linkedin['update-content']['person']['recommendations-given']['recommendation']['recommendee']['last-name']; ?>
													<?php echo $Screen_Name_connected;?>
											</a>
											
											<?php echo ',  ' . Engine_Api::_()->advancedactivity()->getURLString($Linkedin['update-content']['person']['recommendations-given']['recommendation']['recommendee']['headline']);?>
          
                    
						<?php }
						
						
						else if (isset($Linkedin['update-content']['person']['member-url-resources'], $Linkedin['update-content']['person']['member-url-resources']['member-url-resource']) && !empty($Linkedin['update-content']['person']['member-url-resources']['member-url-resource']['url'])) {
                    
         echo  ' ' . $this->translate('has added new profile links:') . ' <a href="'.$Linkedin['update-content']['person']['member-url-resources']['member-url-resource']['url'] . '" target="_blank">' . ucfirst($Linkedin['update-content']['person']['member-url-resources']['member-url-resource']['member-url-resource-type']['code']) . '</a>';?>
                    
						<?php }
						
						else if (isset($Linkedin['updated-fields'], $Linkedin['updated-fields']['update-field'])) { 
						           $fields_string = '';
						           if ($Linkedin['updated-fields']['@attributes']['count'] == 1) {
						             $fields_string = $fields_string . ucfirst(str_replace('person/', '', $Linkedin['updated-fields']['update-field']['name']));
												
						           }
						           else {
													foreach ($Linkedin['updated-fields']['update-field'] as $key => $field) {
															$fields_string = $fields_string . ucfirst(str_replace('person/', '', $field['name'])) . ', ';													}
													 $fields_string = rtrim($fields_string, ", ");
						           }
							
								echo  ' ' .  $this->translate('has an updated profile') .  ' ('. $fields_string .') ';?>
							
						<?php } else { 
                         
                           echo ' ' .  $this->translate('is now your connection.');
                  
                  }?> 
              
               
            </span> 

            <div class="aaf_feed_item_stats" >
                  
							<?php //SHOWING THE NO OF LIKES.............//
                $like_comment = 0;          
							if (isset($Linkedin['is-likable']) && $Linkedin['is-likable'] === 'true') : 
								//CHECK IF THE CURRENT USER HAS LIKED OR NOT
								if (isset($Linkedin['is-liked']) && $Linkedin['is-liked'] === 'true'):
										$current_user_like = 1;
										$Linkedin_action = 'unlike';
										$like_unlike = $this->translate('Like');
										$like_unlike_title = $this->translate('Click to unlike this update');
								
								else :
										$current_user_like = 0;
										$Linkedin_action = 'like';
										$like_unlike = $this->translate('Like');
										$like_unlike_title = $this->translate('Clike to like this update');
								endif;		
			       
			       
								if (isset($Linkedin['num-likes']) && !empty($Linkedin['num-likes'])) :
										$post_count = $Linkedin['num-likes'];
								else:
										$post_count = 0;
								endif;   
  				      
  				      ?>
                <?php 
							
									$like_html = '<a href="javascript:void(0);"  title="'. $like_unlike_title .'">';
									$comment_html = '<a href="javascript:void(0);" title="'. $this->translate('Click to comment on this update') .'">';

									$show_hide_temp = '';
						
            
											echo '<span onclick="linkedin_like(this, \'' . $Linkedin['update-key'] . '\',\''.  $Linkedin_action . '\',\'' . @$post_count . '\' )">'.
													$like_html . $this->translate('%s', $like_unlike);?>
								 					<?php if (isset($Linkedin['num-likes']) && !empty($Linkedin['num-likes'])):
													$like_comment = 2;
													echo '<span class="count_linkedinlike">('  . $Linkedin['num-likes'] .')</span>';
													
											endif;?>
											</a>
										</span>	
									
										<span>&#183;</span>
									<?php echo	'<span onclick="linkedin_comment(this)">'.
											$comment_html . $this->translate('Comment') ;?>
											<?php if (!empty($Linkedin['update-comments']) && !empty($Linkedin['update-comments']['@attributes']['total'])) :
												$like_comment = 1;
												echo '<span>(' .$Linkedin['update-comments']['@attributes']['total'] .')</span>';
											endif;?>
										</a>
									</span>
									 <?php if ($Linkedin['update-type'] != 'STAT' && $Linkedin['update-content']['person']['id'] != $this->currentuser_id): ?>
												<span>&#183;</span>
										<?php endif;  ?>		
								<?php endif;  ?>     
						<?php  
//  					 		if ( $like_comment == 1) :
//  				        echo $show_hide_temp . '</span><span>&#183;</span>';
//  				      elseif ( $like_comment == 2):
//  				       	echo $show_hide_temp . '</span><span>&#183;</span>';
//  				      
//  				      endif;
  				     
  				    ?>
  				    
  				    <?php if ($Linkedin['update-type'] != 'STAT' && $Linkedin['update-content']['person']['id'] != $this->currentuser_id): ?>
									<span>
									<a href="javascript:void(0);" onclick="sendLinkedinMessage(this, 'get', '<?php echo $Screen_Name_connecter; ?>', '<?php echo $Screen_Name_connected; ?>' , '<?php echo $Linkedin['update-content']['person']['id']; ?>')" title="<?php echo $this->translate('Send a message');?>"><?php echo 	$this->translate('Send a message');?></a>
									</span>
             <?php endif; ?>
							<?php echo '<span>&#183;</span><span>' .	$this->timestamp($Linkedin['timestamp']/1000) . '</span>';  ?>

						</div>
						
						<div class="comments aaf_feed_comment_commentbox" <?php if (empty( $Linkedin['num-likes']) && empty( $Linkedin['update-comments']['@attributes']['total'])):?> style="display:none;" <?php else :?> style="display:block;" <?php endif;?>>
								<ul class="postcomment_linkedin">
  					
										<?php //SHOWING THE NO OF LIKES.............//
  					 
										if (!empty( $Linkedin['num-likes'])) : ?>
											<li class="aaf_feed_comment_likes_count">
												<i class="aaf_feed_linkedin_like_icon aaf_feed_fb_icon" ></i>
								<?php
  						    
												if ($current_user_like == 0) :  						         
													echo '<div class="Linkedin_LikesCount">'. $Linkedin['num-likes'].'  ' . $this->translate('people') .' ' . $this->translate('like this').'</div>';
  
												else :
													if ($Linkedin['num-likes'] > 1) :
															//$like_people_total = (isset($feed['object_id'])) ? $feed['object_id']: $Linkedin['timestamp'];		
															$like_people = --$Linkedin['num-likes'] .'  ' . $this->translate('others');
															echo '<div class="Linkedin_LikesCount">' . $this->translate('You and %s like this.', $like_people )    .' </div>';
													else :
														echo '<div class="Linkedin_LikesCount">' . $this->translate('You like this.') .'</div>';
													endif; 
												endif;
  						  ?>
  						        
									
  						 	</li>
  						 <?php endif; ?>
  					
  					  <?php  
  				    if (!empty($Linkedin['update-comments']['update-comment'])) : 
  				   
  				          $comment_count =  $Linkedin['update-comments']['@attributes']['total'];
  					         if ($comment_count > 3) :
  					         
												echo '<li class="aaf_feed_comment_likes_count" onclick="showAllComments(this,\'' . $Linkedin['update-key']. '\')"><div> <a href="javascript:void(0);">' . $this->translate('See previous comments') . '</a></div></li>';
  					         endif; 
  				          $iteration_comment = 0;
										foreach ($Linkedin['update-comments']['update-comment'] as $comment) :?>
								<?php
								      if (!isset($Linkedin['update-comments']['update-comment'][0])):
													$comment = $Linkedin['update-comments']['update-comment'];
											
											endif; ?>	
											<?php $iteration_comment++; ?>
											<li class="feedcomment-<?php echo $Linkedin['update-key'];?>" <?php if ($iteration_comment > 3):?> style="display:none;" <?php endif;?>>
													<div class="comments_author_photo">
														<a href="<?php echo $comment['person']['site-standard-profile-request']['url'];?>" target="_blank">
                                       
															<img src="<?php echo isset($comment['person']['picture-url']) ? $comment['person']['picture-url'] : $this->layout()->staticBaseUrl. 'application/modules/User/externals/images/nophoto_user_thumb_icon.png' ;?>" alt="" class="thumb_icon item_photo_user"/> 
                     
														 </a>
													</div>
													<div class="comments_info">
															<span class="comments_author">
																	<a href="<?php echo $comment['person']['site-standard-profile-request']['url'];?>" target="_blank">
																			<?php echo $comment['person']['first-name'] . ' ' . $comment['person']['last-name'];?> 
																	</a>
															</span> 
															<span id='comment_message'>
																	<?php if (!empty($comment['comment'])) {?>
																			<?php $Message_Length = strlen($comment['comment']);
                            
																				if ($Message_Length > 250) {
																					$message = substr($comment['comment'], 0, 250);
																					$id_text = 'postcomment_id_' . $comment['id'];
																					$message = $message . '... <a href="javascript:void(0);"  class="facebooksepage_seemore">' . $this->translate("See More") .'</a>';
																				}else {
																					$message = $comment['comment'];
																				} ?>
                            
																				<p class="linkedinmessage_text_short" onclick="Linkedin_AAF_showText_More(1, this);">
																				<?php 
																					$message =  Engine_Api::_()->advancedactivity()->getURLString($message);                        
																				echo nl2br($message);?>
																				</p>
																				<div id="linkedinmessage_text_full" style="display:none;">
																					<?php
																						$comment['comment'] =  Engine_Api::_()->advancedactivity()->getURLString($comment['comment']); 
																					
																					echo nl2br($comment['comment']);?>
																				</div>
																<?php } ?>
                       
															</span>
												</div>
											</li>
											<?php if (!isset($Linkedin['update-comments']['update-comment'][0])) : break; endif;endforeach;						
										
										endif;
										?>
									</ul>
									<?php if ($Linkedin['is-commentable'] == 'true') : ?>
											<form action="" style="display:none;" class="aaf_fb_comment CommentonPost_linkedin">
													<div class="comments_info">
															<textarea title="<?php echo $this->translate("Write a comment...") ?>" rows="1" cols="45" 		class="LinkedinCommentonPost_submit" name="body" style="overflow-x: auto; overflow-y: hidden; resize: none; padding-bottom: 0px; padding-top: 4px; padding-left: 4px; height: 19px; max-height: 19px;" onfocus="showHideCommentbox_linkedin($(this), 1);" onblur="showHideCommentbox_linkedin($(this), 2);" class="aaf_color_light">
																	<?php echo $this->translate('Write a comment...');?>
															
															</textarea>
															<input type="hidden" value="<?php echo $Linkedin['update-key'];?>" id="post_commentonfeed">
															<button type="submit" class="Linkedin_activity-comment-submit" name="submit" style="display: none;" onclick="post_comment_onlinkedin(this,'<?php echo $Linkedin['update-key'];?>', 'post');return false;">
																	<?php echo $this->translate('Post Comment');?>
															</button>
													</div>
											</form>
									<?php endif;?>
									</div>
								</li>
								
							<?php
							if (!isset($this->LinkedinFeeds['update'][0])):
							   break; endif; ?>
						<?php endforeach; ?>
     <?php if (empty($this->isajax) && empty($this->checkUpdate) && empty($this->getUpdate)) : ?>
     </ul>
     
      <div class="seaocore_view_more" id="feed_viewmore_linkedin" style="display: none;">
				<?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array(
					'id' => 'feed_viewmore_linkedin_link',
					'class' => 'buttonlink icon_viewmore',
				)) ?>
			</div>
			<div id="feed_loading_linkedin" style="display: none;" class="aaf_feed_loading">
				<img src='<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/core/loading.gif' alt="Loading" />
				<?php echo $this->translate("Loading ...") ?>
			</div>
			<div class="aaf_feed_tip" id="feed_no_more_linkedin" style="display: none;"> 
				<?php echo $this->translate("There are no more posts to show.") ?>
			</div>
     <?php endif;?>
     
     <script type="text/javascript">
     en4.core.runonce.add(function ()  {  
       <?php if(empty($this->isajax) || !empty($this->next_previous)) {?>
          last_linkedin_timestemp = '<?php echo $last_linkedin_timestemp;?>';
       <?php } ?>    
      <?php if(empty($this->isajax) || !empty($this->getUpdate)) {?>    
       current_linkedin_timestemp = '<?php echo $current_linkedin_timestemp;?>';
       
      <?php } ?>        
        getCommonLinkedinElements();
        
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
				if ($('composer_linkedin_toggle')) {
					$('composer_linkedin_toggle').style.display = 'none';
				}
				view_moreconnection_linkedin = '<?php echo $view_moreconnection_linkedin;?>';
         if (view_moreconnection_linkedin == 1) {
						if(autoScrollFeedAAFEnable) {
								window.onscroll = Linkedin_doOnScrollLoadActivity;
						} else if ($('feed_viewmore_linkedin')) { 
								window.onscroll="";
								feed_viewmore_linkedin.style.display = 'block';
								feed_loading_linkedin.style.display = 'none'; 
								feed_view_more_linkedin_link.removeEvents('click').addEvent('click', function(event){ 
									event.stop();
									linkedin_ActivityViewMore();
								});
							}
						}
			});
   </script>  
     
   
  <?php else: ?>
    <?php if (!empty($this->LinkedinLoginURL) && empty($this->isajax)) { 
        $execute_script = 0;
      
      ?>
      <div class="aaf_feed_tip"><?php echo $this->translate('LinkedIn is currently experiencing technical issues, please try again later.');?></div>
      
    <?php } else { ?>
          
         <script type="text/javascript">
            if ($('feed_viewmore_linkedin')) { 
              if(autoScrollFeedAAFEnable)
						   window.onscroll="";
							feed_viewmore_linkedin.style.display = 'none';
							feed_loading_linkedin.style.display = 'none';
							feed_no_more_linkedin.style.display = '';
						}
            //feed_no_more_linkedin.style.display = '';
         </script>
            
     <?php } ?>       
      
  <?php 
  
  
  endif;?>  

<?php if (empty($this->isajax)) : ?>
<div id="linkedinmessage_html" style="display:none;">
	<form method="post" id="post_linkedin_message" class="global_form aaf_linkedin_message_form">		
			<div>	
				<h3><?php echo $this->translate('Send a message');?></h3>
        <div class="show_errormessage"></div>
				<div class="form-elements">
					<div class="form-wrapper" id="to-wrapper">
						<div class="form-label" id="to-label">	
							<label class="optional" for="to"><?php echo $this->translate('To:');?></label>
						</div>
						<div class="form-element" id="to-elementlinkedin" style="padding-top:4px;">
						</div>
					</div>
					<div class="form-wrapper" id="toValues-wrapper">
						<div class="form-element" id="toValues-element">
								<input type="hidden" id="toValueslinkedin" value="" name="toValues">
						</div>
					</div>
					<div class="form-wrapper" id="title-wrapperlinkedin">
						<div class="form-label" id="title-label"><label class="optional" for="title"><?php echo $this->translate('Subject:');?></label>
						</div>
						<div class="form-element" id="title-element">
						<div contenteditable="true" style="display: block;width:300px;" class="compose-content" id="subject_linkedin" ><br>
						</div>
						<input type="text" value="" id="titlelinkedin" name="title" style="display: none;">
					</div>
				</div>
				<div class="form-wrapper" id="body-wrapper">
					<div class="form-label" id="body-label">
							<label class="required" for="body"><?php echo $this->translate('Message');?></label>
					</div>
					<div class="form-element" id="body-element">
						<div id="compose-container" class="compose-container">
								<textarea rows="6" cols="45"  name="body" class="compose-textarea" style="display: block;" id="linkedin_message_textarea" ></textarea>
					</div>
				</div>
			</div>
			<div class="form-wrapper" id="submit-wrapper">
					<div class="form-label" id="submit-label">&nbsp;
					</div>
					<div class="form-element" id="submit-element">
							<div id="compose-tray" style="display: none;margin-right:10px;"></div>
							<button type="submit" id="submit_linkedin" name="submit" onclick= "return sendLinkedinMessage(this);"><?php echo $this->translate('Send Message');?></button> <?php echo $this->translate('or');?> 
							<a onclick="parent.Smoothbox.close();" href="javascript:void(0);"><?php echo $this->translate('Cancel');?></a>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
<?php endif;?>