<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Edit.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Form_Admin_Siteforum_Edit extends Siteforum_Form_Admin_Siteforum_Create {

    public function init() {

        parent::init();

        $this->setTitle('Edit Forum');

        $this->execute->setLabel('Save Changes');
    }

}
