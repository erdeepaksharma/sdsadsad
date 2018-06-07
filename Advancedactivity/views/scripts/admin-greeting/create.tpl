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
<h2>
<?php echo $this->translate("ADVANCED_ACTIVITY_PLUGIN_NAME") . " " . $this->translate("Plugin") ?>
</h2>
<?php if( count($this->navigation) ): ?>
  <div class='seaocore_admin_tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>

<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'advancedactivity', 'controller' => 'greeting'), $this->translate("Back to Manage Greeting / Announcement"), array('class' => 'seaocore_icon_back buttonlink')) ?>
<br style="clear:both;" /><br />
<div class="seaocore_settings_form">
	<div class='settings'>
	  <?php echo $this->form->render($this); ?>
	</div>
</div>	
<!-- <script>
    function showHide(value){
        if(value==1){
            $('starttime-wrapper').style.display='none';
            $('endtime-wrapper').style.display='none';
            
        } else{
            $('starttime-wrapper').style.display='block';
            $('endtime-wrapper').style.display='block';
        }
    }
    </script> -->
<script type="text/javascript">
    function showPreview(){
        if (tinyMCE.get('body').getContent()) {
            var content = "<div>"+tinyMCE.get('body').getContent()+"</div><br /><button onclick='parent.Smoothbox.close()'>Close</button>";
        } else {
            var content = "<div>No preview available as you have not added any content for this new greeting / announcement.</div><br /><button onclick='parent.Smoothbox.close()'>Close</button>";
        }
        Smoothbox.open(content);
    }
    function showHide(value){
        if(value==1){
            $$('.event_calendar_container').each(function(item){
                item.style.display= 'none';
                });
            
        } else{
            $$('.event_calendar_container').each(function(item){
                item.style.display= 'block';
                });
        }
    }
    
    var cal_starttime_onHideStart = function() {
        
        var cal_bound_start = document.getElementById('starttime-date').value;
        console.log(cal_bound_start);
        console.log(document.getElementById('starttime-date').value);
        // check end date and make it the same date if it's too
        cal_endtime.calendars[0].start = new Date(cal_bound_start);
        // redraw calendar
        cal_endtime.navigate(cal_endtime.calendars[0], 'm', 1);
        cal_endtime.navigate(cal_endtime.calendars[0], 'm', -1);
    }
    var cal_endtime_onHideStart = function() {
        
        var cal_bound_start = en4.seaocore.covertdateDmyToMdy(document.getElementById('endtime-date').value);
        // check start date and make it the same date if it's too
        cal_starttime.calendars[0].end = new Date(cal_bound_start);
        // redraw calendar
        cal_starttime.navigate(cal_starttime.calendars[0], 'm', 1);
        cal_starttime.navigate(cal_starttime.calendars[0], 'm', -1);
    }
</script>