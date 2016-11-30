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
 * Recover the actual user session.
 *
 * @package    local
 * @subpackage anonymousposting
 * @copyright  2011 Juan Leyva <juanleyvadelgado@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');

$PAGE->set_url('/local/anonymousposting/relogin.php');

// context instanceid = course module id for this context (forum)
$cmid = $SESSION->aucontext->instanceid;
$cm = get_coursemodule_from_id('', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$context = context_module::instance($cm->id);

require_login($course, false, $cm); // needed to setup proper $COURSE

// Plugin disabled
if (! $anonymousposting = get_config('local_anonymousposting', 'enabled')) {
    print_error('Plugin disabled');
}

// Forum instance disabled
if (! $status = get_config('local_anonymousposting', 'forum_'.$cm->id)) {
    print_error('Instance disabled');
}

require_sesskey();
$urltogo = new moodle_url('/mod/forum/view.php', array('id' => $cmid));

$timenow = time();
// TODO - Some settings for this, 15 minutes
// This is to prevent inactive users
if ($timenow - $SESSION->autime > 900) {
    require_logout();

    $SESSION->wantsurl = $urltogo;
    redirect(get_login_url());
}

// Some security checks
if ($USER->auth == 'nologin') {

    // switch to fresh new $SESSION
    $_SESSION['SESSION']     = new stdClass();

    /// Create the new $USER object with all details and reload needed capabilities
    $user = $_SESSION['AUREALUSER'];
    unset($_SESSION['AUREALUSER']);
    unset($SESSION->aucontext);

    $user = get_complete_user_data('id', $user->id);
    $user->loginascontext = $context;
    \core\session\manager::set_user($user);

    $strloginas    = get_string('loginas');
    $strloggedinas = get_string('loggedinas', '', fullname($user));

    $PAGE->set_title($strloggedinas);
    $PAGE->set_heading($course->fullname);
    $PAGE->navbar->add($strloggedinas);
    notice($strloggedinas, $urltogo);

}
