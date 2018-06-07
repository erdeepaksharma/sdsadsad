<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: create.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<h2>
<?php echo $this->translate("ADVANCED_ACTIVITY_PLUGIN_NAME") . " " . $this->translate("Plugin") ?>
</h2>

    <?php if (count($this->navigation)): ?>
    <div class='seaocore_admin_tabs'>
        <?php
        // Render the menu
        //->setUlClass()
        echo $this->navigation()->menu()->setContainer($this->navigation)->render()
        ?>
    </div>
<?php endif; ?>
 <?php 
 $this->navigationAAF = $navigationAAF = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main_settings', array(), 'advancedactivity_admin_main_settings_banner');
   
 ?> 

<?php if (count($this->navigationAAF)): ?>
  <div class='seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigationAAF)->render() ?>
  </div>
<?php endif; ?>
<div class="seaocore_settings_form">
    <div class='settings'>
<?php echo $this->form->render($this); ?>
    </div>
</div>
<script>
    var set = true;
    en4.core.runonce.add(function(){
        toggleBannerGradient('<?php echo $this->item->gradient ? 1 : 0 ?>');
            if($('banner')){
                showPreview('<?php echo $this->image ?>','<?php echo $this->item->background_color ?>','<?php echo $this->item->color ?>'); 
                $('banner').addEventListener("change", function(event){

                var files = event.target.files;

                for(var i = 0; i< files.length; i++)
                {
                    var file = files[i];

                    if(!file.type.match('image'))
                      continue;

                    var bannerReader = new FileReader();

                    bannerReader.addEventListener("load",function(event){
                        var bannerImage = event.target;
                        showPreview(bannerImage.result,null,null);
                    });
                    bannerReader.readAsDataURL(file);
                }                               

            });
        }
        if($('background_color')){
           $('background_color').addEventListener('input',function(e){ showPreview(null,e.target.value,null); });
           $('background_color').value = '<?php echo $this->item->background_color ?>';
        }
        if($('color')){
           $('color').addEventListener('input',function(e){ showPreview(null,null,e.target.value); });
           $('color').value = '<?php echo $this->item->color ?>';
        }
        <?php if(!empty($this->item->gradient)):  ?>
            $('preview_banner').setStyle('background','<?php echo $this->item->gradient ?>');
        <?php endif; ?>
       });
       function showPreview(image,bgcolor,color){
           if(!$('preview_banner')) { 
                var div = document.createElement("div");
                div.id = 'preview_banner';
                div.innerHTML = "<span style='font-weight:600'>"+en4.core.language.translate('Preview will look like this.')+"</span>";
                div.setStyles({ 'background': '#09e89e','color':'#ffffff','width':'67%','padding':'110px 30px','float':'right','background-size': 'cover','text-align':'center','box-sizing':'border-box','font-size':'25px','font-weight':'bold','margin-top':'-35px'});
                $("gradient_enabled-element").insertBefore(div,null);
            }else {
                var div = $('preview_banner');
            }
            
            if(image){
                div.setStyles({ 'background-image': 'url('+image+')','padding':'110px 30px','width':'67%','background-size': 'cover','text-align':'center','box-sizing':'border-box','font-size':'25px','font-weight':'bold'});
                set = false;
            }else if(bgcolor && set){
                div.setStyles({ 'background': bgcolor});
            }
            if(color){
                div.setStyles({ 'color': color});
            } 
       }
       function toggleBannerGradient(toggle){
            if(toggle == 1){
                $('gradient-wrapper').setStyles({'display':'block'});
                $('banner-wrapper').setStyles({'display':'none'});
                $('banner').value = null;
            } else {
                $('gradient-wrapper').setStyles({'display':'none'});
                $('banner-wrapper').setStyles({'display':'block'});
                $('gradient').value = null;
            }
            if($('preview_banner')) $('preview_banner').setStyles({ 'background': '#09e89e'});
       }

    
</script>