<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php
    $flowplayerJs = Engine_Api::_()->sitereview()->checkVersion(Engine_Api::_()->getDbtable('modules', 'core')->getModule('core')->version, '4.8.10')  ? 'flashembed-1.0.1.pack.js' : 'flowplayer-3.2.13.min.js';
$baseUrl = $this->layout()->staticBaseUrl;
$this->headLink()
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');
?>

<?php if( $this->num_of_slideshow): ?>
  <?php
    $this->headScript()
          ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/scripts/_class.noobSlide.packed.js');
  ?>

  <?php if ($this->params && $this->video->type == 3): ?>
    <?php
    $this->videoPlayerJs();
    ?>
  <?php endif; ?>

  <?php
  // Starting work for "Slide Show".
  $image_var = '';
  $image_text_var = '';
  $pane_var = '';
  $pagination_var = '';
  $thumbnail_var = '';
  $thumb_span_var = '';
  $title_link_var = '';
  $image_count = 1;

  foreach ($this->show_slideshow_object as $key => $item) {
    if (isset($item['video_id'])) {
      $background_url =null;
      if (!empty($this->sitereview->video_snapshot_id)) {
        $photoTableObject = Engine_Api::_()->getItem('sitereview_photo', $this->sitereview->video_snapshot_id);
        if($photoTableObject)
        $background_url = $photoTableObject->getPhotoUrl();

      }

      $a = $item['video_id'];
      if($background_url){
        $itemPhoto = '<div class="sr_video_snapshot"  onclick="sitereviewShowVideo();" style="background-image: url('.$background_url.');" >';
         $itemPhoto .= '<span class="sr_video_snapshot_play_large_button"></span>';
         $itemPhoto .= '</div>';
      }else{
        $itemPhoto = '<div class="sr_video_snapshot" onclick="sitereviewShowVideo();">';
         $itemPhoto .= "<a id='sitereview_video_video_thumb_$a' href='javascript:void(0);' onclick='sitereviewShowVideo();'>" .
              $this->itemPhoto($this->video, 'thumb.normal') .
              '<span class="sr_video_snapshot_play_button"></span>'.
              "</a>";
      $itemPhoto .= '</div>';
      }


      $content_link = $this->htmlLink($this->video->getHref(), $this->translate("View Video &raquo;"), array('class' => 'featured_slideshow_view_link', 'target' => '_blank'));
    } else {
      $photoTableObject = Engine_Api::_()->getItem('sitereview_photo', $item['photo_id']);
      $itemPhoto = (SEA_LIST_LIGHTBOX) ? ("<span class='sr_media_slidebox_fullscreen sr_media_slideshow_controller' onclick='openSeaocoreLightBox(\"".$photoTableObject->getHref() ."\");return false;'><i></i></span>". $this->itemPhoto($photoTableObject, null)) : $this->itemPhoto($photoTableObject, null);
      $content_link = $this->htmlLink($photoTableObject->getHref(), $this->translate("View Photo &raquo;"), array('class' => 'featured_slideshow_view_link', 'target' => '_blank'));
    }

    $image_var .= '<span>' . $itemPhoto . '</span>';
    $pane_var .= "<span>Pane " . ($image_count + 1) . "</span>";
    $pagination_var .= "<span>" . ($image_count + 1) . "</span>";
    $thumb_span_var .= "<span></span>";
    $image_text_var .= "<div class='sr_media_slidebox'>";
    $image_description = "";
    if($this->showCaption) {
      $description = Engine_Api::_()->seaocore()->seaocoreTruncateText($item['description'], $this->captionTruncation);
      $image_description = !empty($description) ? "<div class='sr_media_slidebox_caption'><p>$description</p></div>" : "";
    }
    
    $image_text_var .= "<div class='sr_media_slidebox_photo'><table width='100%' height='100%'><tr><td>" . $itemPhoto .$image_description. "</td></tr></table></div>";
    $image_text_var .= "<div class='featured_slidshow_content'>";

    if (!empty($content_link)) {
      $image_text_var .= "<h3 style='display:none'><span>" . $image_count++ . '_caption_title:' . $item['title'] . '_caption_link:' . $content_link . '</span>' . "</h3>";
    }

    if (!empty($content_info)) {
      $image_text_var .= "<span class='featured_slidshow_info'>" . $content_info . "</span>";
    }
    $image_text_var .= "</div></div>";
  }
  ?>

  <script type="text/javascript">
    <?php if ($this->video): ?>
      
      function sitereviewCloseVideo(){
        $('slideshow_wrapper').style.display='block';
        $('show_video_conent').style.display='none'; 
        $('video_embeded_content').empty();
      }
      
      function sitereviewShowVideo(){
        $('slideshow_wrapper').style.display='none';
        $('show_video_conent').style.display='block';
        $('video_embeded_content').empty();
        <?php if ($this->video->type == 3): ?>
                <?php
                                                        
       $flowplayerSwf = Engine_Api::_()->sitereview()->checkVersion(Engine_Api::_()->getDbtable('modules', 'core')->getModule('core')->version, '4.8.10') ? 'flowplayer-3.1.5.swf' : 'flowplayer-3.2.18.swf';?>
          var flashembedAddSlideShowSR = function(){
            flashembed("video_embeded_content",
            {
              src: "<?php echo $this->layout()->staticBaseUrl ?>externals/flowplayer/<?php echo $flowplayerSwf;?>",
              width: 480,
              height: 386,
              wmode: 'transparent'
            },
            {
            config: {
              clip: {
                url: "<?php echo $this->video_location; ?>",
                autoPlay: <?php echo $this->autoPlay; ?>,
                duration: "<?php echo $this->video->duration ?>",
                autoBuffering: true
              },
              plugins: {
                controls: {
                  background: '#000000',
                  bufferColor: '#333333',
                  progressColor: '#444444',
                  buttonColor: '#444444',
                  buttonOverColor: '#666666'
                }
              },
              canvas: {
                backgroundColor:'#000000'
              }
            }
          });
        }
        if(!(typeof flashembed == 'function')){
          new Asset.javascript( en4.core.staticBaseUrl + 'externals/flowplayer/'+ '<?php echo $flowplayerJs;?>',{
          onLoad :flashembedAddSlideShowSR
          });
        } else {
          flashembedAddSlideShowSR();
        }
        <?php else: ?>
          new Element('div', {      
            'class' : 'sr_profile_loading_image',
            'styles':{
              'height': '400px'
            }
          }).inject($('video_embeded_content'));

          var request = new Request.HTML({
            url : en4.core.baseUrl+'sitereview/video/video-player',
            data : {
              format : 'html',
              video_guid: '<?php echo $this->video->getGuid() ?>',
              listingtype_id :'<?php echo $this->listingtype_id ?>'
            },
            evalScripts : true,
            onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
              $('video_embeded_content').empty();
              Elements.from(responseHTML).inject($('video_embeded_content'));
              en4.core.runonce.trigger();
            }
          });
          request.send();
        <?php endif; ?>
      }
    <?php endif; ?>
    
    en4.core.runonce.add(function() {
      if (document.getElementsByClassName == undefined) {
        document.getElementsByClassName = function(className)
        {
          var hasClassName = new RegExp("(?:^|\\s)" + className + "(?:$|\\s)");
          var allElements = document.getElementsByTagName("*");
          var results = [];

          var element;
          for (var i = 0; (element = allElements[i]) != null; i++) {
            var elementClass = element.className;
            if (elementClass && elementClass.indexOf(className) != -1 && hasClassName.test(elementClass))
              results.push(element);
          }

          return results;
        }
      }
      
      var height='<?php echo $this->slideshow_height ?>';
      $('global_content').getElement(".sr_media_slideshow_mask").style.height= (height)+"px";

      var width='<?php echo $this->slideshow_width ?>';
      $('global_content').getElement(".sr_media_slideshow_mask").style.width= (width)+"px";
      var divElements=document.getElementsByClassName('sr_media_slidebox');   
      for(var i=0;i < divElements.length;i++) {
        divElements[i].style.width= (width)+"px";
        divElements[i].style.height= (height)+"px";
      }
      
      <?php if($this->mouseEnterEvent): ?>
        var handle_event = 'mouseenter';
      <?php else: ?>  
        var handle_event = '';
      <?php endif; ?>  

      var handles8_more = $$('.handles8_more span');
      var num_of_slidehsow = "<?php echo $this->num_of_slideshow; ?>";
      var nS8 = new noobSlide({
        box: $('sitereview_featured_im_te_advanced_box'),
        items: $$('#sitereview_featured_im_te_advanced_box h3'),
        size: (width),
        handles: $$('#handles8 span'),
        handle_event: handle_event,
        addButtons: {previous: $('sitereview_featured_prev8'), stop: $('sitereview_featured_stop8'), play: $('sitereview_featured_play8'), next: $('sitereview_featured_next8') },
        interval: 2800,
        fxOptions: {
          duration: 800,
          transition: '',
          wait: false
        },
        autoPlay: <?php echo $this->autoPlay; ?>,
        mode: 'horizontal',
        onWalk: function(currentItem,currentHandle){

        // Finding the current number of index.
        var current_index = this.items[this.currentIndex].innerHTML;
        var current_start_title_index = current_index.indexOf(">");
        var current_last_title_index = current_index.indexOf("</span>");
        // This variable containe "Index number" and "Title" and we are finding index.
        var current_title = current_index.slice(current_start_title_index + 1, current_last_title_index);
        // Find out the current index id.
        var current_index = current_title.indexOf("_");
        // "current_index" is the current index.
        current_index = current_title.substr(0, current_index);

        handles8_more.removeClass('checked');
        $('handles8_more_' + current_index).getElement('span').addClass('checked');
      }
    });

    //more handle buttons
    nS8.addHandleButtons(handles8_more);
    //walk to item 3 witouth fx
    nS8.walk(0,false,true);
  });
  </script>
  
  <?php if($this->showButtonSlide): ?>
  
    <?php if($this->thumbPosition == 'bottom'):?>
      <div class="sr_media_slideshow_wrapper" style="width:<?php echo $this->slideshow_width;?>px;height:<?php echo $this->totalRow*50+10+$this->slideshow_height?>px">
    <?php elseif($this->thumbPosition == 'left'):?>
      <div class="sr_media_slideshow_wrapper sr_media_slideshow_thumbs_left" style="width:<?php echo $this->totalColumn*50+5+$this->slideshow_width;?>px;height:<?php echo $this->slideshow_height?>px">
    <?php elseif($this->thumbPosition == 'right'):?>
      <div class="sr_media_slideshow_wrapper sr_media_slideshow_thumbs_right" style="width:<?php echo $this->totalColumn*50+5+$this->slideshow_width;?>px;height:<?php echo $this->slideshow_height?>px">        
    <?php endif; ?>
  
  <?php else: ?>
    <div class="sr_media_slideshow_wrapper">
  <?php endif;?>  
    
    
  	<div id="slideshow_wrapper" class="sr_media_slideshow" style="width:<?php echo $this->slideshow_width;?>px;height:<?php echo $this->slideshow_height;?>px;">
			<div class="sr_media_slideshow_mask">
				<div id="sitereview_featured_im_te_advanced_box" class="featured_slideshow_advanced_box">
					<?php echo $image_text_var ?>
				</div>
			</div>

			<div class="sr_media_slideshow_options">
				<p class="buttons" style="<?php if($this->num_of_slideshow<2):?> display:none;<?php endif;?>" >
					<span id="sitereview_featured_prev8" class="sr_media_pre sr_media_pre_nxt sr_media_slideshow_controller" title="<?php echo $this->translate("Previous")?>" ><i></i></span>
					<span id="sitereview_featured_stop8" class="sr_media_stop sr_media_stop_play  sr_media_slideshow_controller" title="Stop"><i></i></span>
					<span id="sitereview_featured_play8" class="sr_media_play sr_media_stop_play sr_media_slideshow_controller" title="Play"><i></i></span>
					<span id="sitereview_featured_next8" class="sr_media_nxt sr_media_pre_nxt sr_media_slideshow_controller" title="<?php echo $this->translate("Next")?>"><i></i></span>
				</p>
			</div>
      
			<?php if($this->showButtonSlide): ?> 
      
        <?php if($this->thumbPosition == 'bottom'):?>
      
          <div class="sr_media_slideshow_paging_thumbs" style='width:<?php echo $this->slideshow_width; ?>px;bottom:-<?php echo $this->totalRow*50+10?>px;'>
            
        <?php elseif($this->thumbPosition == 'left'):?>
          <div class="sr_media_slideshow_paging_thumbs" style='width:<?php echo $this->totalColumn*50; ?>px;height:<?php echo $this->slideshow_height; ?>px;left:-<?php echo $this->totalColumn*50+5; ?>px;top:0;'>
        <?php elseif($this->thumbPosition == 'right'):?>
          <div class="sr_media_slideshow_paging_thumbs" style='width:<?php echo $this->totalColumn*50; ?>px;height:<?php echo $this->slideshow_height; ?>px;right:-<?php echo $this->totalColumn*50+5?>px;top:0;'>
        <?php endif;?>      
				
					<div class="wrapper" style="<?php if($this->num_of_slideshow<2):?> display:none;<?php endif;?>" >
						<?php $i = 1; foreach ($this->show_slideshow_object as $key => $item): ?>
							<p  class="handles8_more" id="handles8_more_<?php  echo $i; ?>"> 
								<?php 
									if (isset($item['video_id'])) {
		
										if($this->sitereview->video_snapshot_id) {
											$photoTableObject = Engine_Api::_()->getItem('sitereview_photo', $this->sitereview->video_snapshot_id);
											$itemPhoto = $this->itemPhoto($photoTableObject,'thumb.icon');
										}
										else {
											$itemPhoto = $this->itemPhoto($this->video, 'thumb.icon');          
										}
									} 
									else { 
									 $photoTableObject = Engine_Api::_()->getItem('sitereview_photo', $item['photo_id']);
									 $itemPhoto = $this->itemPhoto($photoTableObject,'thumb.icon');
									}
								?>
								<span class="<?php  echo $i==1?'checked':''; ?>">
									<?php echo $itemPhoto; ?>
								</span>
							</p>
						<?php $i++; endforeach; ?>
					</div>
				</div> 
			<?php else: ?>
				<div class="sr_media_slideshow_paging_buttons">
					<div class="wrapper" style="<?php if($this->num_of_slideshow<2):?> display:none;<?php endif;?>" >
						<?php for ($i = 1; $i <= $this->num_of_slideshow; $i++): ?>
							<p  class="handles8_more" id="handles8_more_<?php  echo $i; ?>">
								<span class="<?php  echo $i==1?'checked':''; ?>"></span>
							</p>
						<?php endfor; ?>
					</div>
				</div>		
			<?php endif; ?>  
  	</div>
  <?php if ($this->video): ?>
    <div id="show_video_conent" class="sr_media_slideshow" style="width:<?php echo $this->slideshow_width ?>px; height:<?php echo $this->slideshow_height ?>px; background-color: black;display: none;">
      <div onclick="sitereviewCloseVideo()" class="sr_media_slideshow_close"><?php echo $this->translate("Close[X]") ?></div>
      <div id="video_embeded_content" class="sitereview_video_embed" style="text-align: center;">
      </div>
    </div>
  <?php endif; ?>
<?php endif; ?>
</div>
<style tyle="text/css">
  .sr_media_slidebox_photo td img {
    max-height: <?php echo $this->slideshow_height;?>px;
    max-width: <?php echo $this->slideshow_width;?>px;
  }
  .sitereview_video_embed object,
  .sitereview_video_embed embed{
    height: <?php echo $this->slideshow_height - 40;?>px !important;
    width: <?php echo $this->slideshow_width - 30;?>px !important;
  }
</style>  
  
