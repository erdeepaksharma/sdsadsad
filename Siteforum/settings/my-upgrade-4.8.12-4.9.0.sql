--
-- Updating data for table `engine4_core_menuitems`
--
UPDATE `engine4_core_menuitems`
SET `params` = '{"route":"siteforum_general","icon":"fa-comments"}'
WHERE `name` = 'core_main_siteforum';

--
-- Change the Commentable & Shareable values
--
UPDATE engine4_activity_actiontypes SET commentable=3,shareable=3 WHERE (type='comment_forum_post') and module='siteforum';

