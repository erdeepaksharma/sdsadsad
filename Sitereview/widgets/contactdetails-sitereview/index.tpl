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

<?php if ($this->show_phone && isset($this->options_create['phone']) && $this->options_create['phone'] == 'Phone') : ?>    
	<?php if (empty($this->sitereview->phone) && $this->viewer()->getIdentity() && $this->can_edit) : ?>
		<div class="sitereview_contect_field" onmouseover="displayImage('phone');" onmouseout="hideImage('phone');">
			<img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/icons/mobile.png" alt="Phone" />
			<div class="sitereview_contect_det">        
				<input type="text" name="phone" value="<?php if(empty($this->sitereview->phone)): ?><?php echo $this->translate("Contact Number");?><?php else:?><?php echo $this->sitereview->phone ?><?php endif;?>" id="phone" onblur="saveContactDetails('phone')" onclick="onFocus('phone')" />   
				<div id="showPhoneNumber" style="display:none;"></div> 
			</div>  
			<?php if ($this->can_edit) : ?>	
				<div class="edit_icon" id="phoneimage" style="display:none;" title="<?php echo $this->translate('edit');?>">
					<a href="javascript:void(0);" onclick="onFocus('phone')"></a>
				</div>	
			<?php endif; ?>
		</div>   
	<?php elseif (!empty($this->sitereview->phone)) : ?>
		<div class="sitereview_contect_field" onmouseover="displayImage('phone');" onmouseout="hideImage('phone');">
			<img src="<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/icons/mobile.png" alt="Phone" />
			<div class="sitereview_contect_det">
				<input type="text" name="phone" value="<?php echo $this->sitereview->phone ?>" id="phone" onblur="saveContactDetails('phone')" style="display:none;" onclick="onFocus('phone')" />  
				<div id="showPhoneNumber" style="display:block;"><?php echo $this->sitereview->phone ?></div>     
			</div>
			<?php if ($this->can_edit) : ?>	
				<div class="edit_icon" id="phoneimage" style="display:none;" title="<?php echo $this->translate('edit');?>">
					<a href="javascript:void(0);" onclick="onFocus('phone')"></a>
				</div>	
			<?php endif; ?>
		</div>
	<?php endif; ?>
<?php endif; ?>

<?php if ($this->show_email && isset($this->options_create['email']) && $this->options_create['email'] == 'Email') : ?>   
	<?php if (empty($this->sitereview->email) && $this->viewer()->getIdentity() && $this->can_edit) : ?>
		<div class="sitereview_contect_field" onmouseover="displayImage('email');" onmouseout="hideImage('email');">
			<img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/invite.png" alt="E-mail" />
			<div class="sitereview_contect_det">
				<input type="text" name="email" value="<?php if(empty($this->sitereview->email)): ?><?php echo $this->translate("Email ID");?><?php else:?><?php echo $this->sitereview->email ?><?php endif;?>" id="email" onblur="saveContactDetails('email')" onclick="onFocus('email')" />
				<div id="showEmailAddress" style="display:none;"></div>
			</div>
			<?php if ($this->can_edit) : ?>
				<div id="showerrormessage" style="display:none;" class="sitereview_contect_error">  </div> 
				<div class="edit_icon" id="emailimage" style="display:none;" title="<?php echo $this->translate('edit');?>">
					<a href="javascript:void(0);" onclick="onFocus('email')"></a>
				</div>  
			<?php endif; ?>
		</div>
	<?php elseif (!empty($this->sitereview->email)) : ?>
		<div class="sitereview_contect_field" onmouseover="displayImage('email');" onmouseout="hideImage('email');">
			<img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/invite.png" alt="E-mail" />
			<div class="sitereview_contect_det">
				<input type="text" name="email" value="<?php echo $this->sitereview->email ?>" id="email" onblur="saveContactDetails('email')"  style="display:none;" onclick="onFocus('email')" />
				<div id="showEmailAddress">       
					<a href='mailto:<?php echo $this->sitereview->email ?>'><?php echo $this->translate('Email Me') ?></a>
				</div>
			</div>        
			<?php if ($this->can_edit) : ?>
				<div id="showerrormessage" style="display:none;" class="sitereview_contect_error">  </div> 
				<div class="edit_icon" id="emailimage" style="display:none;" title="<?php echo $this->translate('edit');?>">
					<a href="javascript:void(0);" onclick="onFocus('email')"></a>
				</div>  
			<?php endif; ?>
		</div>
	<?php endif; ?>
<?php endif; ?>

<?php if ($this->show_website && isset($this->options_create['website']) && $this->options_create['website'] == 'Website') : ?>    
	<?php if (empty($this->sitereview->website) && $this->viewer()->getIdentity() && $this->can_edit) : ?>
		<div class="sitereview_contect_field" onmouseover="displayImage('website');" onmouseout="hideImage('website');">
			<img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/icons/web.png" alt="Website" />
			<div class="sitereview_contect_det">          
				<input type="text" name="website" value="<?php if(empty($this->sitereview->website)): ?><?php echo $this->translate("Website");?><?php else:?><?php echo $this->sitereview->website ?><?php endif;?>" id="website" onblur="saveContactDetails('website')" onclick="onFocus('website')" />        
				<div id="showWebsite" style="display:none;"></div>
			</div>
			<?php if ($this->can_edit) : ?>
				<div class="edit_icon" id="websiteimage" style="display:none;" title="<?php echo $this->translate('edit');?>">
					<a href="javascript:void(0);" onclick="onFocus('website')"></a>
				</div>
			<?php endif; ?>
		</div>
	<?php elseif (!empty($this->sitereview->website) ) : ?>
		<div class="sitereview_contect_field" onmouseover="displayImage('website');" onmouseout="hideImage('website');">
			<img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/icons/web.png" alt="Website" />
			<div class="sitereview_contect_det">
				<input type="text" name="website" value="<?php echo $this->sitereview->website ?>" id="website" onblur="saveContactDetails('website')"  style="display:none;" onclick="onFocus('website')" />          
				<div id="showWebsite">   
					<?php if (strstr($this->sitereview->website, 'http://') || strstr($this->sitereview->website, 'https://')): ?>
						<a href='<?php echo $this->sitereview->website ?>' target="_blank" title='<?php echo $this->sitereview->website ?>' ><?php echo $this->translate(''); ?> <?php echo $this->translate('Visit Website') ?></a>
					<?php else: ?>
						<a href='http://<?php echo $this->sitereview->website ?>' target="_blank" title='<?php echo $this->sitereview->website ?>' ><?php echo $this->translate(''); ?> <?php echo $this->translate('Visit Website') ?></a>
					<?php endif; ?>
				</div>
			</div>
			<?php if ($this->can_edit) : ?>
				<div class="edit_icon" id="websiteimage" style="display:none;" title="<?php echo $this->translate('edit');?>">
					<a href="javascript:void(0);" onclick="onFocus('website')"></a>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>    
<?php endif;?>

<script type="text/javascript">
 
  function displayImage(showimage) {
    if(showimage == 'phone') {
      if($('phoneimage'))
        $('phoneimage').style.display = "block";      
    } 
    else if(showimage == 'email') {
      if($('emailimage'))
        $('emailimage').style.display = "block";
    } 
    else if(showimage == 'website') {
      if($('websiteimage'))
        $('websiteimage').style.display = "block";
    }
  }
  
  function hideImage(hideImage) {
    if(hideImage == 'phone') {
      if($('phoneimage'))
        $('phoneimage').style.display = "none";
    } 
    else if(hideImage == 'email') {
      if($('emailimage'))
        $('emailimage').style.display = "none";
    } 
    else if(hideImage == 'website') {
      if($('websiteimage'))
        $('websiteimage').style.display = "none";
    }
  }
  
  function onFocus(focusid){
    if(focusid == 'phone') {  
      if($('showPhoneNumber'))
        $('showPhoneNumber').style.display = "none";
      if($('phone')) {
        if($('phone').value == '<?php echo $this->string()->escapeJavascript($this->translate("Contact Number")) ?>') {
          $('phone').value="";
        }
        $('phone').style.display = "block";
        $('phone').focus();
      }
    }
    else if(focusid == 'email') {
      if($('email')) {
        if($('email').value == '<?php echo $this->string()->escapeJavascript($this->translate("Email ID")) ?>') {
          $('email').value="";
        }
        $('email').style.display = "block";
        $('email').focus();  
      }
      if($('showEmailAddress'))
        $('showEmailAddress').style.display = "none";
    }
    else if(focusid == 'website') {
      if($('website')) {
        if($('website').value == '<?php echo $this->string()->escapeJavascript($this->translate("Website")) ?>') {
          $('website').value="";
        }
        $('website').style.display = "block";
        $('website').focus();  
      }
      if($('showWebsite'))
        $('showWebsite').style.display = "none";      
    }
  }
  
  function validateEmail(email) { 
    if(email != '') {
      var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
      return filter.test(email);
    } else {
      return true;
    }
  }  

  function saveContactDetails(blurid) {
    if(blurid == 'phone') {     
      if($('phoneimage'))
        $('phoneimage').src = "<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/edit.png";
    } 
    else if(blurid == 'email') {     
      if(validateEmail($('email').value)){
        if($('emailimage'))
          $('emailimage').src = "<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/edit.png";
      } else {
        if($('showerrormessage')) {
          $('showerrormessage').style.display = "block";
          $('showerrormessage').innerHTML = '<?php echo $this->string()->escapeJavascript($this->translate("Please enter a valid email address.")) ?>';
        }
        return false;
      }      
    } 
    else if(blurid == 'website') {
      if($('websiteimage')) {
        $('websiteimage').src = "<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/edit.png";
      }      
    }
    
    if($('phone')) {
      var phone = $('phone').value;        
      if($('phone').value == '') { 
        $('phone').value = '<?php echo $this->string()->escapeJavascript($this->translate("Contact Number")) ?>';
      }
  
      if(phone == '<?php echo $this->string()->escapeJavascript($this->translate("Contact Number")) ?>') {
        phone="";
      }      
    }
    if($('email')) {
      var email = $('email').value;
      if($('email').value == '') { 
        $('email').value = '<?php echo $this->string()->escapeJavascript($this->translate("Email ID")) ?>';        
      }
      
      if(email == '<?php echo $this->string()->escapeJavascript($this->translate("Email ID")) ?>') {
        email="";
      } 
    }   
   
    if($('website')) {
      var website = $('website').value;
      if($('website').value == '') { 
        $('website').value = '<?php echo $this->string()->escapeJavascript($this->translate("Website")) ?>';       
      }
      
      if(website == '<?php echo $this->string()->escapeJavascript($this->translate("Website")) ?>') {
        website="";
      } 
    }    

    en4.core.request.send(new Request.HTML({
      url :'<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'dashboard', 'action' => 'contact'), 'default', true) ?>',
      data : {
        format : 'html',
        phone : phone,
        email : email,
        website : website,
        listing_id : '<?php echo $this->sitereview->listing_id ?>',
        isajax: 1
      },
      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {  

        if(blurid == 'phone') {
          if($('showPhoneNumber')) {
            $('showPhoneNumber').style.display = "block";
            $('showPhoneNumber').innerHTML = phone;            
          }
          if($('phone') && phone != '')
            $('phone').style.display = "none";   
          if($('phoneimage')) 
            $('phoneimage').style.display = "none";             

        } 
        else if(blurid == 'email') {          
          if($('showEmailAddress')) {    
            $('showEmailAddress').style.display = "block";
            $('showEmailAddress').innerHTML = '<a href="mailto:'+$('email').value+'">'+'<?php echo $this->string()->escapeJavascript($this->translate("Email Me")) ?>'+'</a>';
          }
          if($('email') && email != '')
            $('email').style.display = "none";
          if($('showerrormessage'))
            $('showerrormessage').style.display = "none";
          if($('emailimage'))
            $('emailimage').style.display = "none";
        } else if(blurid == 'website') {
          if($('showWebsite')) { 
            $('showWebsite').style.display = "block";
            var str = website;
            var splitFromHttp = str.split("http://"); 
            var splitFromHttps = str.split("https://");  
            if(typeof splitFromHttp != 'undefined' && splitFromHttp[0] == '') {
              $('showWebsite').innerHTML = '<a href="http://'+splitFromHttp[1]+'" target="_blank" title="'+website+'">'+'<?php echo $this->string()->escapeJavascript($this->translate("Visit Website")) ?>'+'</a>';
            } 
            else if(typeof splitFromHttps != 'undefined' && splitFromHttps[0] == ''){
              $('showWebsite').innerHTML = '<a href="https://'+splitFromHttps[1]+'" target="_blank" title="'+website+'">'+'<?php echo $this->string()->escapeJavascript($this->translate("Visit Website")) ?>'+'</a>';
            }
            else {
              $('showWebsite').innerHTML = '<a href="http://'+str+'" target="_blank" title="'+website+'">'+'<?php echo $this->string()->escapeJavascript($this->translate("Visit Website")) ?>'+'</a>';
            }            
          }
          if($('website') && website != '')
            $('website').style.display = "none";   
          if($('websiteimage'))
            $('websiteimage').style.display = "none";
        }
      }
    }));
  }
</script>  

<style type="text/css">

/* contact details */
.layout_sitereview_contactdetails_sitereview {
	clear: both;
	margin-bottom:10px;
	overflow: hidden;
	padding: 5px;
}
.sitereview_contect_field {
	float:left;
	margin-right:15px;
	line-height:22px;
}
[dir="rtl"] .sitereview_contect_field {
	float:right;
	margin-left:15px;
	margin-right:0;
}
.sitereview_contect_field > img {
	float:left;
	margin-right:5px;
	margin-top:4px;
}
[dir="rtl"] .sitereview_contect_field > img{
	float:right;
	margin-right:0;
	margin-left:5px;
}
.layout_left .sitereview_contect_field, .layout_right .sitereview_contect_field {
	clear:both;
	margin-right:0px;
}
.sitereview_contect_det {
	float:left;
}
[dir="rtl"] .sitereview_contect_det{
	float:right;
}
.layout_right .sitereview_contect_det {
	max-width:130px;
	overflow:hidden;
}
.sitereview_contect_det > div {
	font-size:11px;
	font-weight:bold;
}
.sitereview_contect_field input {
	margin-top:3px;
	padding:2px;
	font-size:11px;
	width:120px;
}
.sitereview_contect_field .edit_icon {
	margin-top:4px;
}
.sitereview_contect_error {
	float:left;
	color:#FF0000;
	padding:0 5px;
	font-size:11px;
}
[dir="rtl"] .sitereview_contect_error{
	float:right;
}

.sitereview_contect_field .edit_icon {
	float:right;
	margin-left:5px;
	width:18px;
}
[dir="rtl"] .sitereview_contect_field .edit_icon{
	float:left;
	margin-left:0px;
	margin-right:5px;
}
.sitereview_contect_field .edit_icon a {
	height:18px;
	width:18px;
	display:block;
	background:url(../images/write_edit_icon.png);
}
.sitereview_contect_field .edit_icon a:hover {
	background:url(../images/write_edit_icon.png) 0 18px;
}
</style>