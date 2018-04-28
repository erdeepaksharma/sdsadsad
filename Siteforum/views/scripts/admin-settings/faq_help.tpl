<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: faq_help 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>

<script type="text/javascript">
    function faq_show(id) {
        if ($(id)) {
            if ($(id).style.display == 'block') {
                $(id).style.display = 'none';
            } else {
                $(id).style.display = 'block';
            }
        }
    }

<?php if ($this->faq_id): ?>
        window.addEvent('domready', function () {
            faq_show('<?php echo $this->faq_id; ?>');
        });
<?php endif; ?>
</script>

<div class = "admin_seaocore_files_wrapper">
    <ul class = "admin_seaocore_files seaocore_faq">
        <?php $i = 0;?>
        
        <li>
            <a href = "javascript:void(0);" onClick = "faq_show('faq_<?php echo ++$i;?>');"><?php echo "I do not want to show the categories and subcategories which do not have any forums in it. Is it possible to do so?"; ?></a>
            <div class = 'faq' style = 'display: none;' id = 'faq_<?php echo $i++;?>'>
                <?php echo "Yes, you can easily do so by following the below steps:";?><br/>
                <?php echo "1. Please go to the Layout Editor > ''Widgetized Page' for which you want to do so."?><br/>
                <?php echo "2. Now click on the 'edit' link of the widget: 'Forum Categories, Subcategories and Forums'."?><br/>
                <?php echo "3. Set 'No' for the 'Do you want all the categories and subcategories to be shown to the users even if they have 0 forums in them?'."?><br/>
            </div>
        </li>         
        
        <li>
            <a href = "javascript:void(0);" onClick = "faq_show('faq_<?php echo ++$i;?>');"><?php echo "I want only categories and subcategories to be shown in 'Quick Navigation' menu, but I am unable to do so from its widget setting. What might be the reason?"; ?></a>
            <div class = 'faq' style = 'display: none;' id = 'faq_<?php echo $i++;?>'>
                <?php echo "It might be that you are configuring the widget on the wrong page i.e you can show different quick navigation menus for different page. So, it might be possible that, for eg: you want to configure the Forum's View Page and you are configuring the widget on Forums Home Page.";?>            </div>
        </li>            
        
        <li>
            <a href = "javascript:void(0);" onClick = "faq_show('faq_<?php echo ++$i;?>');"><?php echo "What is Reputation? How it works?"; ?></a>
            <div class = 'faq' style = 'display: none;' id = 'faq_<?php echo $i++;?>'>
                <?php echo "Reputation is the honour given to the post creator for his / her valuable post. When a reader finds the user's post informative or helpful he / she can increase his / her reputation in regards. Also, you can increase / decrease the creator's reputation for his / her each posts individually."; ?>
            </div>
        </li>          
        
        <li>
            <a href = "javascript:void(0);" onClick = "faq_show('faq_<?php echo ++$i;?>');"><?php echo "To whom we can give thanks?"; ?></a>
            <div class = 'faq' style = 'display: none;' id = 'faq_<?php echo $i++;?>'>
                <?php echo "Reader can give thanks to those post's creators whose posts were helpful to him and solved his / her query. It can be given for each post individually i.e one post creator can be thanked multiple times for his various posts."; ?>
            </div>
        </li>
        
        <li>
            <a href = "javascript:void(0);" onClick = "faq_show('faq_<?php echo ++$i;?>');"><?php echo "I have liked many posts till now, where can I find those posts?"; ?></a>
            <div class = 'faq' style = 'display: none;' id = 'faq_<?php echo $i++;?>'>
                <?php echo "Please go to the 'User Dashboard' link available in the header at the right side, here you have various tabs like: Topics I liked, My Topics, My Posts, My Subscriptions etc, you can browse them according to your requirement."; ?>
            </div>
        </li>
        
        <li>
            <a href = "javascript:void(0);" onClick = "faq_show('faq_<?php echo ++$i;?>');"><?php echo "How can I enable 'User Dashboard' Link?"; ?></a>
            <div class = 'faq' style = 'display: none;' id = 'faq_<?php echo $i++;?>'>
                <?php echo "Please follow the below steps to do so:"; ?><br/>
                <?php echo "1. Please go to the desired widgetized page from the 'Layout Manager' where you want to enable the link.";?><br/>
                <?php echo "2. Now, either place 'Quick Navigation' widget or ‘Breadcrumb Navigation’ widget and configure it from the edit link.";?><br/>
                <?php echo "3. Now, for ‘Breadcrumb Navigation’ widget: set 'Yes' for the setting 'Do you want to show “User Dashboard” link?'.";?><br/>
                <div class="admin_table_centered"> <?php echo "&"?><br/></div>
                <?php echo "For Quick Navigation widget: choose the desired options from setting ‘Choose from below options which you want to enable in this widget’.";?><br/>
            </div>
        </li>
        
        <li>
            <a href = "javascript:void(0);" onClick = "faq_show('faq_<?php echo ++$i;?>');"><?php echo "I want to share my favorite forum topics' URL on Facebook, is there any way to do that?"; ?></a>
            <div class = 'faq' style = 'display: none;' id = 'faq_<?php echo $i++;?>'>
                <?php echo "Yes, you can easily do so by going on to the Admin Panel >> Layout Manager >> Advanced Forums: Topic View Page (Widgetize Page) >> Topic View (Widget). Here, open the widget using 'edit' link and configure the social share options as per your requirement. You will now see a share option on Topic View Page, using which you will be able to share your desired topics on social websites."; ?>
            </div>
        </li>
        
        <li>
            <a href = "javascript:void(0);" onClick = "faq_show('faq_<?php echo ++$i;?>');"><?php echo "What is sticky functionality? I am not getting the option of making a topic sticky."; ?></a>
            <div class = 'faq' style = 'display: none;' id = 'faq_<?php echo $i++;?>'>
                <?php echo "By making a topic Sticky,  user will have those topics at the top, amongst all the forum topics on 'Forum View Page'. [Note: Only moderator will be able to: “Make Sticky”  and “Remove Sticky”.]"; ?>
            </div>
        </li>
        
        <li>
            <a href = "javascript:void(0);" onClick = "faq_show('faq_<?php echo ++$i;?>');"><?php echo "Will user be able to edit / delete the post they created?"; ?></a>
            <div class = 'faq' style = 'display: none;' id = 'faq_<?php echo $i++;?>'>
                <?php echo "No, only the moderators can edit / delete the post which site members have created."; ?>
            </div>
        </li>
    </ul>
</div>