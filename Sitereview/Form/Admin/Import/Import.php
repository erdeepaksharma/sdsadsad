<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Import.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Admin_Import_Import extends Engine_Form {

  public function init() {

    $this->setTitle('Import a File')
            ->setDescription("Add a CSV file to import Listings corresponding to the entries in it, and then click on 'Submit' button.")
            ->setAttrib('enctype', 'multipart/form-data')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    $this->addElement('File', 'filename', array(
        'label' => 'Import File',
        'required' => true,
    ));
    $this->filename->getDecorator('Description')->setOption('placement', 'append');

    $this->addElement('Radio', 'import_seperate', array(
        'label' => 'File Columns Separator',
        'description' => 'Select a separator from below which you are using for the columns of the CSV file.',
        'required' => true,
        'allowEmpty' => false,
        'multiOptions' => array(
            1 => "Pipe ('|')",
            0 => "Comma (',')"
        ),
    ));

    $this->addElement('Button', 'submit', array(
        'label' => 'Submit',
        'type' => 'submit',
        'onclick' => "javascript:showLightbox()",
        'decorators' => array(
            'ViewHelper',
        ),
    ));

    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
        'prependText' => ' or ',
        'onclick' => "javascript:parent.Smoothbox.close()",
        'decorators' => array(
            'ViewHelper',
        ),
    ));

    $this->addDisplayGroup(array(
        'submit',
        'cancel',
            ), 'buttons', array(
        'decorators' => array(
            'FormElements',
            'DivDivDivWrapper'
        ),
    ));
  }

}