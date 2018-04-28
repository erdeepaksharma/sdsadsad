<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Create.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Form_Admin_Siteforum_Create extends Engine_Form {

    public function init() {

        $this->setTitle('Create Forum');

        $order = 1;

        $this->addElement('Text', 'title', array(
            'label' => 'Forum Title',
            'order' => ++$order,
            'NotEmpty' => true,
            'allowEmpty' => false,
            'required' => true,
            'filters' => array(
                new Engine_Filter_Censor(),
                'StripTags',
                new Zend_Filter_StringTrim,
                new Engine_Filter_StringLength(array('max' => '63'))
            ),
        ));

        $this->addElement('Text', 'description', array(
            'label' => 'Forum Description',
            'order' => ++$order,
        ));

        $categories = Engine_Api::_()->getDbtable('categories', 'siteforum')->getCategories();
        $categories_prepared[0] = "";
        foreach ($categories as $category) {
            $categories_prepared[$category->category_id] = $category->title;
        }

        $this->addElement('Select', 'category_id', array(
            'label' => 'Category',
            'allowEmpty' => false,
            'required' => true,
            'multiOptions' => $categories_prepared,
            'onchange' => "subcategories(this.value, '', '');",
            'order' => ++$order,
        ));

        $this->addElement('Select', 'subcategory_id', array(
            'RegisterInArrayValidator' => false,
            'decorators' => array(array('ViewScript', array(
                        'viewScript' => 'application/modules/Siteforum/views/scripts/_formSubcategory.tpl',
                        'class' => 'form element'))
            ),
            'order' => ++$order,
        ));

        $levels = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll();
        $multiOptions = array();
        foreach ($levels as $level) {
            $multiOptions[$level->getIdentity()] = $level->getTitle();
        }
        reset($multiOptions);
        $this->addElement('Multiselect', 'levels', array(
            'label' => 'Member Levels',
            'order' => ++$order,
            'multiOptions' => $multiOptions,
            'value' => array_keys($multiOptions),
            'required' => true,
            'allowEmpty' => false,
        ));

        $this->addElement('Button', 'execute', array(
            'label' => 'Create Forum',
            'type' => 'submit',
            'ignore' => true,
            'decorators' => array('ViewHelper'),
            'order' => ++$order,
        ));

        // Element: cancel
        $this->addElement('Cancel', 'cancel', array(
            'label' => 'cancel',
            'link' => true,
            'prependText' => ' or ',
            'href' => '',
            'onClick' => 'javascript:parent.Smoothbox.close();',
            'decorators' => array(
                'ViewHelper'
            ),
            'order' => ++$order,
        ));

        $this->addDisplayGroup(array(
            'execute',
            'cancel'
                ), 'buttons', array(
            'order' => ++$order,
        ));
    }

}
