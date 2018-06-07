<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminAdvertismentController.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_AdminAdvertismentController extends Core_Controller_Action_Admin {

    function init() {
        $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_advertising');
    }

    public function indexAction() {
        //Show error message if community Ads is not available
        if (!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('communityad')) {
            $errorMessage = 'Wanna display Ads in activity feeds, but confused how to do so? </br>Let\'s do it easy for you, just purchase our Advertisements / Community Ads Plugin and with simple configurations display attractive ads / ad campaigns (from SE-Core) in your activity feeds within few minutes. See this <a target="_blank" class="mleft5" title="View Screenshot" href="application/modules/Advancedactivity/externals/images/admin/ads.png" target="_blank"><img src="application/modules/Seaocore/externals/images/admin/eye.png" /></a>  to know how your ads will look in activity feed after this integration.';

            $this->view->errorMessage = @str_replace("error::", "", $errorMessage);
            return;
        }
        $this->view->form = $form = new Advancedactivity_Form_Admin_Advertisment_General();

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        // Process
        $values = $form->getValues();

        if (isset($values['advancedactivity_community_adv'])) {
            Engine_Api::_()->getApi('settings', 'core')->setSetting('advancedactivity.community.adv', $values['advancedactivity_community_adv']);
        }

        if (isset($values['advancedactivity_campaign_adv'])) {
            Engine_Api::_()->getApi('settings', 'core')->setSetting('advancedactivity.campaign.adv', $values['advancedactivity_campaign_adv']);
        }

        if (isset($values['advancedactivity_adv_count'])) {
            Engine_Api::_()->getApi('settings', 'core')->setSetting('advancedactivity.adv.count', $values['advancedactivity_adv_count']);
        }

        //Save selected community ads & remove those which are not selected table.
        if (array_key_exists('community_adv_types', $values)) {
            Engine_Api::_()->getApi('settings', 'core')->setSetting('advancedactivity.communityads', is_array($values['community_adv_types']) ? json_encode($values['community_adv_types']) : 0);
        }

        //Save selected core ads & remove those which are not selected from database table.
        if (array_key_exists('core_adv_types', $values)) {
            Engine_Api::_()->getApi('settings', 'core')->setSetting('advancedactivity.coreads', is_array($values['core_adv_types']) ? json_encode($values['core_adv_types']) : 0 );
        }
        $this->_redirect('/admin/advancedactivity/advertisment');
    }

}
