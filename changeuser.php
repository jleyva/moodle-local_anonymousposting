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
 * Login as the anonymous user
 *
 * @package    local
 * @subpackage anonymousposting
 * @copyright  2011 Juan Leyva <juanleyvadelgado@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');

$id = required_param('id', PARAM_INT);
$action = required_param('action', PARAM_ALPHA);
$replyid  = optional_param('replyid', 0, PARAM_INT);
$confirm  = optional_param('confirm', 0, PARAM_BOOL);

$cm = get_coursemodule_from_id('', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

$PAGE->set_url('/local/anonymousposting/changeuser.php', array('id' => $id, 'action' => $action, 'replyid' => $replyid));

require_login($course, false, $cm); // needed to setup proper $COURSE

$context = context_module::instance($cm->id);
$return = new moodle_url('/mod/forum/view.php', array('id' => $id));

$strchangeuser = get_string('strchangeuser', 'local_anonymousposting');
$PAGE->navbar->add($strchangeuser);
$PAGE->set_title($strchangeuser);
$PAGE->set_heading(format_string($course->fullname));

$anonymousposting = get_config('local_anonymousposting', 'enabled');
$status = get_config('local_anonymousposting', 'forum_'.$id);

if (! $anonymousposting or ! $status) {
    echo $OUTPUT->header();
    echo $OUTPUT->box(get_string('anonymouspostingdisabled', 'local_anonymousposting'));
    echo $OUTPUT->continue_button($return);
    echo $OUTPUT->footer();
    die;
}

if ($confirm and confirm_sesskey()) {

    // TODO, this have to be in a constant or setting
    $anonuser = 'localanonymousposting';
    // Check if the user exists
    $user = $DB->get_record('user', array('username' => $anonuser, 'auth' => 'nologin'));
    if (! $user) {
        $user = new stdClass();
        // clean_param , email username text
        $user->auth = 'nologin';
        $user->username = $anonuser;
        $user->password = md5(uniqid(rand(), 1));
        $user->firstname = get_string('userfirstname', 'local_anonymousposting');
        $user->lastname = get_string('userlastname', 'local_anonymousposting');
        $user->email = 'noreply@localhost';
        $user->city = $USER->city;
        $user->country = $USER->country;
        $user->timezone = $USER->timezone;
        $user->maildisplay = 0;
        $user->lang = $USER->lang;

        $user->id = $DB->insert_record('user', $user);
        // Reload full user
        $user = $DB->get_record('user', array('id' => $user->id));
    }


    // Enrol the user in the course
    $roleid = get_config('local_anonymousposting', 'defaultcourserole');

    $today = time();
    $today = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);
    $timeend = 0;

    if (enrol_is_enabled('manual')) {
        $manual = enrol_get_plugin('manual');
    } else {
        print_error("Missing manual enrol method");
    }

    if ($instances = enrol_get_instances($course->id, false)) {
        foreach ($instances as $instance) {
            if ($instance->enrol === 'manual') {
                if (! $roleid) {
                    $roleid = $instance->roleid;
                }
                $manual->enrol_user($instance, $user->id, $roleid, $today, $timeend);
                break;
            }
        }
    }

    // Enrol the user in the activity
    $roleid = get_config('local_anonymousposting', 'defaultactivityrole');
    if (! $roleid) {
        $roleid = $instance->roleid;
    }
    role_assign($roleid, $user->id, $context->id);

    // Login as the anonymous user

    // switch to fresh new $SESSION
    $_SESSION['SESSION']     = new stdClass();

    /// Create the new $USER object with all details and reload needed capabilities
    $_SESSION['AUREALUSER'] = $_SESSION['USER'];
    $user = get_complete_user_data('id', $user->id);
    $user->loginascontext = $context;
    \core\session\manager::set_user($user);

    $SESSION->aucontext = $context;
    $SESSION->autime = time();

    if ($replyid) {
        $params = array('reply' => $replyid);
    }
    else {
        $params = array('forum' => $cm->instance);
    }
    $urltogo = new moodle_url('/mod/forum/post.php', $params);
    redirect($urltogo);
}

echo $OUTPUT->header();

if (\core\session\manager::is_loggedinas()) {
    print_error('youcannouseloginas', 'local_anonymousposting');
} else {
    $msg = get_string('strchangeusermsg', 'local_anonymousposting');
    echo $OUTPUT->confirm($msg, new moodle_url('changeuser.php', array('id' => $id, 'action' => $action, 'replyid' => $replyid, 'confirm' => 1, 'sesskey' => sesskey())), $return);
}
echo $OUTPUT->footer();
