<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Language.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Api_Language extends Core_Api_Abstract {

  protected $_languagePath;
  protected $_defaultLanguagePath;
  protected $_defalutFileName = 'sitereview_listingtype_genral.csv';
  protected $_fileNamePre = "sitereview_";
  protected $_hasUnlinkFlag = true;

  public function __construct() {
    $this->_languagePath = APPLICATION_PATH . '/application/languages';
    $this->_defaultLanguagePath = APPLICATION_PATH . '/application/modules/Sitereview/settings/languages';
  }

  public function getDataWithoutKeyPhase() {
    return array('text_overview' => 'Overview', 'text_Where_to_Buy' => 'Where to Buy', 'text_tags' => 'Tags', 'text_listings' => 'Listings', 'text_listing' => 'Listing', 'text_posted' => 'Posted', 'text_post' => 'Post', 'text_stores' => 'Stores', 'text_store' => 'Store');
  }

  public function hasDirectoryPermissions() {
    $flage = false;
    $test = new Engine_Sanity(array(
                'basePath' => APPLICATION_PATH,
                'tests' => array(
                    array(
                        'type' => 'FilePermission',
                        'name' => 'Language Directory Permissions',
                        'path' => 'application/languages',
                        'value' => 7,
                        'recursive' => true,
                        'messages' => array(
                            'insufficientPermissions' => 'Language file for this listing type could not be overwritten. because you do not have write permission chmod -R 777 recursively to the directory "/application/languages/". Please login in over your Cpanel or FTP and give the recursively write permission to this directory and try again.',
                        ),
                    ),
                ),
            ));
    $test->run();
    foreach ($test->getTests() as $stest) {
      $errorLevel = $stest->getMaxErrorLevel();
      if (empty($errorLevel))
        $flage = true;
    }
    return $flage;
  }

  public function setUnlinkFlag($flage=true) {
    $this->_hasUnlinkFlag = $flage;
  }

  public function checkLocal($locale='en') {
    // Check Locale
    $locale = Zend_Locale::findLocale();
    // Make Sure Language Folder Exist
    $languageFolder = is_dir(APPLICATION_PATH . '/application/languages/' . $locale);
    if ($languageFolder === false) {
      $locale = substr($locale, 0, 2);
      $languageFolder = is_dir(APPLICATION_PATH . '/application/languages/' . $locale);
    }
    return $languageFolder;
  }

  public function addLanguageFile($fileName, $locale ='en', $replaceDatas=array(), $replaceDataWithoutKey=array(), $oldFileName=null) {
    if (empty($fileName) || !$this->checkLocal($locale)) {
      return;
    }
    $output = array();
    $dataLocale = array();

    $output = $dataEn = $this->loadTranslationData('en');

//    if ($locale !== 'en') {
//      $dataLocale = $this->loadTranslationData($locale);
//    }
//    $output = array_merge($dataEn, $dataLocale);
    if (empty($output))
      return;

    $output = $this->convertData($output, $replaceDatas, $replaceDataWithoutKey);
    $language_file = $this->_languagePath . '/' . $locale . '/' . $fileName;

    if ($this->_hasUnlinkFlag && file_exists($language_file)) {
      @unlink($language_file);
    }

    if ($oldFileName) {
      $old_language_file = $this->_languagePath . '/' . $locale . '/' . $oldFileName;
      if (file_exists($old_language_file)) {
        @unlink($old_language_file);
      }
    }
    touch($language_file);
    chmod($language_file, 0777);

    $export = new Engine_Translate_Writer_Csv($language_file);
    $export->setTranslations($output);
    $export->write();
  }

  public function addLanguageFiles($fileName, $replaceDatas=array(), $replaceDataWithoutKey=array(), $oldFileName=null) {
    $translate = Zend_Registry::get('Zend_Translate');

    // Prepare language list
    $languageList = $translate->getList();
    if (!empty($languageList)) {
      foreach ($languageList as $key) {
        $this->addLanguageFile($fileName, $key, $replaceDatas, $replaceDataWithoutKey, $oldFileName);
      }
    }
  }

//  public function getMessages($locale) {
//    return $this->_loadTranslationData($locale);
//  }

  protected function loadTranslationData($locale='en', array $options = array()) {
    $file_data = array();
    $options['delimiter'] = ";";
    $options['length'] = 0;
    $options['enclosure'] = '"';
    //$options = $options;
    $filename = $this->_defaultLanguagePath . '/' . $locale . '/' . $this->_defalutFileName;
    $tmp = Engine_Translate_Parser_Csv::parse($filename, 'null', $options);
    if (!empty($tmp['null']) && is_array($tmp['null'])) {
      $file_data = $tmp['null'];
    } else {
      $file_data = array();
    }
    return $file_data;

//    if (file_exists($filename)) {
//      $export = new Engine_Translate_Writer_Csv($filename);
//      return $export->getTranslations();
//    } else {
//      return array();
//    }
//    $file = @fopen($filename, 'rb');
//    if ($file) {
//
//      while (($data = fgetcsv($file, $options['length'], $options['delimiter'], $options['enclosure'])) !== false) {
//        if (substr($data[0], 0, 1) === '#') {
//          $data[1] = '';
//        }
//
//        if (!isset($data[1])) {
//          continue;
//        }
//
//        if (count($data) == 2) {
//          $file_data[$data[0]] = $data[1];
//        } else {
//          $singular = array_shift($data);
//          $file_data[$singular] = $data;
//        }
//      }
//    }
//    return $file_data;
  }

  public function getReplaceData($listType) {
    $replaceDatas = array();


    $Listtype_Title_Plural = $listType->title_plural;
    $Listtype_Title_Plural_KEY = "products";

    $replaceDatas[strtolower($Listtype_Title_Plural_KEY)] = strtolower($Listtype_Title_Plural);
    $replaceDatas[ucfirst($Listtype_Title_Plural_KEY)] = ucfirst($Listtype_Title_Plural);
    $replaceDatas[strtoupper($Listtype_Title_Plural_KEY)] = strtoupper($Listtype_Title_Plural);

    $Listtype_Title_Singular = $listType->title_singular;
    $Listtype_Title_Singular_KEY = "product";

    $replaceDatas[strtolower($Listtype_Title_Singular_KEY)] = strtolower($Listtype_Title_Singular);
    $replaceDatas[ucfirst($Listtype_Title_Singular_KEY)] = ucfirst($Listtype_Title_Singular);
    $replaceDatas[strtoupper($Listtype_Title_Singular_KEY)] = strtoupper($Listtype_Title_Singular);

    $LISTTYPE_VAR = "listtype_" . $listType->listingtype_id;
    $LISTTYPE_KEY = "listtype_1";
    $replaceDatas[strtolower($LISTTYPE_KEY)] = strtolower($LISTTYPE_VAR);
    $replaceDatas[ucfirst($LISTTYPE_KEY)] = ucfirst($LISTTYPE_VAR);
    $replaceDatas[strtoupper($LISTTYPE_KEY)] = strtoupper($LISTTYPE_VAR);

    return $replaceDatas;
  }

  public function getReplaceDataWithoutKey($listType) {
    $replaceWithOutKeyDatas = array();
    $replaceWithOutKeyDatasDefault = $listType->language_phrases;
    if (empty($replaceWithOutKeyDatasDefault))
      return;
    $defaultPhase = $this->getDataWithoutKeyPhase();
    foreach ($replaceWithOutKeyDatasDefault as $arraykey => $data) {
      if (!isset($defaultPhase[$arraykey]))
        continue;

      $key = $defaultPhase[$arraykey];
      $replaceWithOutKeyDatas[strtolower($key)] = strtolower($data);
      $replaceWithOutKeyDatas[ucfirst($key)] = ucfirst($data);
      $replaceWithOutKeyDatas[strtoupper($key)] = strtoupper($data);
      $replaceWithOutKeyDatas[ucwords($key)] = ucwords($data);
    }
    return $replaceWithOutKeyDatas;
  }

  public function setTranslateForListType($listType) {
    $hasDirectoryPermissions = $this->hasDirectoryPermissions();
    if (!$hasDirectoryPermissions)
      return;
    $lisitngType_id = $listType->listingtype_id;
    $fileName = $this->_fileNamePre . str_replace(" ", "_", strtolower($listType->title_plural)) . ".csv";
    $oldFileName = null;
    if ($listType->csv_file_name)
      $oldFileName = $listType->csv_file_name;

    $replaceDatas = $this->getReplaceData($listType);
    $replaceDataWithoutKey = $this->getReplaceDataWithoutKey($listType);
    $this->addLanguageFiles($fileName, $replaceDatas, $replaceDataWithoutKey, $oldFileName);
    $listType->csv_file_name = $fileName;
    $listType->save();
  }

  public function removeTranslateForListType($listType) {
    $fileName = $this->_fileNamePre . strtolower($listType->title_plural) . ".csv";
    $translate = Zend_Registry::get('Zend_Translate');

    // Prepare language list
    $languageList = $translate->getList();
    foreach ($languageList as $locale) {
      $language_file = $this->_languagePath . '/' . $locale . '/' . $fileName;

      if (file_exists($language_file)) {
        @unlink($language_file);
      }
    }
  }

  public function convertData($datas, $replaceDatas, $replaceDataWithoutKey) {
    $data = array();
    foreach ($datas as $data_key => $data) {
      foreach ($replaceDatas as $search => $replace) {
        $pos = strpos($data_key, $search);
        if ($pos !== false) {
          if (isset($datas[$data_key]))
            unset($datas[$data_key]);
          $data_key = str_replace($search, $replace, $data_key);
        }
        $data = str_replace($search, $replace, $data);
      }

      if (is_array($replaceDataWithoutKey)) {
        foreach ($replaceDataWithoutKey as $search => $replace) {
          $data = str_replace($search, $replace, $data);
        }
      }

      $datas[$data_key] = $data;
    }
    return $datas;
  }

}