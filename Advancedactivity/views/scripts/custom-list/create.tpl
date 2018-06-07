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
<div class="seaocore_popup">
	<div class="seaocore_popup_top">		
		<div class="seaocore_popup_des"><b>
            <?php if($this->type=='category'): ?>
            <?php echo $this->translate("Create a new list for viewing post updates by choosing categories below.") ?>
            <?php else: ?>
          <?php echo $this->translate("Create a new list for viewing updates by choosing content items and friends below.") ?>
          <?php endif; ?></b></div>
	</div>
	
	<div class="seaocore_popup_options">
		<input type="hidden"  name="page" id='page' value="1"/>  
		<div id="list_title-element" class="seaocore_popup_options_left">      
			<input type='text' class='text suggested' name='title' id='list_title' onkeypress="javascript:if(document.getElementById('validation_title')){ document.getElementById('list_title-element').removeChild(document.getElementById('validation_title'));}" size='50' maxlength='100' alt='<?php echo $this->translate('Enter List Title...') ?>' />
		</div>
	  <div class="seaocore_popup_options_right">
	    <input type='text' class='seaocore_popup_searchbox suggested' name='search' id='field_search' size='20' maxlength='100' alt='<?php echo $this->translate('Search') ?>' onkeyup="getContentItem()" />
	  </div> 
		<div class="seaocore_popup_options_middle">
        <?php if($this->type=='category'): ?>
          <input type="hidden" name="resource_type" id="resource_type" value="advancedactivity_category" />
        <?php else: ?>
          <b><?php echo $this->translate("Choose :") ?></b>
	    <select name="resource_type" id="resource_type" onchange="getContentItem();">
	      <?php foreach ($this->customTypeLists as $list): ?>
	      <option value="<?php echo $list->resource_type ?>" >  <?php echo $this->translate($list->resource_title); ?></option>
	      <?php endforeach; ?>
	    </select> 
        <?php endif; ?>
	  	<b>&nbsp;<?php echo sprintf($this->translate("Selected (%s)"),'<span id="selected_count">0</span>')
?></b>
	  </div>   
	</div>
	
	<div class="seaocore_popup_content">
		<div class="seaocore_popup_content_inner">
			<div id="resource_loading" class="seaocore_item_list_popup_loader">
				<img src='<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/loading.gif' alt="Loading" />
			</div>
 			<div id="resource_items_content"></div>
 		</div>
 	</div>		
 
	<div class="popup_btm">
		<div id="check_error"></div>
		<form method="post" action="" id="form_custom_list">
			<input type="hidden"  name="selected_resources" id='selected_resources' />
			<input type="hidden"  name="title" id='title' />
			<div class="aaf_feed_popup_bottom">
				<button type='button' onClick='submitListForm()'><?php if($this->type=='category'): ?><?php echo sprintf($this->translate('Create list with %s categories'),'<span id="item_count">0</span>'); ?> <?php else: ?><?php echo sprintf($this->translate('Create list with %s items'),'<span id="item_count">0</span>'); ?><?php endif; ?> </button> 
				<button href="javascript:void(0);" onclick="javascript:parent.Smoothbox.close()"><?php echo $this->translate("Cancel"); ?></button>    
				</div>
		</form>
	</div>
</div>

<script type="text/javascript">
  var list=new Array();
  var pagenationContent=null;
  var resource_type_temp=null;
  var getContentItem =function(){ 
    if(resource_type_temp !=document.getElementById('resource_type').value){
      document.getElementById('field_search').value='';
      document.getElementById('page').value=1;
    }
    resource_type_temp =document.getElementById('resource_type').value;
    var url = '<?php echo $this->url(array('module' => 'advancedactivity', 'controller' => 'custom-list', 'action' => 'get-content-items'), 'default', true) ?>';

    if(document.getElementById('page').value==1){      
    document.getElementById('resource_items_content').innerHTML="";
    document.getElementById('resource_loading').style.display = '';
    }
    var request = new Request.HTML({
      url : url,
      data : {
        format : 'html',
        'resource_type' : document.getElementById('resource_type').value,
        'search':document.getElementById('field_search').value,
        'page':document.getElementById('page').value
      },
      evalScripts : true,
      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
        document.getElementById('resource_loading').style.display = 'none';       
        if(document.getElementById('page').value==1){       
         document.getElementById('resource_items_content').innerHTML="";       
        }else{
          if(document.getElementById('view_more_sea'))
         document.getElementById('view_more_sea').destroy();       
        }
         Elements.from(responseHTML).inject(document.getElementById('resource_items_content'));        
        setSelecetedItems();
        // setTimeout("setSelecetedItems()",100);
      }
    });
    request.send();

  }
  en4.core.runonce.add(function() {
    if(document.getElementById('list_title')){
      new OverText(document.getElementById('list_title'), {
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
getContentItem();
});

function getNextPage(){
  document.getElementById('page').value=parseInt(document.getElementById('page').value)+1;
  getContentItem();
  if(document.getElementById('view_more_sea')){
      document.getElementById('view_more_link').style.display ='none';
      document.getElementById('view_more_loding').style.display ='';  
    }
}
function setContentInList(element,resource_type, resource_id){
  var index=resource_type+"-"+resource_id; 
  var checkelement=document.getElementById(index);
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
  document.getElementById("selected_count").innerHTML= document.getElementById("item_count").innerHTML=list.length;
  document.getElementById("selected_resources").value=list;
}

function setSelecetedItems(){ 
  for(var i=0; i<list.length;i++ )
  { 
    if(document.getElementById(list[i])){
      document.getElementById(list[i]).value=1;
     var element= document.getElementById('contener_'+list[i]);
      element.addClass('selected');
    }

  }      

}
function submitListForm(){
  document.getElementById("title").value=document.getElementById("list_title").value;
   
  if (document.getElementById("title").value=="")
    {
      if(!document.getElementById('validation_title')){
        var div_campaign_name = document.getElementById("list_title-element");
        var myElement = new Element("p");
        myElement.innerHTML = '<?php echo $this->string()->escapeJavascript($this->translate("Please enter a List Title.")) ?>';
        myElement.addClass("aaf_feed_error");
        myElement.id = "validation_title";
        div_campaign_name.appendChild(myElement);
      }
      validationFlage=1;
    }else{
    document.getElementById("form_custom_list").submit();
    }
}
</script>