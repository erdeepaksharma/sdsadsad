--
-- Change the Commentable & Shareable values
--
UPDATE engine4_activity_actiontypes SET commentable=3,shareable=3 WHERE (type='comment_sitereview_listing' or type = 'comment_sitereview_photo' or type = 'comment_sitereview_review' or type = 'comment_sitereview_video') and module='sitereview';


