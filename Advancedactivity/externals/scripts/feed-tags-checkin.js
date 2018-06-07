/* $Id: feed-tags.js 2012-26-01 00:00:00Z SocialEngineAddOns Copyright 2011-2012 BigStep Technologies
Pvt. Ltd. $
 */

// Populate data
var maxRecipients =  20;
var Who_Are_You_Text=en4.core.language.translate('Who are you with?');
var addUserArray=new Array();
var to = {
  id : false,
  type : false,
  guid : false,
  title : false
};
 
var setContentTagUserAAF=function(){
  var toValues = $('toValues').value;
  if ($('toValues').value ==""){
    $('friendas_tag_body_aaf_content').innerHTML='';
  }else{
      
    var toValueArray = toValues.split(",");
    if(toValueArray.length >0){
      var content ='&nbsp;'+en4.core.language.translate('with')+' '; 
      var id=toValueArray[0];
      var newString= '<a href="'+addUserArray[id]['url']+'">'+addUserArray[id]['label']+'</a>'; 
      content =  content+newString;
        
      if(toValueArray.length==2){                
        content =  content+'&nbsp;'+en4.core.language.translate('and')+'&nbsp;' 
        id=toValueArray[1]; 
        newString= '<a href="'+addUserArray[id]['url']+'">'+addUserArray[id]['label']+'</a>'; 
        content =  content+newString;
          
      }else if(toValueArray.length >2){
        content =  content+'&nbsp;'+en4.core.language.translate('and')+'&nbsp;' 
        newString= '<a href="javascript:void(0)">'+(parseInt(toValueArray.length) -
          1)+'&nbsp;'+en4.core.language.translate('others')+'</a>'; 
        content =  content+newString;
      }
      $('friendas_tag_body_aaf_content').innerHTML=content;
       var content_composer =$("friendas_tag_body_aaf_content").getParent();
      content_composer.getFirst('span').innerHTML=' &mdash; '
      content_composer.getLast('span').innerHTML=".";
    }else{
      $('friendas_tag_body_aaf_content').innerHTML="";
      var content_composer =$("friendas_tag_body_aaf").getParent();
      var removeContent=true;     
      content_composer.getElements('span').each(function(el){
        if(el.get('class') != 'aaf_mdash' && el.get('class') != 'aaf_dot'  && el.innerHTML !=''){
          removeContent=false;          
        }
      });
      if(toValueArray.length <1){
        content_composer.getLast('span').empty();
        content_composer.getFirst('span').empty();
      }
    }
  }
  $("friendas_tag_body_aaf").focus();
}  
  
var removeFromToValue=function (id) {
  // code to change the values in the hidden field to have updated values
  // when recipients are removed.
  var toValues = $('toValues').value;
  var toValueArray = toValues.split(",");
  // var toValueIndex = "";

  var checkMulti = id.search(/,/);

  // check if we are removing multiple recipients
  if (checkMulti!=-1){
    var recipientsArray = id.split(",");
    for (var i = 0; i < recipientsArray.length; i++){
      removeToTagValueAAF(recipientsArray[i], toValueArray);
    }
  }
  else{
    removeToTagValueAAF(id, toValueArray);
  }

  // hide the wrapper for usernames if it is empty
  if ($('toValues').value==""){
    $('toValues-wrapper').setStyle('height', '0');
  }

  $('friendas_tag_body_aaf').disabled = false;
}

var removeToTagValueAAF=function(id, toValueArray){
  for (var i = 0; i < toValueArray.length; i++){
    if (toValueArray[i]==id) toValueIndex =i;
  }
   
  toValueArray.splice(toValueIndex, 1);
  $('toValues').value = toValueArray.join();
  
  //  addUserArray.splice(id,1);
  setTimeout("setContentTagUserAAF()",100);
}

var feedTagBindAAF= function(){
  var el=$('adv_post_container_tagging');
  if(el && el.style.display == 'block') {
    el.style.display = 'none';
  }
  $('toValues-element').getElements('.tag').each(function(elemnt){
    elemnt.destroy();
  });
  if($('toValues'))
    $('toValues').value='';
  if($('friendas_tag_body_aaf_content'))
    $('friendas_tag_body_aaf_content').innerHTML="";
  new Autocompleter.Request.JSON('friendas_tag_body_aaf', en4.core.baseUrl + 'advancedactivity/friends/suggest', {
    'minLength': 1,
    'delay' : 250,
    'selectMode': 'pick',
    'autocompleteType': 'message',
    'multiple': false,
		'className': 'tag-autosuggest seaocore-autosuggest',
    'customChoices' : true,  
    'filterSubset' : true,
    'tokenFormat' : 'object',
    'tokenValueKey' : 'label',
    'ignoreKeys' : true,
    'postData' : {          
      'subject' : en4.core.subject.guid
    },
    'injectChoice': function(token){
      
      var choice = new Element('li', {
        'class': 'autocompleter-choices',
        'html': token.photo,
        'id':token.label
      });
      new Element('div', {
        'html': this.markQueryValue(token.label),
        'class': 'autocompleter-choice'
      }).inject(choice);
      new Element('input', {
        'type' : 'hidden',
        'value' : JSON.encode(token)
      }).inject(choice);
      this.addChoiceEvents(choice).inject(this.choices);
      choice.store('autocompleteChoice', token);

    },
    onPush : function(){
      if( $('toValues').value.split(',').length >= maxRecipients ){
        $('friendas_tag_body_aaf').disabled = true;
      }
    },
    onChoiceSelect : function(choice) {     
      var data = JSON.decode(choice.getElement('input').value);
      addUserArray[data.id]=new Array();
      addUserArray[data.id]['label']=data.label;
      addUserArray[data.id]['url']=data.url;
      setTimeout("setContentTagUserAAF()",100);
    },
    onCommand : function(e) {
      // This code is copy to Autocompleter JS amd hack minor for check that stop the key event
      if (e && e.key && !e.shift) {              
        switch (e.key) {
          case 'enter':
            e.stop();
            if( !this.selected ) {
              if( !this.options.customChoices ) {
                // @todo support multiple
                this.element.value = '';
              }
              return true;
            }
            if (this.selected && this.visible) {               
              this.choiceSelect(this.selected);
              return !!(this.options.autoSubmit);
            }
            break;
          case 'up': case 'down':
            var value = this.element.value;
            if (!this.prefetch() && this.queryValue !== null) {               
              var up = (e.key == 'up');                
              if (this.selected) this.selected.removeClass('autocompleter-selected');                
              if(!(this.selected)[
                ((up) ? 'getPrevious' : 'getNext')
                ](this.options.choicesMatch)){
                this.selected=null;
              }
               
              this.choiceOver(
                (this.selected || this.choices)[
                (this.selected) ? ((up) ? 'getPrevious' : 'getNext') : ((up) ? 'getLast' : 'getFirst')
                ](this.options.choicesMatch), true);
              this.element.value = value;
            }
            return false;
          case 'esc':
            this.hideChoices(true);               
            if( !this.options.customChoices ) this.element.value = '';
            //if (this.options.autocompleteType=='message') this.element.value="";               
            break;
          case 'tab':
            if (this.selected && this.visible) {
              this.choiceSelect(this.selected);
              return !!(this.options.autoSubmit);
            } else {
              this.hideChoices(true);
              if( !this.options.customChoices ) this.element.value = '';
              //if (this.options.autocompleteType=='message') this.element.value="";
              break;
            }                   
        }
      }
          
    }
  });
      
  
  new ComposerCheckin.OverText($('friendas_tag_body_aaf'), {
    'textOverride' : Who_Are_You_Text,
    'element' : 'label',
    'isPlainText' : true,
    'positionOptions' : {
      position: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
      edge: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
      offset: {
        x: ( en4.orientation == 'rtl' ? -4 : 4 ),
        y: 2
      }
    }
  });
}
var toogleTagWith=function(){
  var el=$('adv_post_container_tagging');
  if(el.style.display == 'block') {
    el.style.display = 'none';
    if($('compose-checkin-container-checkin')){
      $('compose-checkin-container-checkin').getFirst('label').focus();
    }
  } else {
    el.style.display = 'block';
    if(composeInstanceCheckin)
      composeInstanceCheckin.focus();
    if($('compose-checkin-container-checkin')){
      $('compose-checkin-container-checkin').getFirst('label').focus();
    }
    ( function(){
      $('friendas_tag_body_aaf').focus();
    }).delay(100);
    $('friendas_tag_body_aaf').value='';
  }
}

en4.core.runonce.add(function() {
  feedTagBindAAF();
});
