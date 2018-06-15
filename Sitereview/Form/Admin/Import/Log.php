<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Log.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Admin_Import_Log extends Engine_Form {

  public function init() {

    $this->setMethod('GET')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array('file' => null)))
            ->addAttribs(array(
                'id' => 'filter_form',
                'class' => 'global_form_box',
            ));

    $this->clearDecorators()
            ->addDecorator('FormElements')
            ->addDecorator('Form')
            ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'search'))
            ->addDecorator('HtmlTag2', array('tag' => 'div', 'class' => 'clear'));

    $this->addElement('Select', 'file', array(
        'multiOptions' => array(
            '' => '',
        ),
        'decorators' => array(
            'ViewHelper',
            array('HtmlTag', array('tag' => 'div')),
        ),
    ));

    $this->addElement('Select', 'length', array(
        'multiOptions' => array(
            '10' => 'Show 10 Lines',
            '50' => 'Show 50 Lines',
            '100' => 'Show 100 Lines',
            '500' => 'Show 500 Lines',
            '1000' => 'Show 1000 Lines',
            '5000' => 'Show 5000 Lines',
            '10000' => 'Show 10000 Lines',
            '50000' => 'Show 50000 Lines',
        ),
        'decorators' => array(
            'ViewHelper',
            array('HtmlTag', array('tag' => 'div')),
        ),
    ));

    $this->addElement('Button', 'execute', array(
        'type' => 'submit',
        'label' => 'View History',
        'decorators' => array(
            'ViewHelper',
            array('HtmlTag', array('tag' => 'div')),
        ),
    ));

    $this->addElement('Button', 'clear', array(
        'label' => 'Clear History',
        'decorators' => array(
            'ViewHelper',
            array('HtmlTag', array('tag' => 'div')),
        ),
    ));
  }

}