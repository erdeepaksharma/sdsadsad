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

<div id="usertextboxcontent" style="display:none;">
  <?php echo nl2br($this->aboutSubject); ?>
</div>

<script language="JavaScript">
 
 var usertextboxcontent = $('usertextboxcontent').innerHTML;
  function writeAbout() {
    $("write").style.display="none";
    $("write1").style.display="block";
    $('fname').focus();
  }
 
  function saveAbout()
  {
    var str = document.getElementById('fname').value.replace(/\n/g,'<br />');
    var str_temp = document.getElementById('fname').value;

    $('write1').style.display="none";
    $('id_saveimage').style.display="block"; 

		en4.core.request.send(new Request.HTML({
			url : en4.core.baseUrl + 'sitereview/index/display/listingtype_id/'+'<?php echo $this->listingTypeId ?>',
			data : {
				format : 'html',
				strr : str_temp,
				subjectId : '<?php echo $this->subjectId; ?>',
        subjectType : '<?php echo $this->subject->getType();?>'
			},
			onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
				if (str == '') {
					str = "<div class='write_link b_medium'><span onclick='writeAbout()'>" + 'Write something about '+ '<?php echo $this->string()->escapeJavascript($this->subject->getTitle());?>' + "</span></div>";
					$('write').innerHTML = str
				}
				else {
						$('write').innerHTML = '<div class="des_edit b_medium"><div class="edit_icon"><a  href="javascript:void(0);" onclick="writeAbout()"></a></div><div class="des">' + str +' </div></div>';
					}
				usertextboxcontent = str; 
				$('write').style.display="block";
				$('id_saveimage').style.display="none"; 
			}
		}));
  }
</script>

<?php if($this->isOwner): ?>
	<div class="sr_write_about">
		<div id="write">
		<?php echo '<div class="des_edit b_medium"><div class="edit_icon"><a href="javascript:void(0);" onclick="writeAbout()"></a></div><div class="des">' .htmlspecialchars_decode(nl2br($this->aboutSubject),  ENT_QUOTES).'</div></div>' ?>
		</div>
		
		<div style='display:none;' id="write1" >
		  <div class="textarea">
		  	<textarea class="b_medium" rows="2" cols="10" onblur="saveAbout()" id="fname" style='display:block;'><?php echo $this->aboutSubject;?></textarea>
		  </div>
		  <div class="edit_icon">
		  	<a href="javascript:void(0);" onclick="writeAbout()"></a>
		  </div>	
		</div>

		<div class="des_edit b_medium" style='display:none;' id="id_saveimage">
			<center>
		  	<img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/loading.gif" alt="" />
			</center>
		</div>
	</div>
<?php elseif(!empty($this->aboutSubject)): ?>
	<div class="sr_write_about">
		<div class="details b_medium" id="write">
			<?php echo htmlspecialchars_decode(nl2br($this->aboutSubject),  ENT_QUOTES) ;?>
		</div>
	</div>
<?php endif; ?>

<script language="JavaScript">
    window.addEvent('load',function() {
        
         <?php if(empty($this->aboutSubject)): ?>
			
            var content = "<div class='write_link b_medium'><span onclick='writeAbout()'>" + 'Write something about '+ '<?php echo $this->string()->escapeJavascript($this->subject->getTitle());?>' + "</span></div>";
            if( $('write') )
	    $('write').innerHTML = content;
         <?php endif; ?>
        
    });
    
    </script>