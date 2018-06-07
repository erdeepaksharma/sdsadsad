<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: edit.tpl 10110 2013-10-31 02:04:11Z andres $
 * @author     Jung
 */
?>
<?php $this->headLink()
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/styles/style_advancedactivity.css');
?>
 <?php
//GET API KEY
$apiKey = Engine_Api::_()->seaocore()->getGoogleMapApiKey();
$this->headScript()->appendFile("https://maps.googleapis.com/maps/api/js?libraries=places&key=$apiKey");
$this->headLink()
  ->appendStylesheet($this->layout()->staticBaseUrl . 'externals/fancyupload/fancyupload.css');

?>
<style type="text/css">
  #demo-list{
    max-height: none;
    width: auto;
    text-align: center;
  }
  #demo-list li {
    width: 160px;
    height: 170px;
    margin: 10px;
    float:left;
    border: 1px solid #ccc;
  }
  #demo-list li img{
      margin-top:48px;
  }
  #demo-list li.file, #demo-list li.file-success {
    background-image: none;
    padding: 10px;
  }
  #demo-list li.file-success img{
    width: 150px;
    height: 150px;
    margin-top:0;
  }
</style>
 <script type="text/javascript">
  en4.core.runonce.add(function () {
    new google.maps.places.Autocomplete(document.getElementById('place'));
  });
  </script>
  
  <script type="text/javascript">
  
  en4.core.runonce.add(function() { 
    <?php foreach($this->images as $key => $image) : ?>
  var img = document.createElement('img');
      img.src = '<?php echo $image ?>'; 
  var a = document.createElement('a');
      a.href='javascript:void(0)';
      a.innerHTML='X';
      a.title='Click to remove this entry';
      a.setAttribute('onclick','removePhoto(<?php echo $key ?>)');
      a.addClass('file-remove');
    var li = document.createElement('li');
    li.setAttribute('id','li_image_<?php echo $key ?>')
    li.addClass('file file-success');
    li.appendChild(a);  
    li.appendChild(img);
    document.getElementById('demo-list').appendChild(li);
    <?php endforeach; ?>
    document.getElementById('demo-list').setStyle('display','block');  
      
    });
  function removePhoto(photo_id){
    $('li_image_'+photo_id).destroy();
    var temp = $('photo_id').value; 
    $('photo_id').value = temp.replace(''+photo_id+' ',''); 
    if(!$('photo_id').value){
      document.getElementById('demo-list').setStyle('display','none');  
    }
  }
</script>

<?php
  /* Include the common user-end field switching javascript */
  echo $this->partial('_jsSwitch.tpl', 'fields', array(
    //'topLevelId' => (int) @$this->topLevelId,
    //'topLevelValue' => (int) @$this->topLevelValue
  ))
?>

<div class="headline">
  <h2>
    <?php echo $this->translate('BuySell Item');?>
  </h2>
  
</div>

<form id="form-upload" action="<?php echo $this->escape($this->form->getAction()) ?>" method="<?php echo $this->escape($this->form->getMethod()) ?>" class="global_form classifieds_browse_filters">
    <div class="aaf_edit_buysell_product">
    <div>
      <h3>
        <?php echo $this->translate($this->form->getTitle()) ?>
      </h3>
        <p> <?php echo $this->translate($this->form->getDescription()) ?> </p>
    
      <div class="form-elements">
        <?php echo $this->form->getDecorator('FormErrors')->setElement($this->form)->render("");?>
        <?php echo $this->form->title; ?>
        <?php echo $this->form->price; ?>
        <?php echo $this->form->currency; ?>
        <?php echo $this->form->place; ?>
        <?php echo $this->form->description; ?>
        <?php echo $this->form->getSubForm('fields'); ?>
       
        <div id="uploaded-images">
        <div class="form-label">&nbsp;</div>
          <ul id="demo-list"></ul>
        </div>
        <?php echo $this->form->photo_id; ?>
        <?php echo $this->form->file;  ?>
      </div>
        <?php echo $this->form->execute->render(); ?>
      
    </div>
  </div>
</form>

 