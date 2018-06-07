<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: FeedBanner.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/

class Advancedactivity_Form_Admin_FeedBanner extends Engine_Form {

  public function init() {
    $this
            ->setTitle('Add New Banner')
            ->setDescription('You can add one or many banner at a time.');
    $this->addElement('Radio', 'gradient_enabled', array(
          'label' => 'Enable Gradient',
          'description' => "Do you want to enable gradient feature for this banner?",
          'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
          ),
          'value' => 0,
          'onchange' => 'toggleBannerGradient(this.value)'
    ));
    $this->addElement('Text','gradient',array(
        'label' => 'Banner\'s Gradient',
        'description' => 'Enter the gradient for the banner.',
        'onblur' => 'this.value ? $("preview_banner").setStyle("background",this.value) : null'
    ));
    $this->addElement('File', 'banner', array(
            'label' => 'Background Image',
            'description' => "Select image to be added as background.",
            'multiple' => 'multiple',
        ));
    $this->addElement('Text','color',array(
    	'label' => 'Select Text Color',
    	'description' => 'Select the color for the banner.',
        'decorators' => array(array('ViewScript', array(
                        'viewScript' => '_namebgcoloreven.tpl',
                        'class' => 'form element',
                        'name' => 'color',
                        'value' => '#ffffff',
                        'label' => 'Select Banner\'s Text Color',
                        'description' => 'Select the color for the text displaying on background.',
                        'order' =>98
                    ))),
    	));
    $this->addElement('Text','background_color',array(
    	'label' => 'Select Background Color',
    	'description' => 'Select the background color. [Note: This background color will be visible only if the background image is not displaying.]',
        'decorators' => array(array('ViewScript', array(
                        'viewScript' => '_namebgcoloreven.tpl',
                        'class' => 'form element',
                        'name' => 'background_color',
                        'value' => '#09e89e',
                        'label' =>'Select Banner\'s Background Color',
                        'description' => 'Select the background color. [Note: This background color will be visible only if the background image is not displaying.]',
                        'order' =>99
                    ))),
    	));
    
    //Start End date element
    $currentYear = date('Y');
    $this->addElement('Date', 'startdate', array(
        'label' => "Start Date",
        'allowEmpty' => false,
        'required' => true,
        'value' => array('year' => date('Y'), 'month' => date('m'), 'day' => date('d'))
    ));
    $this->startdate->setYearMax($currentYear + 1);
    $this->startdate->setYearMin($currentYear - 1);
    
    $this->addElement('Date', 'enddate', array(
        'label' => "End Date",
        'allowEmpty' => true,
        'value' => array('year' => date('Y') + 1, 'month' => date('m'), 'day' => date('d'))
    ));
    $this->enddate->setYearMax($currentYear + 50);
    $this->enddate->setYearMin($currentYear - 1);
    $this->addElement('Radio', 'highlighted', array(
            'label' => 'Highlighted',
            'description' => "Do you want to mark it as highlighted?",
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'value' => 0,
    ));
    $this->addElement('Radio', 'enabled', array(
            'label' => 'Enable',
            'description' => "Do you want to enable this background?",
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'value' => 1,
        ));
    // Add submit button
    $this->addElement('Button', 'submit', array(
        'label' => 'Submit',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));
    $this->addElement('Cancel', 'preview', array(
      'prependText' => ' or ',
      'label' => 'Reset Default',
      'onclick' => 'document.location.reload()',
      'decorators' => array(
        'ViewHelper'
      ),
    ));
    $this->addDisplayGroup(array('submit','preview'),'buttons');
  }

}