<?php
 
class Advancedactivity_Form_BuySell_Edit extends Advancedactivity_Form_BuySell_Create
{
  public $_error = array();
  protected $_item;

  public function getItem()
  {
    return $this->_item;
  }

  public function setItem(Core_Model_Item_Abstract $item)
  {
    $this->_item = $item;
    return $this;
  }
  
  public function init()
  {
    parent::init();

    
    $this->setTitle('Edit BuySell')
         ->setDescription('Edit your Advertising item below, then click \"Save Changes\" to save your item.');
   
    $this->addElement('Hidden', 'photo_id', array(
                      'value'=>''
    ));
    $fancyUpload = new Engine_Form_Element_FancyUpload('file');
    $fancyUpload->clearDecorators()
                ->addDecorator('FormFancyUpload')
                ->addDecorator('viewScript', array(
                  'viewScript' => 'buy-sell/upload.tpl',
                  'placement'  => '',
                  ));
    Engine_Form::addDefaultDecorators($fancyUpload);
    $this->addElement($fancyUpload);
    // Element: execute
    $this->addElement('Button', 'execute', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      
    ));
  }
}