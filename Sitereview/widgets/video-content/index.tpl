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
	$this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');
	$this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview_video.css')
?>
<?php if( !$this->video || ($this->video->status!=1)): ?>
	<div class="tip">
		<span>
			<?php echo $this->translate('The video you are looking for does not exist or has not been processed yet.');?>
		</span>
	</div>		
	<?php return; // Do no render the rest of the script in this mode
endif; ?>

<?php if ( $this->video->type == 3 && $this->video_extension == 'mp4' )
    $this->videoPlayerJs();
?>

<?php if( $this->video->type == 3 && $this->video_extension == 'flv' ):
    $this->videoPlayerJs();
  ?>
<?php

       $flowplayerSwf = Engine_Api::_()->sitereview()->checkVersion(Engine_Api::_()->getDbtable('modules', 'core')->getModule('core')->version, '4.8.10')  ? 'flowplayer-3.1.5.swf' : 'flowplayer-3.2.18.swf';?>
  <script type='text/javascript'>
    flashembed("video_embed",
    {
      src: "<?php echo $this->layout()->staticBaseUrl ?>externals/flowplayer/<?php echo $flowplayerSwf;?>",
      width: 480,
      height: 386,
      wmode: 'transparent'
    },
    {
      config: {
        clip: {
          url: "<?php echo $this->video_location;?>",
          autoPlay: false,
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
    
  /*var flowplayer = "<?php echo $this->layout()->staticBaseUrl ?>/externals/flowplayer/flowplayer-3.1.5.swf";
  var video_player = new Swiff(flowplayer, {
    width:  320,
    height: 240,
    vars: {
      clip: {
        url: '/engine4/public/video/1000000/1000/68/53.flv',
        autoPlay: false,
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
  en4.core.runonce.add(function(){video_player.inject($('video_embed'))});*/

  </script>
<?php endif;?>

<script type="text/javascript">
  var listing_id = <?php echo $this->video->listing_id;?>;
  var listingtype_id = <?php echo $this->listingtype_id;?>;
  var pre_rate = <?php echo $this->video->rating;?>;
  var rated = '<?php echo $this->rated;?>';
  var video_id = <?php echo $this->video->video_id;?>;
  var total_votes = <?php echo $this->rating_count;?>;
  var viewer = <?php echo $this->viewer_id;?>;
  <?php if(empty($this->rating_count)): ?>
  var rating_var =  '<?php echo $this->string()->escapeJavascript($this->translate(" rating")) ?>';
  <?php else: ?>
  var rating_var =  '<?php echo $this->string()->escapeJavascript($this->translate(" ratings")) ?>';
   <?php endif; ?>
   var check_rating = 0;
	var current_total_rate;
  function rating_over(rating) {
    if (rated == 1){
      $('rating_text').innerHTML = "<?php echo $this->translate('you already rated');?>";
      //set_rating();
    }
    else if (viewer == 0){
      $('rating_text').innerHTML = "<?php echo $this->translate('please login to rate');?>";
    }
    else{
      $('rating_text').innerHTML = "<?php echo $this->translate('click to rate');?>";
      for(var x=1; x<=5; x++) {
        if(x <= rating) {
          $('rate_'+x).set('class', 'rating_star_big_generic rating_star_big');
        } else {
          $('rate_'+x).set('class', 'rating_star_big_generic rating_star_big_disabled');
        }
      }
    }
  }
  function rating_out() {
    $('rating_text').innerHTML = " <?php echo $this->translate(array('%s rating', '%s ratings', $this->rating_count),$this->locale()->toNumber($this->rating_count)) ?>";
    if (pre_rate != 0){
      set_rating();
    }
    else {
      for(var x=1; x<=5; x++) {
        $('rate_'+x).set('class', 'rating_star_big_generic rating_star_big_disabled');
      }
    }
  }

  function set_rating() {
    var rating = pre_rate;
     if(check_rating == 1) {
      if(current_total_rate == 1) {
    	  $('rating_text').innerHTML = current_total_rate+rating_var;
      }
      else {
		  	$('rating_text').innerHTML = current_total_rate+rating_var;
    	}
	  }
	  else {
    	$('rating_text').innerHTML = "<?php echo $this->translate(array('%s rating', '%s ratings', $this->rating_count),$this->locale()->toNumber($this->rating_count)) ?>";
	  }
    for(var x=1; x<=parseInt(rating); x++) {
      $('rate_'+x).set('class', 'rating_star_big_generic rating_star_big');
    }
    

    for(var x=parseInt(rating)+1; x<=5; x++) {
      $('rate_'+x).set('class', 'rating_star_big_generic rating_star_big_disabled');
    }

    var remainder = Math.round(rating)-rating;
    if (remainder <= 0.5 && remainder !=0){
      var last = parseInt(rating)+1;
      $('rate_'+last).set('class', 'rating_star_big_generic rating_star_big_half');
    }
  }
  
  function rate(rating) {
    $('rating_text').innerHTML = "<?php echo $this->translate('Thanks for rating!');?>";
    for(var x=1; x<=5; x++) {
      $('rate_'+x).set('onclick', '');
    }
    (new Request.JSON({
      'format': 'json',
      'url' : '<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'video', 'action' => 'rate'), 'default', true) ?>',
      'data' : {
        'format' : 'json',
        'rating' : rating,
        'video_id': video_id,
        'listing_id' : listing_id,
        'listingtype_id' : listingtype_id
      },
      'onRequest' : function(){
        rated = 1;
        total_votes = total_votes+1;
        pre_rate = (pre_rate+rating)/total_votes;
        set_rating();
      },
      'onSuccess' : function(responseJSON, responseText)
      {
         $('rating_text').innerHTML = responseJSON[0].total+rating_var;
         current_total_rate =  responseJSON[0].total;
         check_rating = 1;
      }
    })).send();
    
  }
  
  var tagAction =function(tag, url){
    $('tag').value = tag;
    window.location.href = url;
  }

  en4.core.runonce.add(set_rating);
  
</script>

<form id='filter_form' class='global_form_box' method='get' style='display:none;'>
  <input type="hidden" id="tag" name="tag" value=""/>
</form>

<div class="sr_view_top">
	<?php echo $this->htmlLink($this->sitereview->getHref(), $this->itemPhoto($this->sitereview, 'thumb.icon', '', array('align' => 'left'))) ?>
	<h2>
	  <?php echo $this->sitereview->__toString() ?>
	  <?php echo $this->translate('&raquo;');?>
     <?php echo $this->htmlLink($this->sitereview->getHref(array('tab'=>$this->tab_selected_id)), $this->translate('Videos')) ?>
	  <?php echo $this->translate('&raquo;');?>
	  <?php echo $this->video->title ?>
	</h2>
</div>

<div class="sr_video_view">
  <h3>
    <?php echo $this->video->title;?>
  </h3>

  <div class="sr_video_date seaocore_txt_light">
    <?php echo $this->translate('Posted by') ?>
    <?php echo $this->htmlLink($this->video->getOwner(), $this->video->getOwner()->getTitle()) ?>
  </div>
  <div class="video_desc">
    <?php echo nl2br($this->video->description);?>
  </div>
  <?php if( $this->video->type == 3): ?>
		<div id="video_embed" class="sr_video_embed">
      <?php if ($this->video_extension !== 'flv'): ?>
        <video id="video" controls preload="auto" width="480" height="386">
          <source type='video/mp4;' src="<?php echo $this->video_location ?>">
        </video>
      <?php endif ?>
		</div>
  <?php else: ?>
		<div class="sr_video_embed">
			<?php echo $this->videoEmbedded;?>
		</div>
  <?php endif; ?>
  <div class="sr_video_date seaocore_txt_light">
    <?php echo $this->translate('Posted');?> <?php echo $this->timestamp($this->video->creation_date) ?>
     <span class="video_views">- <?php echo $this->translate(array('%s comment', '%s comments', $this->video->comments()->getCommentCount()),$this->locale()->toNumber($this->video->comments()->getCommentCount())) ?>	-  
			<?php echo $this->translate(array('%s view', '%s views', $this->video->view_count ), $this->locale()->toNumber($this->video->view_count )) ?>
     - <?php echo $this->translate(array('%s like', '%s likes', $this->video->likes()->getLikeCount()),$this->locale()->toNumber($this->video->likes()->getLikeCount())) ?>
     </span>

    <?php if ($this->category): ?>
      - <?php echo $this->translate('Filed in');?>
      <a href='javascript:void(0);' onclick='javascript:categoryAction(<?php echo $this->category->category_id?>);'>
          <?php echo $this->translate($this->category->category_name) ?>
      </a>
    <?php endif; ?>

    <?php if (count($this->videoTags )):?>
      -
      <?php  foreach ($this->videoTags as $tag): ?>
       <?php if(!empty($tag->getTag()->text)):?>
  <a href="<?php echo $this->url(array('tag'=>$tag->getTag()->tag_id), 'sitereview_video_general', true) ?>"><?php endif;?>#<?php echo $tag->getTag()->text?></a>&nbsp;
  
     <?php endforeach; ?>
    <?php  endif; ?>
  </div>
  <div id="video_rating" class="rating" onmouseout="rating_out();">
    <span id="rate_1" class="rating_star_big_generic" <?php if (!$this->rated && $this->viewer_id):?>onclick="rate(1);"<?php endif; ?> onmouseover="rating_over(1);"></span>
    <span id="rate_2" class="rating_star_big_generic" <?php if (!$this->rated && $this->viewer_id):?>onclick="rate(2);"<?php endif; ?> onmouseover="rating_over(2);"></span>
    <span id="rate_3" class="rating_star_big_generic" <?php if (!$this->rated && $this->viewer_id):?>onclick="rate(3);"<?php endif; ?> onmouseover="rating_over(3);"></span>
    <span id="rate_4" class="rating_star_big_generic" <?php if (!$this->rated && $this->viewer_id):?>onclick="rate(4);"<?php endif; ?> onmouseover="rating_over(4);"></span>
    <span id="rate_5" class="rating_star_big_generic" <?php if (!$this->rated && $this->viewer_id):?>onclick="rate(5);"<?php endif; ?> onmouseover="rating_over(5);"></span>
    <span id="rating_text" class="rating_text"><?php echo $this->translate('click to rate');?></span>
  </div>

  <div class='sr_video_view_options'>
  	<?php if($this->can_create):?>
      <?php  echo $this->htmlLink(array('route' => "sitereview_video_create_listtype_$this->listingtype_id", 'listing_id' => $this->sitereview->listing_id,'content_id'=>$this->tab_selected_id), $this->translate('Add Video'), array('class' => 'buttonlink icon_sitereviews_video_new')) ?>
				&nbsp; | &nbsp;
		<?php endif; ?> 
        
		<?php if($this->video->owner_id == $this->viewer_id || $this->can_edit == 1): ?>
      <a href='<?php echo $this->url(array('listing_id' => $this->sitereview->listing_id,'video_id' => $this->video->video_id,'content_id' => $this->tab_selected_id), "sitereview_video_edit_listtype_$this->listingtype_id", true) ?>'  class='buttonlink seaocore_icon_edit'><?php echo $this->translate('Edit Video'); ?></a>
      &nbsp; | &nbsp;
      <?php echo $this->htmlLink(Array('route' => "sitereview_video_delete_listtype_$this->listingtype_id", 'listing_id' => $this->sitereview->getIdentity(),'video_id' => $this->video->video_id,'content_id' => $this->tab_selected_id), $this->translate("Delete Video"), array('class' => 'buttonlink seaocore_icon_delete')); ?>
			&nbsp; | &nbsp;
    <?php elseif($this->can_create):?>
    <?php endif; ?>
      
    <?php echo $this->htmlLink(Array('module'=> 'activity', 'controller' => 'index', 'action' => 'share', 'route' => 'default', 'type' => 'sitereview_video', 'id' => $this->video->getIdentity(), 'format' => 'smoothbox'), $this->translate("Share"), array('class' => 'smoothbox buttonlink seaocore_icon_share')); ?>&nbsp; | &nbsp;

    <?php if( $this->can_embed ): ?>
      <?php echo $this->htmlLink(Array('route' => "sitereview_video_embed_listtype_$this->listingtype_id", 'id' => $this->video->getIdentity(), 'format' => 'smoothbox'), $this->translate("Embed"), array('class' => 'smoothbox buttonlink icon_sitereview_video_embed')); ?>&nbsp; | &nbsp; 
	  <?php endif ?>
    
    <?php echo $this->htmlLink(Array('module'=> 'core', 'controller' => 'report', 'action' => 'create', 'route' => 'default', 'subject' =>  $this->video->getGuid(), 'format' => 'smoothbox'), $this->translate("Report"), array('class' => 'smoothbox buttonlink seaocore_icon_report')); ?>
  </div>
  <div id="comments">
    <?php 
        include_once APPLICATION_PATH . '/application/modules/Seaocore/views/scripts/_listComment.tpl';
    ?>
  </div>
</div>