<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Share.php 8968 2011-06-02 00:48:35Z john $
 * @author     John
 */ 
class Advancedactivity_Form_EditPost extends Engine_Form
{
    public function init()
    {

        $this
          ->setMethod('POST')
          ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
              'module' => 'advancedactivity', 'controller' => 'feed', 'action' => 'edit'), 'default', true))
        ;

        $this->addElement('Textarea', 'body', array(
          'attribs' => array('rows' => 3),
          'filters' => array(
                   'StripTags',
          )
        ));
        $this->addElement('hidden', 'action_id',array('order' => 9999,));
        $this->addElement('hidden', 'auth_view',array('order' => 9987));
        // Buttons
        $buttons = array();

        $this->addElement('Button', 'submit', array(
          'label' => 'Edit Post',
          'type' => 'submit',
          'ignore' => true,
          'decorators' => array('ViewHelper')
        ));
        $buttons[] = 'submit';

        $this->addElement('Cancel', 'cancel', array(
          'label' => 'cancel',
          'link' => true,
          'prependText' => ' or ',
          'class' => 'feed-edit-cancel',
          'href' => 'javascript:void(0);',
          'decorators' => array(
            'ViewHelper'
          )
        ));
        $buttons[] = 'cancel';


        $this->addDisplayGroup($buttons, 'buttons');
    }

}
