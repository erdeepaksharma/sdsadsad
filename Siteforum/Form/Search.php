<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Search.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Form_Search extends Engine_Form {

    public function init() {
        $this
                ->setAttribs(array(
                    'id' => 'searchBox',
                    'class' => 'siteforum-search-box',
                ))
                ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'search'), 'siteforum_general'));

        parent::init();

        $placeholder = Zend_Registry::get('Zend_Translate')->_('Search Topics...');
        
        $this->addElement('Text', 'search', array(
            'StripTags',
            'placeholder' => $placeholder
        ));

        $this->addElement('Select', 'forum_id', array(
            'multiOptions' => array(
                '0' => 'All Forums',
            ),
        ));

        $this->addElement('Button', 'save', array(
            'label' => 'Search',
            'type' => 'submit',
        ));
    }

}
