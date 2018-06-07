<?php

class Advancedactivity_StoryController extends Siteapi_Controller_Action_Standard {

    /**
     * Init model
     *
     */
    public function init() {
        if (0 !== ($story_id = (int) $this->getRequestParam('story_id')) &&
                null !== ($subject = Engine_Api::_()->getItem('advancedactivity_story', $story_id)))
            Engine_Api::_()->core()->setSubject($subject);
        Engine_Api::_()->getApi('Core', 'siteapi')->setView();
        Engine_Api::_()->getApi('Core', 'siteapi')->setLocal();
    }

    public function createAction() {

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        if (empty($viewer_id))
            $this->respondWithError('unauthorized');

        if ($this->getRequest()->isGet()) {
            //advanced activity feed

            $response = Engine_Api::_()->getApi('Siteapi_Core', 'advancedactivity')->getStoryForm();
            $this->respondWithSuccess($response);
        } else if ($this->getRequest()->isPost()) {
            $values = $data = $_REQUEST;

            $values = @array_merge($values, array(
                        'owner_type' => $viewer->getType(),
                        'owner_id' => $viewer->getIdentity(),
            ));

            $createDate = date('Y-m-d H:i:s');
            $days = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity_max_allowed_days', 1);
            $pastDate = date('Y-m-d H:i:s', strtotime('+' . $days . ' day', strtotime($currentDate)));
            //$expiryDate = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($createDate)));
            $expiryDate = $pastDate;
            $values['create_date'] = $createDate;
            $values['expiry_date'] = $expiryDate;

            $db = Engine_Api::_()->getDbtable('stories', 'advancedactivity')->getAdapter();
            $db->beginTransaction();
            try {
                $table = Engine_Api::_()->getDbtable('stories', 'advancedactivity');

                $story = $table->createRow();
                $story->setFromArray($values);
                $story->save();

                if (isset($_FILES['filedata']) && !empty($_FILES['filedata'])) {

                    if (!is_uploaded_file($_FILES['filedata']['tmp_name']))
                        $this->respondWithError("invalid_upload");

                    $extensions = array('mp4', 'flv');
                    if (in_array(pathinfo($_FILES['filedata']['name'], PATHINFO_EXTENSION), $extensions)) {
                        if ($_FILES['filedata'] instanceof Storage_Model_File) {
                            $params['file_id'] = $_FILES['filedata']->getIdentity();
                        } else {
                            // create video item
                            $file_ext = pathinfo($file['name']);
                            $file_ext = $file_ext['extension'];
                            $story->code = $file_ext;
                            $story->save();

                            // Channel video in temporary storage object for ffmpeg to handle
                            $storage = Engine_Api::_()->getItemTable('storage_file');
                            $storageObject = $storage->createFile($_FILES['filedata'], array(
                                'parent_id' => $story->getIdentity(),
                                'parent_type' => $story->getType(),
                                'user_id' => $story->owner_id,
                            ));

                            // Remove temporary file
                            @unlink($file['tmp_name']);

                            $story->file_id = $storageObject->file_id;
                            $story->save();
                            $this->_process($story);
                        }
                    }
                } else {
                    $story = Engine_Api::_()->getApi('Siteapi_Core', 'advancedactivity')->setPhoto($_FILES['photo'], $story);
                }
                $db->commit();
                $response['response']['story_id'] = $story->getIdentity();
                $this->respondWithSuccess($response);
                $this->successResponseNoContent('no_content');
            } catch (Exception $ex) {
                $db->rollBack();

                $this->respondWithValidationError('internal_server_error', $ex->getMessage());
            }
        }
    }

    public function browseAction() {

        $table = Engine_Api::_()->getDbtable('stories', 'advancedactivity');

        $subject = $user = Engine_Api::_()->user()->getViewer();
        $user_id = $user->getIdentity();
        if (empty($user_id))
            $this->respondWithError('unauthorized');
        try {


            $limit = (int) $this->getRequestParam('limit', 20);
            $page = (int) $this->getRequestParam('page', 1);
            $paginator = $table->getStoryPaginator($subject);
            $paginator->setItemCountPerPage($limit);
            $paginator->setCurrentPageNumber($page);
            $params['totalItemCount'] = $paginator->getTotalItemCount();
            $response = array();
            $response[] = $this->_getYourStory($user);
            $owner_ids = array();
            foreach ($paginator as $story) {
                if ($story->owner_id == $user_id) {
                    continue;
                }
                $owner_ids[] = $story->owner_id;
                $browseStory = $story->toArray();
                $browseStory["total_stories"] = Engine_Api::_()->getDbtable('stories', 'advancedactivity')->getTotalStoryCount($story->owner_id);
                $browseStory["owner_title"] = $story->getOwner()->getTitle();

                $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($story);
                $browseStory = array_merge($browseStory, $getContentImages);

                $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($story, true);
                $browseStory = array_merge($browseStory, $getContentImages);
                if ($story->file_id) {
                    $browseStory['videoUrl'] = Engine_Api::_()->getApi('Siteapi_Core', 'advancedactivity')->getVideoUrl($story);
                } else {
                    $browseStory['videoUrl'] = '';
                }


                $response[] = $browseStory;
            }
        } catch (Exception $ex) {
            
        }

        if (count($response) < 4) {
            $browseStory = array();
            $response = $this->_getEveryoneStory($response);
            if (count($response) > 4) {
                $this->respondWithSuccess($response);
            }
            $select = $subject->membership()->getMembersOfSelect();
            $friends = $paginator = Zend_Paginator::factory($select);
            $paginator->setItemCountPerPage(40);
            $paginator->setCurrentPageNumber(1);
            $i = 0;
            foreach ($friends as $friend) {
                if (in_array($friend->resource_id, $owner_ids))
                    continue;
                if ($i == 4)
                    break;
                $user = Engine_Api::_()->getItem('user', $friend->resource_id);
                $browseStory["owner_title"] = $user->getTitle();
                $browseStory["owner_id"] = $user->getIdentity();
                $browseStory["total_stories"] = Engine_Api::_()->getDbtable('stories', 'advancedactivity')->getTotalStoryCount($user->user_id);
                $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($user, true);
                $browseStory = array_merge($browseStory, $getContentImages);
                $response[] = $browseStory;
                $i++;
            }
        }

        $this->respondWithSuccess($response);
    }

    public function viewAction() {
        $table = Engine_Api::_()->getDbtable('stories', 'advancedactivity');
        $viewer = Engine_Api::_()->user()->getViewer();
        if (Engine_Api::_()->core()->hasSubject())
            $story = Engine_Api::_()->core()->getSubject();

        if (empty($story)) {
            $this->respondWithError('no_record');
        }
        if ($story->isOwner($viewer)) {
            $user_id = $viewer->getIdentity();
        } else {
            $user_id = $story->owner_id;
        }
        if (empty($user_id))
            $this->respondWithError('unauthorized');

        $isSendMessage = 0;
        if (!$story->isOwner($viewer)) {
            try {
                $isSendMessage = Engine_Api::_()->getApi('Siteapi_Core', 'advancedactivity')->isAllowMessage($viewer, $story);
            } catch (Exception $ex) {
                $isSendMessage = 0;
            }
        } else {
            $isSendMessage=1;
        }
        
        $stories = $limit = (int) $this->getRequestParam('limit', 200);
        $page = (int) $this->getRequestParam('page', 1);
        $paginator = $table->getUserStory($user_id,$isSendMessage);
        $paginator->setItemCountPerPage($limit);
        $paginator->setCurrentPageNumber($page);
        //$response['totalItemCount'] = $paginator->getTotalItemCount();

        foreach ($paginator as $story) {
            $browseStory = $story->toArray();
            $browseStory["owner_title"] = $story->getOwner()->getTitle();
            $browseStory["isSendMessage"] = $isSendMessage;
            $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($story);
            $browseStory = array_merge($browseStory, $getContentImages);

            $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($story, true);
            $browseStory = array_merge($browseStory, $getContentImages);
            $isViewed = 1;
            if (!$story->isOwner($viewer)) {
                $isViewed = $this->_isViewerStory($story, $viewer);
            }
            $browseStory['isViewed'] = $isViewed;
            if (!$story->isOwner($viewer)) {
                if ($story->privacy == 'owner') {
                    $browseStory['authView'] = 0;
                } else {
                    $browseStory['authView'] = 1;
                }
            } else {
                $browseStory['authView'] = 1;
            }
            if ($story->file_id) {
                $browseStory['videoUrl'] = Engine_Api::_()->getApi('Siteapi_Core', 'advancedactivity')->getVideoUrl($story);
            } else {
                $browseStory['videoUrl'] = '';
            }

            $browseStory['gutterMenu'] = $this->_gutterMenus($story);
            $response[] = $browseStory;
        }

        $this->respondWithSuccess($response);
    }

    public function getViewerAction() {
        $viewer = Engine_Api::_()->user()->getViewer();
        $story = Engine_Api::_()->core()->getSubject();

        if (empty($story))
            $this->respondWithError('no_record');
        try {
            $table = Engine_Api::_()->getDbtable('viewers', 'advancedactivity');
            $storyViewers = $table->select()
                    ->where('object_id = ?', $story->getIdentity());

            $paginator = Zend_Paginator::factory($storyViewers);

            $limit = (int) $this->getRequestParam('limit', 20);
            $page = (int) $this->getRequestParam('page', 1);
            $paginator->setItemCountPerPage($limit);
            $paginator->setCurrentPageNumber($page);
            $users['totalItemCount'] = $paginator->getTotalItemCount();
            //->query()
            // ->fetchAll();
            foreach ($paginator as $storyViewer) {
                $user = Engine_Api::_()->getItem('user', $storyViewer->subject_id);
                $tempUser = Engine_Api::_()->getApi('Core', 'siteapi')->validateUserArray($user);

                // Add images
                $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($user);
                $tempUser = array_merge($tempUser, $getContentImages);
                $users['response'][] = $tempUser;
            }
            $this->respondWithSuccess($users);
        } catch (Exception $ex) {
            
        }
    }

    public function editAction() {
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        if (Engine_Api::_()->core()->hasSubject())
            $story = Engine_Api::_()->core()->getSubject();

        // RETURN IF NO SUBJECT AVAILABLE.
        if (empty($story))
            $this->respondWithError('no_record');


        if (empty($viewer_id))
            $this->respondWithError('unauthorized');

        if ($this->getRequest()->isGet()) {
            //advanced activity feed

            $response['form'] = Engine_Api::_()->getApi('Siteapi_Core', 'advancedactivity')->getStoryForm();
            $response['formValues'] = $story->toArray();
            $this->respondWithSuccess($response);
        } else if ($this->getRequest()->isPost()) {
            $values = $data = $_REQUEST;
            $db = Engine_Api::_()->getDbtable('stories', 'advancedactivity')->getAdapter();
            $db->beginTransaction();
            try {
                $story->setFromArray($values);
                $story->save();
                $db->commit();
                $this->successResponseNoContent('no_content', true);
            } catch (Exception $ex) {
                $db->rollBack();
                $this->respondWithValidationError('internal_server_error', $ex->getMessage());
            }
        }
    }

    public function deleteAction() {
        // Validate request methods
        $this->validateRequestMethod('DELETE');

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        if (empty($viewer_id))
            $this->respondWithError('unauthorized');

        if (Engine_Api::_()->core()->hasSubject())
            $story = Engine_Api::_()->core()->getSubject();

        // RETURN IF NO SUBJECT AVAILABLE.
        if (empty($story))
            $this->respondWithError('no_record');

        $db = $story->getTable()->getAdapter();
        $db->beginTransaction();
        try {

            if (Engine_Api::_()->getItem('storage_file', $story->file_id))
                Engine_Api::_()->getItem('storage_file', $story->file_id)->remove();

            if (Engine_Api::_()->getItem('storage_file', $story->photo_id) && $story->photo_id)
                Engine_Api::_()->getItem('storage_file', $story->photo_id)->remove();

            Engine_Api::_()->getDbtable('viewers', 'advancedactivity')->delete(array(
                'object_id = ?' => $story->story_id,
            ));

            $story->delete();
            $db->commit();
            $this->successResponseNoContent('no_content', true);
        } catch (Exception $ex) {
            $db->rollBack();
            $this->respondWithValidationError('internal_server_error', $ex->getMessage());
        }
    }

    public function viewerCountAction() {
        $this->validateRequestMethod('POST');
        $table = Engine_Api::_()->getDbtable('stories', 'advancedactivity');
        $viewer = Engine_Api::_()->user()->getViewer();
        if (Engine_Api::_()->core()->hasSubject())
            $story = Engine_Api::_()->core()->getSubject();

        if (empty($story)) {
            $this->respondWithError('no_record');
        }


        if (!$story->isOwner($viewer)) {
            $isViewed = $this->_isViewerStory($story, $viewer);
            if (!$isViewed) {
                $story->view_count++;
                $story->save();
                $this->_addViewerDetails($story, $viewer);
            }
        }
        $this->successResponseNoContent('no_content', true);
    }

    private function _getYourStory($user = array()) {
        if (empty($user))
            return;
        $table = Engine_Api::_()->getDbtable('stories', 'advancedactivity');
        $result = $table->getyourStory($user->user_id);
        $total = $result->getTotalItemCount();

        if ($total > 0) {
            foreach ($result as $story) {
                $browseStory = $story->toArray();
                $browseStory["owner_title"] = 'Your Story';
                $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($story);
                $browseStory = array_merge($browseStory, $getContentImages);

                $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($story, true);
                $browseStory = array_merge($browseStory, $getContentImages);
                $browseStory["total_stories"] = Engine_Api::_()->getDbtable('stories', 'advancedactivity')->getTotalStoryCount($story->owner_id);
                if ($story->file_id) {
                    $browseStory['videoUrl'] = Engine_Api::_()->getApi('Siteapi_Core', 'advancedactivity')->getVideoUrl($story);
                } else {
                    $browseStory['videoUrl'] = '';
                }
                $browseStory['gutterMenu'] = $this->_gutterMenus($story);
                break;
            }
        } else {
            $browseStory["owner_title"] = 'Your Story';
            $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($user, true);
            $browseStory = array_merge($browseStory, $getContentImages);
        }

        return $browseStory;
    }

    private function _addViewerDetails($story, $viewer) {
        if (empty($story) || empty($viewer)) {
            return;
        }
        $db = Engine_Api::_()->getDbtable('viewers', 'advancedactivity')->getAdapter();
        $db->beginTransaction();
        try {
            $table = Engine_Api::_()->getDbtable('viewers', 'advancedactivity');
            $row = $table->select()
                    ->where('subject_id =?', $viewer->getIdentity())
                    ->where('object_id = ?', $story->getIdentity())
                    ->query()
                    ->fetchAll();
            $rowCount = count($row);
            if ($rowCount == 0) {
                $values['subject_id'] = $viewer->getIdentity();
                $values['subject_type'] = $viewer->getType();
                $values['object_id'] = $story->getIdentity();
                $values['object_type'] = $story->getType();
                $row = $table->createRow();
                $row->setFromArray($values);
                $row->save();
                $db->commit();
            }
        } catch (Exception $ex) {
            $db->rollBack();
            $this->respondWithValidationError('internal_server_error', $ex->getMessage());
        }
    }

    private function _isViewerStory($story, $viewer) {
        try {
            $table = Engine_Api::_()->getDbtable('viewers', 'advancedactivity');
            $row = $table->select()
                    ->where('subject_id =?', $viewer->getIdentity())
                    ->where('object_id = ?', $story->getIdentity())
                    ->query()
                    ->fetchAll();
            $rowCount = count($row);
            if ($rowCount > 0) {
                return 1;
            } else
                return 0;
        } catch (Exception $ex) {
            
        }
    }

    protected function _process($video) {
        $tmpDir = $this->getTmpDir();

        if (!Zend_Registry::isRegistered('Zend_Translate'))
            Engine_Api::_()->getApi('Core', 'siteapi')->setTranslate();

        Engine_Api::_()->getApi('Core', 'siteapi')->setView();

        // Prepare information
        $owner = $video->getOwner();

        // Pull video from storage system for encoding
        $storageObject = $this->getStorageObject($video);
        $originalPath = $this->getOriginalPath($storageObject);

        $outputPath = $tmpDir . DIRECTORY_SEPARATOR . $video->getIdentity() . '_vconverted.' . $type;
        $thumbPath = $tmpDir . DIRECTORY_SEPARATOR . $video->getIdentity() . '_vnormalthumb.jpg';

        $thumbNormalLargePath = $tmpDir . DIRECTORY_SEPARATOR . $video->getIdentity() . '_vnormallargethumb.jpg';
        $thumbMainPath = $tmpDir . DIRECTORY_SEPARATOR . $video->getIdentity() . '_vmainthumb.jpg';
        $width = 480;
        $height = 386;

        $videoCommand = $this->buildVideoCmd($video, $width, $height, $type, $originalPath, $outputPath, $compatibilityMode);

        // Prepare output header
        $output = PHP_EOL;
        $output .= $originalPath . PHP_EOL;
        $output .= $outputPath . PHP_EOL;
        $output .= $thumbPath . PHP_EOL;

        // Prepare logger
        $log = new Zend_Log();
        $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/temporary/log/sitevideo.log'));

        // Execute video encode command
        $videoOutput = $output .
                $videoCommand . PHP_EOL .
                shell_exec($videoCommand);

        // Log
        if ($log) {
            $log->log($videoOutput, Zend_Log::INFO);
        }

        // Check for failure
        $success = $this->conversionSucceeded($video, $videoOutput, $outputPath);
        // Failure
        if (!$success) {
            if (!$compatibilityMode) {
                $this->_process($video, true);
                return;
            }

            $exceptionMessage = '';

            $db = $video->getTable()->getAdapter();
            $db->beginTransaction();

            try {
                $video->save();
                $exceptionMessage = $this->notifyOwner($video, $owner);
                $db->commit();
            } catch (Exception $e) {
                $videoOutput .= PHP_EOL . $e->__toString() . PHP_EOL;

                if ($log) {
                    $log->write($e->__toString(), Zend_Log::ERR);
                }

                $db->rollBack();
            }

            // Write to additional log in dev
            if (APPLICATION_ENV == 'development') {
                file_put_contents($tmpDir . '/' . $video->video_id . '.txt', $videoOutput);
            }

            throw new Sitevideo_Model_Exception($exceptionMessage);
        }

        // Success
        else {
            // Get duration of the video to caculate where to get the thumbnail
            $duration = $this->getDuration($videoOutput);

            // Log duration
            if ($log) {
                $log->log('Duration: ' . $duration, Zend_Log::INFO);
            }

            // Fetch where to take the thumbnail
            $thumb_splice = $duration / 2;

            $thumbMainSuccess = $this->generateMainThumbnail($outputPath, $output, $thumb_splice, $thumbMainPath, $log);

            // Save video and thumbnail to storage system
            $params = array(
                'parent_id' => $video->getIdentity(),
                'parent_type' => $video->getType(),
                'user_id' => $video->owner_id
            );

            $db = $video->getTable()->getAdapter();
            $db->beginTransaction();

            try {
                $storageObject->setFromArray($params);
                //$storageObject->store($outputPath);

                if ($thumbMainSuccess) {
                    $thumbMainSuccessRow = Engine_Api::_()->storage()->create($thumbMainPath, array_merge($params, array('type' => 'thumb.main')));
                }

                $thumbPath = $tmpDir . DIRECTORY_SEPARATOR . $video->getIdentity() . '_vnormalthumb.jpg';

                $thumbNormalLargePath = $tmpDir . DIRECTORY_SEPARATOR . $video->getIdentity() . '_vnormallargethumb.jpg';

                $image = Engine_Image::factory();
                $image->open($thumbMainPath)
                        ->resize(720, 720)
                        ->write($thumbNormalLargePath)
                        ->destroy();

                $image = Engine_Image::factory();
                $image->open($thumbMainPath)
                        ->resize(375, 375)
                        ->write($thumbPath)
                        ->destroy();
                Engine_Api::_()->storage()->create($thumbNormalLargePath, array_merge($params, array('type' => 'thumb.large')));
                $thumbNormalSuccessRow = Engine_Api::_()->storage()->create($thumbPath, array_merge($params, array('type' => 'thumb.normal')));

                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();

                // delete the files from temp dir
                unlink($originalPath);
                unlink($outputPath);

                if ($thumbSuccess) {
                    unlink($thumbPath);
                }

                $video->save();

                $this->notifyOwner($video, $owner);

                throw $e; // throw
            }

            // Video processing was a success!
            // Save the information
            if ($thumbMainSuccess) {
                $video->photo_id = $thumbMainSuccessRow->file_id;
            }

            $video->duration = $duration;
            $video->save();

            // delete the files from temp dir
            unlink($originalPath);
            unlink($outputPath);
            unlink($thumbPath);
            unlink($thumbMainPath);
            unlink($thumbNormalLargePath);
            // insert action in a separate transaction if video status is a success
            $actionsTable = Engine_Api::_()->getDbtable('actions', 'activity');
            $db = $actionsTable->getAdapter();
            $db->beginTransaction();

//            try {
//                // new action
//                $chanel = $video->getChannelModel();
//                $actionType = $chanel ? 'sitevideo_channel_video_new' : 'sitevideo_video_new';
//                $actionObject = $chanel ? $chanel : $video;
//                $action = $actionsTable->addActivity($owner, $actionObject, $actionType);
//
//                if ($action) {
//                    $actionsTable->attachActivity($action, $video);
//                }
//
//                // notify the owner
//                Engine_Api::_()->getDbtable('notifications', 'activity')
//                        ->addNotification($owner, $owner, $video, 'sitevideo_processed');
//
//                $db->commit();
//            } catch (Exception $e) {
//                $db->rollBack();
//                throw $e; // throw
//            }
        }
    }

    private function generateMainThumbnail($outputPath, $output, $thumb_splice, $thumbPath, $log) {
        set_time_limit(0);
        $ffmpeg_path = $this->getFFMPEGPath();
        // Thumbnail process command
        $thumbCommand = $ffmpeg_path . ' '
                . '-i ' . escapeshellarg($outputPath) . ' '
                . '-f image2' . ' '
                . '-ss ' . $thumb_splice . ' '
                . '-vframes 1' . ' '
                . '-v 2' . ' '
                . '-y ' . escapeshellarg($thumbPath) . ' '
                . '2>&1';

        // Process thumbnail
        $thumbOutput = $output .
                $thumbCommand . PHP_EOL .
                shell_exec($thumbCommand);

        // Log thumb output
        if ($log) {
            $log->log($thumbOutput, Zend_Log::INFO);
        }

        // Check output message for success
        $thumbSuccess = true;
        if (preg_match('/video:0kB/i', $thumbOutput)) {
            $thumbSuccess = false;
        }
        $mainHeight = Engine_Api::_()->getApi('settings', 'core')->getSetting('main.video.height', 1600);
        $mainWidth = Engine_Api::_()->getApi('settings', 'core')->getSetting('main.video.height', 1600);
        // Resize thumbnail
        if ($thumbSuccess) {
            try {
                $image = Engine_Image::factory();
                $image->open($thumbPath)
                        ->resize($mainHeight, $mainWidth)
                        ->write($thumbPath)
                        ->destroy();
            } catch (Exception $e) {
                $this->_addMessage((string) $e->__toString());
                $thumbSuccess = false;
            }
        }

        return $thumbSuccess;
    }

    private function getTmpDir() {
        // Check the video temporary directory
        $tmpDir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary' .
                DIRECTORY_SEPARATOR . 'sitevideo';

        if (!is_dir($tmpDir) && !mkdir($tmpDir, 0777, true)) {
            return;
        }

        if (!is_writable($tmpDir)) {
            return;
        }
        return $tmpDir;
    }

    private function getStorageObject($video) {
        // Pull video from storage system for encoding
        $storageObject = Engine_Api::_()->getItem('storage_file', $video->file_id);

        if (!$storageObject) {
            return;
        }
        return $storageObject;
    }

    private function getOriginalPath($storageObject) {
        $originalPath = $storageObject->temporary();
        if (!file_exists($originalPath)) {
            return;
        }
        return $originalPath;
    }

    private function buildVideoCmd($video, $width, $height, $type, $originalPath, $outputPath, $compatibilityMode = false) {

        $ffmpeg_path = $this->getFFMPEGPath();

        $videoCommand = $ffmpeg_path . ' '
                . '-i ' . escapeshellarg($originalPath) . ' '
                . '-ab 64k' . ' '
                . '-ar 44100' . ' '
                . '-qscale 5' . ' '
                . '-r 25' . ' ';

        if ($type == 'mp4')
            $videoCommand .= '-vcodec libx264' . ' '
                    . '-acodec aac' . ' '
                    . '-strict experimental' . ' '
                    . '-preset veryfast' . ' '
                    . '-f mp4' . ' '
            ;
        else
            $videoCommand .= '-vcodec flv -f flv ';

        if ($compatibilityMode) {
            $videoCommand .= "-s ${width}x${height}" . ' ';
        } else {
            $filters = $this->getVideoFilters($video, $width, $height);
            $videoCommand .= '-vf "' . $filters . '" ';
        }

        $videoCommand .= '-y ' . escapeshellarg($outputPath) . ' '
                . '2>&1';
        return $videoCommand;
    }

    private function conversionSucceeded($video, $videoOutput, $outputPath) {
        $success = true;
        // Unsupported format

        if (preg_match('/Unknown format/i', $videoOutput) ||
                preg_match('/Unsupported codec/i', $videoOutput) ||
                preg_match('/patch welcome/i', $videoOutput) ||
                preg_match('/Audio encoding failed/i', $videoOutput) ||
                !is_file($outputPath) ||
                filesize($outputPath) <= 0) {
            $success = false;
            $video->status = 3;
        }

        // This is for audio files
        else if (preg_match('/video:0kB/i', $videoOutput)) {
            $success = false;
        }

        return $success;
    }

    private function notifyOwner($video, $owner) {
        $translate = Zend_Registry::get('Zend_Translate');
        $language = !empty($owner->language) && $owner->language != 'auto' ? $owner->language : null;

        $notificationMessage = '';
        $exceptionMessage = 'Unknown encoding error.';

        if ($video->status == 3) {
            $exceptionMessage = 'Video format is not supported by FFMPEG.';
            $notificationMessage = 'Video conversion failed. Video format is not supported by FFMPEG. Please try %1$sagain%2$s.';
        } else if ($video->status == 5) {
            $exceptionMessage = 'Audio-only files are not supported.';
            $notificationMessage = 'Video conversion failed. Audio files are not supported. Please try %1$sagain%2$s.';
        } else if ($video->status == 7) {
            $notificationMessage = 'Video conversion failed. You may be over the site upload limit.  Try %1$suploading%2$s a smaller file, or delete some files to free up space.';
        }

        $notificationMessage = $translate->translate(sprintf($notificationMessage, '', ''), $language);

        Engine_Api::_()->getDbtable('notifications', 'activity')
                ->addNotification($owner, $owner, $video, 'sitevideo_processed_failed', array(
                    'message' => $notificationMessage,
                    'message_link' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'manage'), 'sitevideo_general', true),
        ));

        return $exceptionMessage;
    }

    private function getDuration($videoOutput) {
        $duration = 0;

        if (preg_match('/Duration:\s+(.*?)[.]/i', $videoOutput, $matches)) {
            list($hours, $minutes, $seconds) = preg_split('[:]', $matches[1]);
            $duration = ceil($seconds + ($minutes * 60) + ($hours * 3600));
        }

        return $duration;
    }

    private function getVideoFilters($video, $width, $height) {
        $filters = "scale=$width:$height";
        if ($video->rotation > 0) {
            $filters = "pad='max(iw,ih*($width/$height))':ow/($width/$height):(ow-iw)/2:(oh-ih)/2,$filters";

            if ($video->rotation == 180)
                $filters = "hflip,vflip,$filters";
            else {
                $transpose = array(90 => 1, 270 => 2);

                if (empty($transpose[$video->rotation]))
                    return;
                $filters = "transpose=${transpose[$video->rotation]},$filters";
            }
        }

        return $filters;
    }

    private function getFFMPEGPath() {
        set_time_limit(0);
        // Check we can execute
        if (!function_exists('shell_exec')) {
            return;
        }

        if (!function_exists('exec')) {
            return;
        }
        $coreSettings = Engine_Api::_()->getApi('settings', 'core');

        // Make sure FFMPEG path is set
        $ffmpeg_path = $coreSettings->getSetting('sitevideo.ffmpeg.path', $coreSettings->getSetting('sitevideo.ffmpeg.path', ''));
        if (!$ffmpeg_path) {
            return;
        }

        // Make sure FFMPEG can be run
        if (!@file_exists($ffmpeg_path) || !@is_executable($ffmpeg_path)) {
            $output = null;
            $return = null;
            exec($ffmpeg_path . ' -version', $output, $return);

            if ($return > 0) {
                return;
            }
        }

        return $ffmpeg_path;
    }

    public function handleIframelyInformation($uri) {
        $iframelyDisallowHost = Engine_Api::_()->getApi('settings', 'core')->getSetting('video_iframely_disallow');
        if (parse_url($uri, PHP_URL_SCHEME) === null) {
            $uri = "http://" . $uri;
        }
        $uriHost = Zend_Uri::factory($uri)->getHost();
        if ($iframelyDisallowHost && in_array($uriHost, $iframelyDisallowHost)) {
            return;
        }
        $config = Engine_Api::_()->getApi('settings', 'core')->core_iframely;
        $iframely = Engine_Iframely::factory($config)->get($uri);
        if (!in_array('player', array_keys($iframely['links']))) {
            return;
        }
        $information = array('thumbnail' => '', 'title' => '', 'description' => '', 'duration' => '');
        if (!empty($iframely['links']['thumbnail'])) {
            $information['thumbnail'] = $iframely['links']['thumbnail'][0]['href'];
            if (parse_url($information['thumbnail'], PHP_URL_SCHEME) === null) {
                $information['thumbnail'] = str_replace(array('://', '//'), '', $information['thumbnail']);
                $information['thumbnail'] = "http://" . $information['thumbnail'];
            }
        }

        if (!isset($information['thumbnail']) || empty($information['thumbnail'])) {
            $page_content = file_get_contents($uri);
            $dom_obj = new DOMDocument();
            $dom_obj->loadHTML($page_content);
            $meta_val = null;

            foreach ($dom_obj->getElementsByTagName('meta') as $meta) {

                if ($meta->getAttribute('property') == 'og:image') {

                    $information['thumbnail'] = $meta->getAttribute('content');
                }
            }
        }

        if (!empty($iframely['meta']['title'])) {
            $information['title'] = $iframely['meta']['title'];
        }
        if (!empty($iframely['meta']['description'])) {
            $information['description'] = $iframely['meta']['description'];
        }
        if (!empty($iframely['meta']['duration'])) {
            $information['duration'] = $iframely['meta']['duration'];
        }
        $information['code'] = $iframely['html'];
        return $information;
    }

    private function _gutterMenus($story) {
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $owner = $story->getOwner();
        $owner_id = $owner->getIdentity();
        $menus = array();

        if ($owner_id == $viewer_id) {
            $menus[] = array(
                'label' => $this->translate('Delete'),
                'name' => 'delete',
                'url' => 'advancedactivity/story/delete/' . $story->getIdentity()
            );
            if (empty($story->file_id)) {
                $menus[] = array(
                    'label' => $this->translate('Save Photo'),
                    'name' => 'save',
                );
            } else {
                $menus[] = array(
                    'label' => $this->translate('Download Video'),
                    'name' => 'save_video',
                );
            }
            $menus[] = array(
                'label' => $this->translate('Story Settings'),
                'name' => 'setting',
                'url' => 'advancedactivity/story/edit/' . $story->getIdentity()
            );
        } else {
            $menus[] = array(
                'label' => $this->translate('Report'),
                'name' => 'report',
                'url' => 'report/create/subject/' . $story->getGuid(),
                'urlParams' => array(
                    "type" => $story->getType(),
                    "id" => $story->getIdentity()
                )
            );
        }
        return $menus;
    }

    //get Every one story
    private function _getEveryoneStory($response) {
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $table = Engine_Api::_()->getDbtable('stories', 'advancedactivity');
        $paginator = $table->getEveryoneStory($viewer);
        $paginator->setItemCountPerPage(50);
        $paginator->setCurrentPageNumber(1);
        foreach ($paginator as $story) {
            if ($story->owner_id == $viewer_id) {
                continue;
            }
            $browseStory = $story->toArray();
            $browseStory["total_stories"] = Engine_Api::_()->getDbtable('stories', 'advancedactivity')->getTotalStoryCount($story->owner_id);
            $browseStory["owner_title"] = $story->getOwner()->getTitle();

            $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($story);
            $browseStory = array_merge($browseStory, $getContentImages);

            $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($story, true);
            $browseStory = array_merge($browseStory, $getContentImages);
            if ($story->file_id) {
                $browseStory['videoUrl'] = Engine_Api::_()->getApi('Siteapi_Core', 'advancedactivity')->getVideoUrl($story);
            } else {
                $browseStory['videoUrl'] = '';
            }

            $response[] = $browseStory;
        }

        return $response;
    }

}
?>

