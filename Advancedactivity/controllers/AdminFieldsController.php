<?php
 
class Advancedactivity_AdminFieldsController extends Fields_Controller_AdminAbstract
{
  protected $_fieldType = 'advancedactivity_sell';

  protected $_requireProfileType = false;

  public function indexAction()
  {
    // Make navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_fields');

    parent::indexAction();
  }

  public function fieldCreateAction(){
    parent::fieldCreateAction();


    // remove stuff only relavent to profile questions
    $form = $this->view->form;

    if($form){
      $form->setTitle('Add BuySell Field');

      $display = $form->getElement('display');
      $display->setLabel('Show on Buy-Sell feed?');
      $display->setOptions(array('multiOptions' => array(
          1 => 'Show on Buy-Sell feed',
          0 => 'Hide Buy-Sell feed'
        )));

      $search = $form->getElement('search');
      $search->setLabel('Show on the search options?');
      $search->setOptions(array('multiOptions' => array(
          0 => 'Hide on the search options',
          1 => 'Show on the search options'
        )));
    }
  }

  public function fieldEditAction(){
    parent::fieldEditAction();


    // remove stuff only relavent to profile questions
    $form = $this->view->form;

    if($form){
      $form->setTitle('Edit Buy-Sell Question');

      $display = $form->getElement('display');
      $display->setLabel('Show on Buy-Sell page?');
      $display->setOptions(array('multiOptions' => array(
          1 => 'Show on Buy-Sell page',
          0 => 'Hide on Buy-Sell page'
        )));

      $search = $form->getElement('search');
      $search->setLabel('Show on the search options?');
      $search->setOptions(array('multiOptions' => array(
          0 => 'Hide on the search options',
          1 => 'Show on the search options'
        )));
    }
  }
}