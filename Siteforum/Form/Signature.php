<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Signature.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Form_Signature extends Engine_Form {

    public function init() {

        $this->setTitle("Edit Signature")
                ->setDescription("Edit your signature using the editor below, and click on 'Save Signature' to save changes.");

        $this->addElement('TinyMce', 'signature', array(
            'label' => '',
            'allowEmpty' => false,
            'attribs' => array('rows' => 180, 'cols' => 350, 'style' => 'width:740px; max-width:740px;height:858px;'),
            'editorOptions' => Engine_Api::_()->seaocore()->tinymceEditorOptions(),
            'filters' => array(
                'StripTags',
                new Engine_Filter_Censor()),
        ));

        $this->addElement('Button', 'save', array(
            'label' => 'Save Signature',
            'type' => 'submit',
        ));
    }

}
