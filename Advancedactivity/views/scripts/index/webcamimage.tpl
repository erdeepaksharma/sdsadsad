<?php
	$this->headLink()
  	->prependStylesheet($this->layout()->staticBaseUrl .'application/modules/Advancedactivity/externals/styles/style_advancedactivity.css');
?>
<?php
$this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/jpegcam/htdocs/webcam.js');
$base_url = Zend_Controller_Front::getInstance()->getBaseUrl();

// For "Activity - Album Photo".
$zendJson = Zend_Json::encode(array('src' => $this->src, 'photo_id' => $this->photo_id ));
if( !empty($this->status) && !empty($this->photo_id) && !empty($this->album_id) ) {
?>
  <script language="JavaScript">
    window.parent.composeInstance.plugins.each(function(plugin){
      if( plugin.name == 'photo'  ) {
	plugin.doProcessResponse(<?php echo $zendJson; ?>);
	window.parent.$('compose-webcam-body').style.visibility = 'hidden';
	parent.Smoothbox.close();
      }
    });
  </script>
<?php
}

// For "Member Profile Photo".
if( !empty($this->tem_file_name) ) {
?>
<script language="JavaScript">
    window.parent.document.getElementById('user_preview').src = '<?php echo $this->tem_file_name; ?>';
    parent.Smoothbox.close();
</script>
<?php } ?>


<div class="webcam_box">
<h3><?php 
  if( strstr($this->webcam_type, 'profile_photo') ){
    echo $this->translate("Take a Profile Picture");
  }else if( strstr($this->webcam_type, 'album_photo') ){
    echo $this->translate("Take a photo to share");
  }
?></h3>
<div id="temp_upload_image">
  <!-- Configure a few settings -->
  <script language="JavaScript">
    var baseURL = "<?php echo $base_url; ?>";
    webcam.set_api_url(baseURL + '/seaocore/index/uploadcamimage');
    webcam.set_quality( 100 ); // JPEG quality (1 - 100)
    webcam.set_shutter_sound( false ); // play shutter click sound
  </script>
  
  <!-- Next, write the movie to the page at 320x240 -->
  <script language="JavaScript">
    document.write( webcam.get_html(600, 480) );
  </script>

  <form class="webcam_box_buttons">
    <button type=button value="Capture" id="webcam_capture" onClick="webcam.freeze()" style="display:inline"><?php echo $this->translate("Capture"); ?></button>
    <button type="button" value="Reset" id="webcam_reset" onClick="webcam.reset()" style="display:none"><?php echo $this->translate("Re-take"); ?></button>
    <button type=button value="Save" id="webcam_save" onClick="do_upload()" style="display:none"><?php echo $this->translate("Save"); ?></button>
    <?php echo $this->translate("or"); ?>&nbsp;
    <a href="javascript:void(0)" onClick="parent.Smoothbox.close()"><?php echo $this->translate("Cancel"); ?></a>
  </form>
</div>

<script language="JavaScript">
  webcam.set_hook( 'onComplete', 'my_completion_handler' );
  
  function do_upload() {
	  webcam.upload();
  }
  
  function my_completion_handler(msg) {
  document.getElementById("temp_upload_image").innerHTML = "<div class='webcam_box_loading'><div class='aaf_main_container_lodding' style='margin-bottom:0px;margin-top:230px;'></div></div>";
  window.location.reload();
  }
</script>