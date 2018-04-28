UPDATE `engine4_activity_notificationtypes` SET
`body`='{item:$subject} has {item:$postGuid:posted} on a {item:$object:forum topic} you created.'
WHERE `type`= 'siteforum_topic_response';


UPDATE `engine4_activity_notificationtypes` SET
`body`= '{item:$subject} has {item:$postGuid:posted} on a {item:$object:forum topic} posted on.'
WHERE `type`= 'siteforum_topic_reply';
