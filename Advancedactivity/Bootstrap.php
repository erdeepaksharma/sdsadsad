<?php

class Advancedactivity_Bootstrap extends Engine_Application_Bootstrap_Abstract {

  public function __construct($application) {
    parent::__construct($application);
    include APPLICATION_PATH . '/application/modules/Advancedactivity/controllers/license/license.php';
    
	//START CODE FOR MOBILE / TABLET PLUGIN
    $session = new Zend_Session_Namespace('siteViewModeSM');
    if (isset($session->siteViewModeSM) && in_array($session->siteViewModeSM, array("mobile", "tablet"))) {
      $settings = Engine_Api::_()->getApi('settings', 'core');
      // [ RESOLVED CONFLICTED WITH ADVANCED MEMBER
      if ($settings->advancedmembers_enabled) {
        Zend_Registry::set('advancedmembers_enabled', $settings->advancedmembers_enabled);
        $settings->advancedmembers_enabled = 0;
      }
      // RESOLVED CONFLICTED WITH ADVANCED MEMBER ]
      
      // [ RESOLVED CONFLICTED WITH PROFILE URL
      if(_ENGINE_R_BASE == '/')
				$subDirectory = '';
			else
				$subDirectory = _ENGINE_R_BASE;

			define('SUBDIRECTORY',$subDirectory);
			
			
			if(substr(str_replace($subDirectory,'',$_SERVER['REQUEST_URI']),0,9) == '/profile/'){
				$_SERVER['REQUEST_URI'] = str_replace('/profile/','/profileSM/',$_SERVER['REQUEST_URI']);
			}
      //  RESOLVED CONFLICTED WITH PROFILE URL ]
    }
    //END CODE FOR MOBILE / TABLET PLUGIN 
  }

  protected function _initOverwriteManifest()
  {
     $manifest = Zend_Registry::get('Engine_Manifest');
     $manifest['core']['composer']['link']['plugin'] = 'Advancedactivity_Plugin_Composer';
     Zend_Registry::set('Engine_Manifest', $manifest);
  }

  protected function _initFrontController() {

    $this->initViewHelperPath();
    $this->initActionHelperPath();
    //Initialize helper
     Zend_Controller_Action_HelperBroker::addHelper(new
Advancedactivity_Controller_Action_Helper_Advancedactivitys());
    $headScript = new Zend_View_Helper_HeadScript();
    $notificationIsEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('aaf.isenable.notification', 1);
    if ($notificationIsEnable) {
      if (Zend_Registry::isRegistered('StaticBaseUrl')) {
        $headScript->appendFile(Zend_Registry::get('StaticBaseUrl')
              . 'application/modules/Advancedactivity/externals/scripts/notification.js');
      } else {
        $headScript->appendFile('application/modules/Advancedactivity/externals/scripts/notification.js');
      }
     
    }
    $front = Zend_Controller_Front::getInstance();
    $front->registerPlugin(new Advancedactivity_Plugin_Core);
  }

}
