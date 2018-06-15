<?php 
if(!Engine_Api::_()->sitemobile()->isApp()) {  
$breadcrumb = array(
    array("href"=>$this->sitereview->getHref(),"title"=>$this->sitereview->getTitle(),"icon"=>"arrow-r"),
    array("href"=>$this->sitereview->getHref(array('tab' => $this->tab_selected_id)),"title"=>"Photos","icon"=>"arrow-d"),
 );

echo $this->breadcrumb($breadcrumb);
}
?>
<div class="layout_middle">
  <?php echo $this->form->render($this) ?>
</div>	


<script type="text/javascript">

  sm4.core.runonce.add(function() { 
    if (DetectAllWindowsMobile()) {
      $.mobile.activePage.find('#form-upload').css('display', 'none');
      $.mobile.activePage.find('#show_supported_message').css('display', 'block');
    } else {
      $.mobile.activePage.find('#form-upload').css('display', 'block');
      $.mobile.activePage.find('#show_supported_message').css('display', 'none');
    } 
  });

</script>


<div style="display:none" id="show_supported_message" class='tip'>

  <span><?php echo $this->translate("Sorry, the browser you are using does not support Photo uploading. You can create an album from your Desktop."); ?><span>

</div>