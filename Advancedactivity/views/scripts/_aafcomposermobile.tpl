<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: _aafcomposer.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>
<?php
$is_iphone = false;
if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) :
  $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
  $version = preg_replace("/(.*) OS ([0-9]*)_(.*)/","$2", $_SERVER['HTTP_USER_AGENT']); 
  if (preg_match('/iphone/i', $useragent) && $version < 8) {
    $is_iphone = true;
  }
endif;
?>
<script type='text/javascript'>
    var showVariousTabs = '<?php echo $this->showTabs; ?>';

<?php if (Engine_Api::_()->hasModuleBootstrap('sitealbum') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sitealbum.open.lightbox.upload', 1)): ?>
        var showAddPhotoInLightbox = "<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sitealbum.open.lightbox.upload', 1); ?>";
<?php else: ?>
        var showAddPhotoInLightbox = 0;
<?php endif; ?>
</script>
  <style type="text/css"> 
  #compose-photo-activator,
  #compose-sitepagephoto-activator,
  #compose-sitegroupphoto-activator,
  #compose-sitestorephoto-activator,
  #compose-sitebusinessphoto-activator{
    display: inline-block !important;
  }
  </style>
<?php
  $this->headLink()
  	->prependStylesheet($this->layout()->staticBaseUrl.'application/modules/Advancedactivity/externals/styles/mobile_statusbar.css');
?>
<?php if( $this->enableComposer ): ?>
  
  <?php
    $this->headScript()
            ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/scripts/seaomooscroll/SEAOMooHorizontalScrollBar.js')
    ;
    ?>
  <div class="activity-post-container" id="activity-post-container">
<?php $composerOptions= Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.composer.options',
			array("withtags", "emotions", "userprivacy")); ?>
    <form method="post" action="<?php echo $this->url(array('module' => 'advancedactivity', 'controller' => 'index', 'action' => 'post'), 'default', true) ?>" class="activity" enctype="application/x-www-form-urlencoded" id="activity-form">
      <textarea id="activity_body" cols="1" rows="1" name="body"></textarea>
      <input type="hidden" name="return_url" value="<?php echo $this->url() ?>" />
      <input type="hidden" name="activity_type" value="1" />
      <?php if( $this->viewer() && $this->subject() && !$this->viewer()->isSelf($this->subject())): ?>
        <input type="hidden" name="subject" value="<?php echo $this->subject()->getGuid() ?>" />
      <?php endif; ?>
      <?php if( $this->formToken ): ?>
        <input type="hidden" name="token" value="<?php echo $this->formToken ?>" />
      <?php endif ?>
        <div id="adv_post_container_icons"></div>
        <div class="compose-menu_before" >                    <?php 
        $SEA_EMOTIONS_TAG = unserialize(SEA_EMOTIONS_TAG);	        
	      if (in_array("emotions",$composerOptions) && $SEA_EMOTIONS_TAG && isset ($SEA_EMOTIONS_TAG[0])): ?>
				<span id="emoticons-button"  class="adv_post_smile"  onclick="setEmoticonsBoard()">
					<span id="emoticons-board"  class="seaocore_embox seaocore_embox_closed" >
	        	<span class="seaocore_embox_arrow"><img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/tooltip_arrow_top.png" alt="" /></span>
	        	<span class="seaocore_embox_title">
	        		<span class="fleft" id="emotion_lable"></span>
	        		<span class="fright"id="emotion_symbol" ></span>
	        		</span>
		         <?php foreach ($SEA_EMOTIONS_TAG[0] as $tag_key=>$tag):?>         
            <span class="seaocore_embox_icon" onmouseover='setEmotionLabelPlate("<?php echo $this->string()->escapeJavascript($this->translate(preg_replace("/__([^_]*)__([^_]*)__([^_]*)__/","$3", $tag)))?>","<?php echo $this->string()->escapeJavascript($tag_key)?>")' onclick='addEmotionIcon("<?php echo $this->string()->escapeJavascript($tag_key)?>")'  title="<?php echo $this->translate(preg_replace("/__([^_]*)__([^_]*)__([^_]*)__/","$3", $tag))."&nbsp;".$tag_key; ?>"><?php 
		            echo preg_replace("/__([^_]*)__([^_]*)__([^_]*)__/", "<img src=\"".$this->layout()->staticBaseUrl."application/modules/Seaocore/externals/emoticons/$1\" border=\"0\" alt=\"$2\" />", $tag);              
		          ?></span>
		        <?php endforeach;?>
	        </span>					
				</span>
				 <?php endif; ?>
      </div>
      <div id="compose-menu" class="compose-menu" >
        <button id="compose-submit" type="submit"><?php echo $this->translate("ADVADV_SHARE") ?></button>
        <div id="show_loading_main" class="show_loading" style="display:none;">140</div>
         	<?php if ($this->showPrivacyDropdown): ?> 
           <?php $content = (isset ($this->availableLabels[$this->showDefaultInPrivacyDropdown]) || !empty($this->privacylists) ) ? $this->showDefaultInPrivacyDropdown : $this->settingsApi->getSetting('activity.content', 'everyone');?> 
              <?php $availableLabels = $this->availableLabels; ?>
              <?php if (count($this->privacylists) > 1):
                  $content = "friends";
                endif;
                
              ?>
            <select name="auth_view" value="<?php echo $content; ?>">
              <?php foreach ($availableLabels as $key => $value): ?>
              <option value="<?php echo $key ?>" <?php if($content==$key): ?>selected="selected"<?php endif;?> > <?php echo $this->translate($value); ?></option>
              <?php endforeach; ?>
               <?php if( $this->enableList):?>                  
              <?php foreach( $this->lists as $list ): ?>
              <option value="<?php echo $list->list_id; ?>" <?php if($content==$list->list_id): ?>selected="selected"<?php endif;?>> <?php echo $this->translate($list->getTitle()) ?></option>
              <?php endforeach; ?>
               <?php endif; ?>
               <?php if ($this->enableNetworkList): ?>
              <?php  foreach ($this->network_lists as $list):?>
              <option value="<?php echo "network_".$list->getIdentity(); ?>" <?php if($content=="network_".$list->getIdentity()): ?>selected="selected"<?php endif;?> > <?php echo $this->translate($list->getTitle()) ?></option>
              <?php endforeach; ?>
              <?php endif; ?>
            </select>
          <?php else: ?>
            <?php $content = $this->settingsApi->getSetting('activity.content', 'everyone'); ?>       
            <input type="hidden" id="auth_view" name="auth_view" value="<?php echo $content; ?>" />
          <?php endif; ?>                  
				 <div class="aaf_cm_sep"></div>
      </div>
    </form>

    <?php
      $this->headScript()
        ->appendFile($this->layout()->staticBaseUrl . 'externals/mdetect/mdetect' . ( APPLICATION_ENV != 'development' ? '.min' : '' ) . '.js')
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/composer-core.js');
    ?>
    
    <script type="text/javascript">
      var Share_Translate="<?php echo $this->string()->escapeJavascript($this->translate("ADVADV_SHARE")); ?>";
      var Who_Are_You_Text="<?php echo $this->string()->escapeJavascript($this->translate("Who are you with?")); ?>";
      var composeInstance;
      en4.core.runonce.add(function() {
       en4.core.language.addData({
      "with":"<?php echo $this->string()->escapeJavascript($this->translate("with"));?>",
      "and":"<?php echo $this->string()->escapeJavascript($this->translate("and"));?>",
      "others":"<?php echo $this->string()->escapeJavascript($this->translate("others"));?>"
   });
        // @todo integrate this into the composer
       // if( !DetectMobileQuick() && !DetectIpad() ) {
          composeInstance = new Composer('activity_body', {
            menuElement : 'compose-menu',
            baseHref : '<?php echo $this->baseUrl() ?>',
            lang : {
              'Post Something...' : '<?php echo $this->string()->escapeJavascript($this->translate('Post Something...')) ?>'
            },
            overText : false,   
            hideSubmitOnBlur : false,   
            useContentEditable : false
          });
          
//        composeInstance.getForm().addEvent('submit', function(e) {
//        composeInstance.fireEvent('editorSubmit');
//      }.bind(this));
      //  }
      });
      
       <?php  if (in_array("emotions",$composerOptions) ) : ?>
  var hideEmotionIconClickEnable=false;
   function setEmoticonsBoard(){
   if(composeInstance)
    composeInstance.focus();
   $('emotion_lable').innerHTML="";
   $('emotion_symbol').innerHTML="";
      hideEmotionIconClickEnable=true;    
      var  a=$('emoticons-button');
        a.toggleClass('emoticons_active');
        a.toggleClass('');
      var  el=$('emoticons-board');
        el.toggleClass('seaocore_embox_open');
        el.toggleClass('seaocore_embox_closed'); 
    }

   function addEmotionIcon(iconCode){ 
     var content =composeInstance.getContent();
        content=content.replace(/(<br>)$/g, "");
        content =  content +' '+ iconCode; 
       composeInstance.setContent(content);
            
    }
     //hide on body click
    // $(document.body).addEvent('click',hideEmotionIconClickEvent.bind());
   function hideEmotionIconClickEvent(){
     if(!hideEmotionIconClickEnable){       
        $('emoticons-board').removeClass('seaocore_embox_open').addClass('seaocore_embox_closed');      
     }
     hideEmotionIconClickEnable=false;
   }  
   function setEmotionLabelPlate(lable,symbol){
    $('emotion_lable').innerHTML=lable;
    $('emotion_symbol').innerHTML=symbol;
   }
   <?php endif; ?>
    </script>
    
    <?php if(in_array("withtags",$composerOptions) && (empty ($this->subjectGuid) || $this->subject()->getType()== "user" )): echo $this->partial('_composeAddpeopletagmobile.tpl', 'advancedactivity', array("isAAFWIDGETMobile"=>1));    endif; ?>
    <?php foreach( $this->composePartials as $partial ): ?>
      <?php echo $this->partial($partial[0], $partial[1], array("isAAFWIDGETMobile"=>1, 'composerType' => 'activity')) ?>
    <?php endforeach; ?>

  </div>
<?php endif; ?>