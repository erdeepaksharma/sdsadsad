<?php
/**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: faq_help.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>
<script type="text/javascript">
    function faq_show(id) {
        if ($(id).style.display == 'block') {
            $(id).style.display = 'none';
        } else {
            $(id).style.display = 'block';
        }
    }
</script>
<div class="admin_seaocore_files_wrapper">
    <ul class="admin_seaocore_files seaocore_faq">	

        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_1');"><?php echo $this->translate(" I have placed Advanced Activity Feeds widget on Content Profile / View Pages and enabled Welcome, Facebook and Twitter tabs there, but only the site activity feeds are getting displayed and no tabs are coming. What could be the reason ?"); ?></a>
            <div class='faq' style='display: none;' id='faq_1'>
                <?php echo $this->translate(" The Welcome, Facebook and Twitter tabs will not be shown on the content profile / view pages even if they are enabled for the widget location. On these pages, only the feeds for respective content profile will be shows."); ?> 
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_2');"><?php echo $this->translate(" I have placed all the widgets in the Welcome tab page but Welcome tab is not being shown in the Advanced Activity Feeds widget. What might be the reason ?"); ?></a>
            <div class='faq' style='display: none;' id='faq_2'>
                <?php echo $this->translate(' The Welcome tab will not be shown in the Advanced Activity Feeds widget for a user if none of the conditions configured by you for the blocks in it are being satisfied. You may edit these conditions from the "Welcome Settings" section of this plugin.'); ?>
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_3');"><?php echo $this->translate(" The CSS of this plugin is not coming on my site. What should I do ?"); ?></a>
            <div class='faq' style='display: none;' id='faq_3'>
                <?php echo $this->translate(" Please enable the 'Development Mode' system mode for your site from the Admin homepage and then check the page which was not coming fine. It should now seem fine. Now you can again change the system mode to 'Production Mode'."); ?>
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_4');"><?php echo $this->translate(" I am performing lots of actions on my site, but the activity feeds of those actions are not shown in the 'all updates' section of the Advanced Activity Feeds. What might be the reason?"); ?></a>
            <div class='faq' style='display: none;' id='faq_4'>
                <?php echo $this->translate(" To show activity feeds of all the actions performed by you, please go to the 'Activity Feeds Settings' section of this plugin and find the field 'Item Limit Per User'. Now, enter the value for number of feeds per user you want to be displayed in the 'all updates' section. (Note : To have a nice mix of feeds from various users on your site, it is recommended to put a value less than 10.)"); ?>
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_5');"><?php echo $this->translate(" While updating status on my site, I selected the option to publish the status updates on twitter also, but my updates are not shown in my twitter timeline. Why is it happening?"); ?></a>
            <div class='faq' style='display: none;' id='faq_5'>
                <?php echo $this->translate(" This is happening because you might have not given the 'Read and Write' permission while creating your application on twitter. To give the permission now, please go to <a href='https://dev.twitter.com/apps' target='_blank'> 'https://dev.twitter.com/apps/' </a> and select your application. Now, search for the field 'Application Type' in the settings section of your application. Selecting 'Read and write' value for this field will enable you to publish your status updates on twitter from your site."); ?>
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_6');"><?php echo $this->translate(" I have selected the 'feed content' privacy on my site to 'friends only' but some of my feeds are still shown to the members who are not my friends when they visit my profile page. What might be the reason?"); ?></a>
            <div class='faq' style='display: none;' id='faq_6'>
                <?php echo $this->translate(" This is happening because while updating the status you might have chosen the privacy to 'Everyone' because of which feeds posted with privacy 'Everyone' will be visible to all the users when they visit your profile."); ?>
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_7');"><?php echo $this->translate(' I want to enable / disable the "Scroll to Top" button for the Advanced Activity Feeds widget. What should I do?'); ?></a>
            <div class='faq' style='display: none;' id='faq_7'>
                <?php echo $this->translate(" To do so, please go to the Layout Editor and click on the 'edit' link of the 'Advanced Activity Feeds' widget for the location where you want to enable / disable the 'Scroll to Top' button. Now, from the settings form popup of this widget, enable / disable the 'Scroll to Top Button' setting as per your requirement."); ?>
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_8');"><?php echo $this->translate(' I have enabled "Welcome" tab in the "Advanced Activity Feeds" widget placed on the Member Home Page on my site, but this widget is not displayed when I view my site in mobile. Why is this happening?'); ?></a>
            <div class='faq' style='display: none;' id='faq_8'>
                <?php echo $this->translate(" This is happening so because welcome tab will not be displayed when your site is viewed in mobile."); ?>
            </div>
        </li>
        <li>
            <a onClick="faq_show('faq_9');">
                I have created some custom lists for filtering activity feeds on my Wall, but I can not edit them. How can edit them?
            </a>
            <div class='faq' style='display: none;' id='faq_9'>
                You can edit custom lists by clicking on 'Pencil' icon placed in front of listing. But if your custom lists in not coming in the "More" tab, then the option to edit the custom list will not be shown.
                <br>
                To place the custom list in the More tab, you can set number of default items to be shown in the wall by using the "Default Visible Items" setting available in the "Manage Lists" >> "General" section of this plugin such that custom lists created on your site come in the "More" tab.
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_10');"><?php echo $this->translate("Facebook Feeds are not getting display on my website in Facebook Tab. What might be the reason?");?></a>
            <div class='faq' style='display: none;' id='faq_10'>
                <?php echo $this->translate("It is happening so because, Facebook has restricted the feature of displaying Facebook Feeds on other social sites. Please <a href='http://www.socialengineaddons.com/page/facebook-application-submission' target='_blank' >click here</a> to read more details.");?>
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_11');"><?php echo $this->translate("Can I set the time duration limit for the pinned posts which will be available to my site users?");?></a>
            <div class='faq' style='display: none;' id='faq_11'>
                <?php echo $this->translate(" Yes, you can set the time duration limit for the pinned posts which will be available for your site users. To do so, follow below steps: <br /><br />
                1. Go to ‘Global Settings’ → ‘General Settings’ section available in the admin panel.<br />
                2. Set the time duration limit in ‘Max Allowed Days for Pinned Post’.<br />
                3. Your site users will be able to pin their post maximum for the above set days.<br />
                ");?>
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_12');"><?php echo $this->translate("Can I change the sequence of options available in ‘Status Box Attachment Menus’?");?></a>
            <div class='faq' style='display: none;' id='faq_12'>
                <?php echo $this->translate(" Yes, you can change the sequence of options available in ‘Status Box Attachment Menus’. To do so, follow below steps:<br /><br />
                1. Go to ‘Global Settings’ → ‘Status Update Settings’ → ‘Status Box Attachment Menus’ section available in the admin panel.<br />
                2. Drag the attachment menu whose sequence you want to change.<br />
                3. Click on ‘Save Changes’ to save the new sequence of attachment menus.<br />");?>
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_13');"><?php echo $this->translate("What are the various ways to style the status updates?");?></a>
            <div class='faq' style='display: none;' id='faq_13'>
                <?php echo $this->translate(" There are two ways to style your status updates:<br /><br />
                Font Settings:<br />
                1. Go to ‘Global Settings’ → ‘Font Settings’ section available in the admin panel.<br />
                2. Set the character length and font size for the status update.<br />
                3. Character length defined here will decide that which status updates will be visible in different font size.<br />
                4. Example: If you have set the character length 30 and font size as 20. Then, the status updates consisting of characters equal or less than 30 will appear in font size 20. This will help short status updates to stand out from the rest updates.<br /><br />
                Word Styling:<br />
                1. Go to ‘Global Settings’ → ‘Word Styling’ section available in the admin panel.<br />
                2. Add the word which you want to highlight in the status updates.<br />
                3. You can set a different color for the word and its background which will make that word stand out in the various posts.<br />
                4. Example: If you want to highlight a word ‘Free’, then add it using above steps. Whenever someone uses ‘Free’ word in their status update, it will be shown in the color and background which you have used to highlight it. <br />
                ");?>
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_14');"><?php echo $this->translate("Is it possible to show user’s name and user’s profile photo with the greeting?");?></a>
            <div class='faq' style='display: none;' id='faq_14'>
                <?php echo $this->translate(" Yes, it is possible to show user’s name and user’s profile photo with the greeting. <br /><br />
                1. Add ‘[USER_NAME]’ to show user’s name with the greeting.<br />
                2. Add ‘[USER_PHOTO]’ to show user’s profile photo with the greeting.<br />");?>
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_16');"><?php echo $this->translate("Can I set the time duration limit for the pinned posts which will be available to my site users?");?></a>
            <div class='faq' style='display: none;' id='faq_16'>
                <?php echo $this->translate(" Yes, you can set the time duration limit for the pinned posts which will be available for your site users. To do so, follow below steps: <br /><br />
                                    1. Go to ‘Global Settings’ → ‘General Settings’ section available in the admin panel.<br />
                                    2. Set the time duration limit in ‘Max Allowed Days for Pinned Post’.<br />
                                    3. Your site users will be able to pin their post maximum for the above set days.");?>
            </div>
        </li>
         <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_17');"><?php echo $this->translate("My site user is not able to pin a post on Member’s Home page, although I have enabled this feature for his member level. What might be the reason behind it?");?></a>
            <div class='faq' style='display: none;' id='faq_17'>
                <?php echo $this->translate("Pinned post feature does not work for Member’s Home page. This feature is only for content related feeds generated on the content’s profile page. <br />
                                    Example: If a user want to pin a post from posted on a Group Profile page, then he can do so. Whenever the user will visit that particular Group Profile page, he can see his pinned post on top (for a particular time duration).");?>
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_18');"><?php echo $this->translate("How ‘Target Post’ feature works?");?></a>
            <div class='faq' style='display: none;' id='faq_18'>
                <?php echo $this->translate("‘Target Post’ feature provides your users an opportunity to reach out their post to the right audience based on gender and age. 
                    Follow below steps to understand the functionality of ‘Target Post’ feature:<br /><br />

                    1. Click on ‘Target your Post’ icon from the bottom of status box. <br />
                    2. Choose the gender for your post to whom you want to showcase your post.<br />
                    3. Set the range for age to target particular age group. ");?>
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_19');"><?php echo $this->translate("Can I post announcements above / below status box, to get attention of my site users whenever they visits the site?");?></a>
            <div class='faq' style='display: none;' id='faq_19'>
                <?php echo $this->translate("Yes, you can post announcements above / below status box. To do so, follow below steps:<br /><br />

                    1. Go to ‘Manage Greetings / Announcements’ section available in the admin panel of this plugin.<br />
                    2. Click on ‘+ Create New Greeting / Announcement’.<br />
                    3. Create announcement from TINYMCE editor.<br />
                    4. Set the time duration for the announcement.<br />
                    5. Enable the announcement and click on ‘Create’ button.");?>
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_20');"><?php echo $this->translate("From where I can set the count of background images to be shown to my site users? Is it possible to set the character length for the text being displayed on these backgrounds?");?></a>
            <div class='faq' style='display: none;' id='faq_20'>
                <?php echo $this->translate("Follow below steps to set the count of backgrounds and the character length for the text displaying on these backgrounds: <br /><br />

                    1. Go to ‘Global Settings’ → ‘Feed Decoration Settings’ from the admin section of this plugin.<br />
                    2. From ‘Background Images Limit’, set the count of backgrounds to be available in the status box.<br />
                    3. From ‘Character Limit for Text on Background Image’, set the character length for the text being displayed on the backgrounds.");?>
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_21');"><?php echo $this->translate("What are the various ways to showcase a normal post as a highlighted post?");?></a>
            <div class='faq' style='display: none;' id='faq_21'>
                <?php echo $this->translate("There are three ways to showcase a normal post as a highlighted post: <br /><br />

                    1. Font Size: Set the font size between 24px to 42px to increase the visibility of the post.<br />
                    2. Font Color: Highlight the post in different color from a wide range of colors.<br />
                    3. Font Style: Set the font style for text as per your choice from - normal, italic and oblique. <br /><br />

                    [Note: You have to set ‘Character Length’ first for posts, under this defined length above settings will work.]
                    ");?>
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_22');"><?php echo $this->translate("Can I highlight certain words whenever they are used in the status posts on my website?");?></a>
            <div class='faq' style='display: none;' id='faq_22'>
                <?php echo $this->translate("Yes you can highlight certain words whenever they are used in the status posts on my website. To do so, follow below steps: <br /><br />

                    1. Go to ‘Global Settings’ to ‘Word Styling’ from the admin section of this plugin.<br />
                    2. Click on ‘Add New Word’ link.<br />
                    3. Enter the word which you want to highlight.<br />
                    4. Set the color for the word and its background.<br />
                    5. Set the font style for the word from: normal, italic and oblique.");?>
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_24');"><?php echo $this->translate("What are the various layouts for status box and from where I can change the current layout?");?></a>
            <div class='faq' style='display: none;' id='faq_24'>
                <?php echo $this->translate("There are three layout for the status box:<br />
                    1. All Attachments Link Icon<br />
                    2. All Attachments Links in Buttons with Popup<br />
                    3. All Attachments Links on Top of Box<br /><br />

                    To change the current layout of the status box, go to ‘Layout Editor’. Edit ‘Advanced Activity Feed’ widget settings to choose your desired layout.");?>
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_25');"><?php echo $this->translate("How many ways are there for user’s photo displaying in feed? From where I can change the current position of user’s photo?");?></a>
            <div class='faq' style='display: none;' id='faq_25'>
                <?php echo $this->translate("There are 7 ways to display user’s photo in feed. To change the current position / way, please go to ‘Layout Editor’. Edit ‘Advanced Activity Feed’ widget settings to choose your desired option.");?>
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_26');"><?php echo $this->translate("Can I change the count of columns in Pinboard View of activity feeds? \"Or\" How can I decide the count of columns in Pinboard View of activity feeds??");?></a>
            <div class='faq' style='display: none;' id='faq_26'>
                <?php echo $this->translate("Yes, you can change the count of columns in Pinboard View of activity feeds. To do so please go to the ‘Advanced Activity Feeds’ widget settings on the widgetized page it is placed.<br />
                    You can decide the count of columns in Pinboard View depending upon the column width where you have placed ‘Advanced Activity Feeds’. For example, if you are using 3 column layout then you can set the count of Pinboard View columns from 1 to 3. If you are using single column then you can set the count of Pinboard View columns from 1 to 5.");?>
            </div>
        </li>
    </ul>
</div>