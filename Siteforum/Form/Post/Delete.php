<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Delete.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Form_Post_Delete extends Engine_Form {

    public function init() {
        $this->setTitle('Delete Post')
                ->setDescription('Are you sure that you want to delete this post? It will not be recoverable after being deleted.');

        $this->addElement('Hash', 'token');

        $this->addElement('Button', 'submit', array(
            'label' => 'Delete Post',
            'type' => 'submit',
            'decorators' => array(
                'ViewHelper',
            ),
        ));

        $this->addElement('Cancel', 'cancel', array(
            'label' => 'Cancel',
            'link' => true,
            'prependText' => ' or ',
            'decorators' => array(
                'ViewHelper',
            ),
            'onclick' => 'parent.Smoothbox.close();'
        ));

        $this->addDisplayGroup(array(
            'submit',
            'cancel'
                ), 'buttons', array(
                //'decorators' => array(
                //    'FormElements'
                // )
        ));

        $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))->setMethod('POST');
    }

}
