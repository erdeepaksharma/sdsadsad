<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: feed-settings.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<script type="text/javascript">
    function showHideDaysOption(value){
      if(value==0){
          $('advancedactivity_pin_reset_days-wrapper').hide();
      } else{
          $('advancedactivity_pin_reset_days-wrapper').show();
      }
  }
  
  function showHideFeedLengthOption(value){
      if(value==0){
          $('advancedactivity_feed_char_length-wrapper').hide();
          $('advancedactivity_feed_font_size-wrapper').hide();
      } else{
          $('advancedactivity_feed_char_length-wrapper').show();
          $('advancedactivity_feed_font_size-wrapper').show();
      }
  }
  
  //  window.addEvent('domready', function() {
  //     set = $('aaf_pinunpin_enable-1').checked ? 1 : 0;
  //     showHideDaysOption(set);
  // });
</script>
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
 $this->navigationAAF = $navigationAAF = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main_settings', array(), 'advancedactivity_admin_main_settings_feed');
   
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
 <script type="text/javascript">
   en4.core.runonce.add(function(){
    var bannerUl = new Element('ul', {
      'id' : 'admin-banner-wrapper',
      'class' : 'admin-banner-wrapper',
    }).inject($('advancedactivity_feed_banners-element'));
    var images = JSON.parse('<?php echo $this->banners ?>');
    for(i in images){
          var img = new Element('li', {
          'class' : 'admin-banner-image',
          'title' : 'View this banner',
          'data-source' : images[i],
          'styles' :{
            'background':'url('+en4.core.baseUrl+'application/modules/Advancedactivity/externals/images/banner/'+images[i]+')',
            'width':'100px',
            'height':'100px'
          }, 
        'events' : {
            'click' : function(e) {
              content = '<img src="'+en4.core.baseUrl+'application/modules/Advancedactivity/externals/images/banner/'+e.target.get('data-source')+'"  width="500px" height="350px" />';
              Smoothbox.open('<div><a href="javascript:void(0)" onclick="parent.Smoothbox.close()" style="float:right;">X</a>'+content+'</div>');
            }.bind(this)
          }
        }).inject(bannerUl);
        new Element('a', {
          'class' : 'admin-banner-image-close',
          'data-source' : images[i],
          'html' : '',
          'title' : 'Remove this banner',
          'styles' :{
            'font-weight':'bold',
            'float' : 'right'
          },
          'events' : {
            'click' : function(e) {
              e.stop();
              var remove = confirm('Do you really want to remove this banner?')
              if(remove){
                e.target.getParent().setStyle('display','none');
                removeBanner(e.target.get('data-source'));
              }
            }.bind(this)
          }
      }).inject(img);
    }
   });
   function removeBanner(banner){
      var url = en4.core.baseUrl + 'admin/advancedactivity/settings/remove-banner/';
      var request = new Request.JSON({
          'url' : url,
          'method':'post',
          'data' : {
          'format' : 'json',
          'banner' : banner,
          },
          'onSuccess':function(responseJSON,responseHTML){
            if (!responseJSON.delete) {
              alert('Some problem occured :( ');
            }            
          }
         });
    request.send();
   }
 </script>
 <style type="text/css">
   #advancedactivity_feed_banners-element > ul > li {
      display: inline-block;
      padding: 15px;
   }
 </style>