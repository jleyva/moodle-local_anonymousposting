<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Enable or disable anonymous posting in a forum.
 *
 * @package    local
 * @subpackage anonymousposting
 * @copyright  2011 Juan Leyva <juanleyvadelgado@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$context = context_module::instance($cm->id);

$PAGE->set_url('/local/anonymousposting/layout.php', array('id'=>$id));

require_login($course, false, $cm); // needed to setup proper $COURSE

// Plugin disabled
if (! $anonymousposting = get_config('local_anonymousposting', 'enabled')) {
    die;
}

$status = get_config('local_anonymousposting', 'forum_'.$id);
$anonymousonly = get_config('local_anonymousposting', 'anonymousonly');

$changeuserurl = $CFG->wwwroot.'/local/anonymousposting/changeuser.php?id='.$id;
$strnewpost = get_string('newpost', 'local_anonymousposting');
$strreply = get_string('reply', 'local_anonymousposting');
$stranonymousposting = get_string('anonymousposting', 'local_anonymousposting');
$strenable = get_string('enable', 'local_anonymousposting');
$strdisable = get_string('disable', 'local_anonymousposting');
$strenabled = get_string('enabled', 'local_anonymousposting');
$strdisabled = get_string('disabled', 'local_anonymousposting');
$strforumreply = get_string('reply', 'mod_forum');

$jsedit = "";
if (has_capability('moodle/course:manageactivities', $context)) {

    $params = array("id" => $id);

    if ($status) {
        $params['enabled'] = 0;
        $mode = $strenabled;
        $action = $strdisable;
    }
    else{
        $params['enabled'] = 1;
        $mode = $strdisabled;
        $action = $strenable;
    }

    $actionurl = new moodle_url('/local/anonymousposting/forum.php', $params);

    $newnode = "<li class=\"tree_setting depth_2 item_with_icon \">"
            . "<p class=\"tree_item hasicon tree_item leaf\">"
            . "<span tabindex=\"0\">"
            . $OUTPUT->pix_icon(
                'i/navigationitem',
                '',
                'moodle',
                array('class' => 'tree-icon'))
            . "$stranonymousposting</span></p>";
    $newnode .= "<ul id=\"yui_3_4_1_1_1326125892104_50\">";
    $newnode .= "<li class=\"type_setting collapsed item_with_icon\"><p class=\"tree_item leaf activesetting\"><span tabindex=\"0\">".$OUTPUT->pix_icon(
            'i/navigationitem',
            '',
            'moodle',
            array('class' => 'tree-icon', 'title' => $strenable)
        ).$mode."</span></p></li>";
    $newnode .= "<li class=\"type_setting collapsed item_with_icon\"><p class=\"tree_item leaf\"><a title=\"$action\" href=\"$actionurl\">".$OUTPUT->pix_icon(
            'i/navigationitem',
            '',
            'moodle',
            array('class' => 'tree-icon', 'title' => $strdisable)
        ).$action."</a></p></li>";
    $newnode .= "</ul></li>";
    $newnode = preg_replace( "/\r|\n/", "", $newnode );

    $jsedit = "
    var stop = false;
    var settingsnav = Y.one('#settingsnav');
    if (settingsnav) {
        var settings = settingsnav.one('.block_tree').all('ul');
        settings.each(function (setting) {
            var lists = setting.all('li');
            lists.each(function (list) {
                if (!stop && list.getContent().indexOf('subscribers.php?id=".$cm->instance."') ) {
                    setting.append('".$newnode."');
                    stop = true;
                    return;
                }
            });
            if(stop){
                return;
            }
        });
    }
";
}

$jsuser = "";
if ($status and !isset($SESSION->aucontext)) {

    $jsuser .= "
    var newpost = Y.one('#newdiscussionform');

    if (newpost) {
        newpost.append('&nbsp;&nbsp;<input type=\"button\" id=\"apnewdiscussion\" value=\"".$strnewpost."\">');
        Y.one('#apnewdiscussion').on('click', function(e) {
            location.href = '".$changeuserurl."&action=newpost';
        });
    }

    var replyposts = Y.one('#region-main').all('.commands');
    replyposts.each(function (reply) {
        var content = reply.getContent();
        var tok = content.indexOf('reply=');
        if (tok) {
            content = content.substring(tok);
            var replyId = content.substring(6, content.indexOf('\"'));
            reply.append('&nbsp;|&nbsp;<a href =\"".$changeuserurl."&action=reply&replyid='+replyId+'\">".$strreply."</a>');
        }
    });
    ";

    // If we only allow anonymous posting, then need to remove the post buttons.
    if ($anonymousonly) {
        $jsuser .= "
            if (newpost) {
                Y.one('#newdiscussionform input[type=\"submit\"]').remove();
            }
            replyposts.each(function (reply) {
                var content = reply.getContent();
                reply.setContent(content.replace('$strforumreply</a> |','</a>'));
            });
        ";
    }

}

// Show link for return to the actual user
if ($status and isset($SESSION->aucontext)) {
    $strrelogin = get_string('relogin', 'local_anonymousposting');
    $url = new moodle_url('/local/anonymousposting/relogin.php', array('sesskey' => sesskey()));

    $newnode = "<li class=\"type_setting collapsed item_with_icon\"><p class=\"tree_item leaf\"><a title=\"$strrelogin\" href=\"$url\">$strrelogin</a></p></li>";
    $newnode = preg_replace( "/\r|\n/", "", $newnode );

    $jsuser .= "
    var stop = false;
    var settingsnav = Y.one('#settingsnav');
    if (settingsnav) {
        var settings = settingsnav.one('.block_tree').all('ul');
        settings.each(function (setting) {
            var lists = setting.all('li');
            lists.each(function (list) {
                if (!stop && list.getContent().indexOf('subscribers.php?id=".$cm->instance."') ) {
                    setting.append('".$newnode."');
                    stop = true;
                    return;
                }
            });
            if(stop){
                return;
            }
        });
    }
    ";
}

$js = "

YUI().use('node', function (Y) {

".$jsuser."

".$jsedit."

});

";


$lifetime  = 600;                                   // Seconds to cache this stylesheet


header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
header('Expires: ' . gmdate("D, d M Y H:i:s", time() - $lifetime) . ' GMT');
header('Cache-control: max_age = '. $lifetime);
header('Pragma: ');
header('Content-type: text/javascript; charset=utf-8');  // Correct MIME type

echo $js;
die;
