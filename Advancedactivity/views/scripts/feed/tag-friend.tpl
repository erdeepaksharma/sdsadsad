<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: tag-friend.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>
<div class="seaocore_popup">
	<div class="seaocore_popup_top">
		<div class="seaocore_popup_title"><?php echo $this->translate("Tag Friends") ?></div>	
	</div>
	
	<div class="seaocore_popup_options">	
		<div class="seaocore_popup_options_middle fleft">	  	
	    <select name="filter_type" id="filter_type" onchange="showContent()">	    
	      <option value="all" >  <?php echo $this->translate("Search by Name"); ?></option>
        <option value="selected" >  <?php echo $this->translate("Selected"); ?></option>	     
	    </select>   
	  	<b>&nbsp;<?php echo sprintf($this->translate("Selected (%s)"),'<span id="selected_count">0</span>')
?></b>
	  </div>
	  <div class="seaocore_popup_options_right" id="options_right">
      <input type='text' class='seaocore_popup_searchbox suggested' name='search' id='field_search' size='2' maxlength='100' style="width:400px;" alt='<?php echo $this->translate('Search all friends') ?>' onkeyup="searchFriends()" />
	  </div> 
	</div>
	
	<div class="seaocore_popup_content">
		<div class="seaocore_popup_content_inner">			
 			<div id="resource_user_content">
        <?php foreach ($this->members as $item): ?>
          <div class="seaocore_popup_items">
            <a class="aaf_tag_user_content" href="javascript:void(0);" id="contener_<?php echo $item->getIdentity() ?>"  onclick="setContentInList(this,'<?php echo $item->getIdentity() ?>')">      
              <span> <?php echo $this->itemPhoto($item, 'thumb.icon', '', array('align' => 'left')); ?>
                <span></span>
              </span>    
              <p> <?php echo $item->getTitle() ?></p>
              <input type="hidden" id="<?php echo $item->getType() ?>-<?php echo $item->getIdentity() ?>" value="0" />
            </a>
          </div>
        <?php endforeach; ?>

        <?php if (empty($this->count)): ?>
          <div class="tip" style="margin:10px">
            <span>
              <?php echo $this->translate('No friends were found.'); ?>
            </span>
          </div>
        <?php endif; ?>
      </div>
 		</div>
 	</div>		
 
	<div class="popup_btm">
		<div id="check_error"></div>
		<form method="post" action="" id="form_custom_list">
			<input type="hidden"  name="selected_resources" id='selected_resources' />		
			<div class="aaf_feed_popup_bottom">
				<button type='submit'><?php echo $this->translate('Submit') ?></button> 
				<button href="javascript:void(0);" onclick="javascript:parent.Smoothbox.close()"><?php echo $this->translate("Cancel"); ?></button>    
				</div>
		</form>
	</div>
</div>
<script type="text/javascript">
var list=new Array();
window.addEvent('domready', function() {
<?php foreach ($this->tagMembers as $value): ?>
    list.push('<?php echo $value->tag_id ?>');
<?php endforeach; ?>
   setSelecetedItems();


   if(document.getElementById('field_search')){
     new OverText(document.getElementById('field_search'), {
       poll: true,
       pollInterval: 500,
       positionOptions: {
         position: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
         edge: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
         offset: {
           x: ( en4.orientation == 'rtl' ? -4 : 4 ),
           y: 2
         }
       }
     });
   }
 });
 function setContentInList(element,resource_id){
   var index=resource_id; 
   var checkelement=document.getElementById("user-"+index);
   if(checkelement.value==0){
     // pushinto list  
     list.push(index);
     element.addClass('selected');
     checkelement.value=1;
   }else{
     // pop from list
     for(var i=0; i<list.length;i++ )
     {
       if(list[i]==index) 
         list.splice(i,1); 
     }
     checkelement.value=0;
     element.removeClass('selected');
   } 
   document.getElementById("selected_count").innerHTML=list.length;
   document.getElementById("selected_resources").value=list;
 }

 function setSelecetedItems(){ 
   for(var i=0; i<list.length;i++ )
   { 
     if(document.getElementById("user-"+list[i])){
       document.getElementById("user-"+list[i]).value=1;
       var element= document.getElementById('contener_'+list[i]);
       element.addClass('selected');
     }
   }
 document.getElementById("selected_count").innerHTML=list.length;
 document.getElementById("selected_resources").value=list;   
 }
 function showContent(){
   var type=document.getElementById("filter_type").value;
   document.getElementById('field_search').value="";
   if(type=='all'){
     document.getElementById("options_right").style.display='inline-block';
     $$(".aaf_tag_user_content").each(function(el) {
       el.style.display='inline-block';
     });
   }else{
      document.getElementById("options_right").style.display='none';
      $$(".aaf_tag_user_content").each(function(el) {
       el.style.display='none';
     });
     
     list.each(function(el_value){
       var el=  document.getElementById('contener_'+el_value);
       if(el)
        el.style.display='inline-block';
     });
   }
 }
 
 function searchFriends(){
   var search_term=document.getElementById('field_search').value;
   if(search_term==''){
     showContent();
   }else{
     $$(".aaf_tag_user_content").each(function(el) {
       el.style.display='none';
     });
     <?php foreach ($this->members as $item): ?>
          var str="<?php echo $item->getTitle();?>"
          str=str.toLowerCase();
          search_term=search_term.toLowerCase();
           var url_check = str.indexOf( search_term ); 
          if ( url_check != -1 ) {
            var el=  document.getElementById('contener_<?php echo $item->getIdentity() ?>');
             if(el)
           el.style.display='inline-block';
          
         } 
     <?php endforeach;?>      
   }
     
 }
</script>
