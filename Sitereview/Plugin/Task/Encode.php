<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Encode.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Plugin_Task_Encode extends Core_Plugin_Task_Abstract {

  public function getTotal() {
    $table = Engine_Api::_()->getDbTable('videos', 'sitereview');
    return $table->select()
                    ->from($table->info('name'), new Zend_Db_Expr('COUNT(*)'))
                    ->where('status = ?', 0)
                    ->query()
                    ->fetchColumn(0)
    ;
  }

  public function execute() {
    // Check allowed jobs vs executing jobs
    // @todo this does not function correctly as the task system only allows
    // one to run at a time, unless encoding takes more than 15 minutes anyway
    $sitereviewvideoTable = Engine_Api::_()->getItemTable('sitereview_video');
    $maxAllowedJobs = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.video.jobs', 2);
    $currentlyEncodingCount = $sitereviewvideoTable
            ->select()
            ->from($sitereviewvideoTable->info('name'), new Zend_Db_Expr('COUNT(*)'))
            ->where('status = ?', 2)
            ->query()
            ->fetchColumn(0)
    ;

    // Let's run some more
    $startedCount = 0;
    if ($currentlyEncodingCount < $maxAllowedJobs) {
      //for( $i = $currentlyEncodingCount + 1, $l = $maxAllowedJobs; $i <= $l; $i++ ) {
      $sitereviewvideoSelect = $sitereviewvideoTable->select()
              ->where('status = ?', 0)
              ->order('video_id ASC')
              ->limit(1)
      ;
      $sitereview_video = $sitereviewvideoTable->fetchRow($sitereviewvideoSelect);
      if ($sitereview_video instanceof Sitereview_Model_Sitereview) {
        $startedCount++;
        $this->_process($sitereview_video);
      }
      //}
    }

    // We didn't do anything
    if ($startedCount <= 0) {
      $this->_setWasIdle();
    }
  }

  protected function _process($sitereview_video) {
    // Make sure FFMPEG path is set
    $ffmpeg_path = Engine_Api::_()->getApi('settings', 'core')->sitereview_video_ffmpeg_path;
    if (!$ffmpeg_path) {
      $error_msg1 = Zend_Registry::get('Zend_Translate')->_('Ffmpeg not configured');
      throw new Sitereview_Model_Exception($error_msg1);
    }
    // Make sure FFMPEG can be run
    if (!@file_exists($ffmpeg_path) || !@is_executable($ffmpeg_path)) {
      $output = null;
      $return = null;
      exec($ffmpeg_path . ' -version', $output, $return);
      if ($return > 0) {
        $error_msg2 = Zend_Registry::get('Zend_Translate')->_('Ffmpeg found, but is not executable');
        throw new Sitereview_Model_Exception($error_msg2);
      }
    }

    // Check we can execute
    if (!function_exists('shell_exec')) {
      $error_msg3 = Zend_Registry::get('Zend_Translate')->_('Unable to execute shell commands using shell_exec(); the function is disabled.');
      throw new Sitereview_Model_Exception($error_msg3);
    }

    // Check the video temporary directory
    $tmpDir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary' .
            DIRECTORY_SEPARATOR . 'sitereview_video';
    if (!is_dir($tmpDir)) {
      if (!mkdir($tmpDir, 0777, true)) {
        $error_msg4 = Zend_Registry::get('Zend_Translate')->_('Video temporary directory did not exist and could not be created.');
        throw new Sitereview_Model_Exception($error_msg4);
      }
    }
    if (!is_writable($tmpDir)) {
      $error_msg5 = Zend_Registry::get('Zend_Translate')->_('Video temporary directory is not writable.');
      throw new Sitereview_Model_Exception($error_msg5);
    }

    // Get the video object
    if (is_numeric($sitereview_video)) {
      $sitereview_video = Engine_Api::_()->getItem('sitereview_video', $video_id);
    }

    if (!($sitereview_video instanceof Sitereview_Model_Video)) {
      $error_msg6 = Zend_Registry::get('Zend_Translate')->_('Argument was not a valid video');
      throw new Sitereview_Model_Exception($error_msg6);
    }

    // Update to encoding status
    $sitereview_video->status = 2;
    $sitereview_video->save();

    // Prepare information
    $owner = $sitereview_video->getOwner();
    $filetype = $sitereview_video->code;

    $originalPath = $tmpDir . DIRECTORY_SEPARATOR . $sitereview_video->getIdentity() . '.' . $filetype;
    $outputPath = $tmpDir . DIRECTORY_SEPARATOR . $sitereview_video->getIdentity() . '_vconverted.flv';
    $thumbPath = $tmpDir . DIRECTORY_SEPARATOR . $sitereview_video->getIdentity() . '_vthumb.jpg';

    $sitereviewvideoCommand = $ffmpeg_path . ' '
            . '-i ' . escapeshellarg($originalPath) . ' '
            . '-ab 64k' . ' '
            . '-ar 44100' . ' '
            . '-qscale 5' . ' '
            . '-vcodec flv' . ' '
            . '-f flv' . ' '
            . '-r 25' . ' '
            . '-s 480x386' . ' '
            . '-v 2' . ' '
            . '-y ' . escapeshellarg($outputPath) . ' '
            . '2>&1'
    ;

    $thumbCommand = $ffmpeg_path . ' '
            . '-i ' . escapeshellarg($outputPath) . ' '
            . '-f image2' . ' '
            . '-ss 4.00' . ' '
            . '-v 2' . ' '
            . '-y ' . escapeshellarg($thumbPath) . ' '
            . '2>&1'
    ;

    // Prepare output header
    $output = PHP_EOL;
    $output .= $originalPath . PHP_EOL;
    $output .= $outputPath . PHP_EOL;
    $output .= $thumbPath . PHP_EOL;

    // Prepare logger
    $log = null;
    //if( APPLICATION_ENV == 'development' ) {
    $log = new Zend_Log();
    $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/temporary/log/sitereview_video.log'));
    //}
    // Execute sitereviewvideo encode command
    $sitereviewvideoOutput = $output .
            $sitereviewvideoCommand . PHP_EOL .
            shell_exec($sitereviewvideoCommand);

    // Log
    if ($log) {
      $log->log($sitereviewvideoOutput, Zend_Log::INFO);
    }

    // Check for failure
    $success = true;

    // Unsupported format
    if (preg_match('/Unknown format/i', $sitereviewvideoOutput) ||
            preg_match('/Unsupported codec/i', $sitereviewvideoOutput) ||
            preg_match('/patch welcome/i', $sitereviewvideoOutput) ||
            !is_file($outputPath) ||
            filesize($outputPath) <= 0) {
      $success = false;
      $sitereview_video->status = 3;
    }

    // This is for audio files
    else if (preg_match('/sitereviewvideo:0kB/i', $sitereviewvideoOutput)) {
      $success = false;
      $sitereview_video->status = 5;
    }

    // Failure
    if (!$success) {

      $db = $sitereview_video->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $sitereview_video->save();

        // notify the owner
        $translate = Zend_Registry::get('Zend_Translate');
        $language = (!empty($owner->language) && $owner->language != 'auto' ? $owner->language : null );
        $notificationMessage = '';
        if ($sitereview_video->status == 3) {
          $notificationMessage = $translate->translate(sprintf(
                          'Video conversion failed. Sitereview format is not supported by FFMPEG. Please try %1$sagain%2$s.', '', ''
                  ), $language);
        } else if ($sitereview_video->status == 5) {
          $notificationMessage = $translate->translate(sprintf(
                          'SVideo conversion failed. Audio files are not supported. Please try %1$sagain%2$s.', '', ''
                  ), $language);
        }
        Engine_Api::_()->getDbtable('notifications', 'activity')
                ->addNotification($owner, $owner, $sitereview_video, 'sitereview_video_processed_failed', array(
                    'message' => $notificationMessage,
                    'message_link' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'view'), 'sitereview_video_general', true),
                ));

        $db->commit();
      } catch (Exception $e) {
        $sitereviewvideoOutput .= PHP_EOL . $e->__toString() . PHP_EOL;
        if ($log) {
          $log->write($e->__toString(), Zend_Log::ERR);
        }
        $db->rollBack();
      }

      // Write to additional log in dev
      if (APPLICATION_ENV == 'development') {
        file_put_contents($tmpDir . '/' . $sitereview_video->video_id . '.txt', $sitereviewvideoOutput);
      }
    }

    // Success
    else {
      // Get duration of the sitereviewvideo to caculate where to get the thumbnail
      if (preg_match('/Duration:\s+(.*?)[.]/i', $sitereviewvideoOutput, $matches)) {
        list($hours, $minutes, $seconds) = preg_split('[:]', $matches[1]);
        $duration = ceil($seconds + ($minutes * 60) + ($hours * 3600));
      } else {
        $duration = 0; // Hmm
      }

      // Log duration
      if ($log) {
        $log->log('Duration: ' . $duration, Zend_Log::INFO);
      }

      // Process thumbnail
      $thumbOutput = $output .
              $thumbCommand . PHP_EOL .
              shell_exec($thumbCommand);

      // Log thumb output
      if ($log) {
        $log->log($thumbOutput, Zend_Log::INFO);
      }

      // Resize thumbnail
      $image = Engine_Image::factory();
      $image->open($thumbPath)
              ->resize(120, 240)
              ->write($thumbPath)
              ->destroy();

      // Save sitereviewvideo and thumbnail to storage system
      $params = array(
          'parent_id' => $sitereview_video->getIdentity(),
          'parent_type' => $sitereview_video->getType(),
          'user_id' => $sitereview_video->owner_id
      );

      $db = $sitereview_video->getTable()->getAdapter();
      $db->beginTransaction();

      try {
        $sitereviewvideoFileRow = Engine_Api::_()->storage()->create($outputPath, $params);
        $thumbFileRow = Engine_Api::_()->storage()->create($thumbPath, $params);

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();

        // delete the files from temp dir
        unlink($originalPath);
        unlink($outputPath);
        unlink($thumbPath);

        $sitereview_video->status = 7;
        $sitereview_video->save();

        // notify the owner
        $translate = Zend_Registry::get('Zend_Translate');
        $notificationMessage = '';
        $language = (!empty($owner->language) && $owner->language != 'auto' ? $owner->language : null );
        if ($sitereview_video->status == 7) {
          $notificationMessage = $translate->translate(sprintf(
                          'Video conversion failed. You may be over the site upload limit.  Try %1$suploading%2$s a smaller file, or delete some files to free up space.', '', ''
                  ), $language);
        }
        Engine_Api::_()->getDbtable('notifications', 'activity')
                ->addNotification($owner, $owner, $sitereview_video, 'sitereview_video_processed_failed', array(
                    'message' => $notificationMessage,
                    'message_link' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'view'), 'sitereview_video_general', true),
                ));

        throw $e; // throw
      }

      // Sitereviewvideo processing was a success!
      // Save the information
      $sitereview_video->file_id = $sitereviewvideoFileRow->file_id;
      $sitereview_video->photo_id = $thumbFileRow->file_id;
      $sitereview_video->duration = $duration;
      $sitereview_video->status = 1;
      $sitereview_video->save();

      // delete the files from temp dir
      unlink($originalPath);
      unlink($outputPath);
      unlink($thumbPath);

      // insert action in a seperate transaction if sitereviewvideo status is a success
      $actionsTable = Engine_Api::_()->getDbtable('actions', 'activity');
      $db = $actionsTable->getAdapter();
      $db->beginTransaction();

      try {
        // new action
        $subject = Engine_Api::_()->getItem('sitereview_listing', $sitereview_video->listing_id);
        if(time() >= strtotime($subject->creation_date)) {          
            $action = $actionsTable->addActivity($owner, $sitereview_video->getParent(), 'sitereview_video_new_listtype_' . $sitereview_video->getParent()->listingtype_id);
            if ($action) {
              $actionsTable->attachActivity($action, $sitereview_video);
            }
        }

        // notify the owner
        Engine_Api::_()->getDbtable('notifications', 'activity')
                ->addNotification($owner, $owner, $sitereview_video, 'sitereview_video_processed');

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e; // throw
      }
    }
  }

}