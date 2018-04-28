<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: QuickNavigation.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Form_QuickNavigation extends Engine_Form {

    public function init() {

        $this->addElement('Select', 'navigation', array(
            'disableTranslator' => 'true',
            'onchange' => 'this.form.submit();',
                )
        );
    }

}
