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
 * Global settings for the plugin.
 *
 * @package    local
 * @subpackage anonymousposting
 * @copyright  2011 Juan Leyva <juanleyvadelgado@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot.'/local/anonymousposting/index_form.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

$pageparams = array();
admin_externalpage_setup('local_anonymousposting', '', $pageparams);

$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($SITE->fullname . ': ' . get_string('pluginname', 'local_anonymousposting'));

$returnurl = new moodle_url('/local/anonymousposting/');

$mform = new local_anonymousposting_form($returnurl);
$mform->set_data(get_config('local_anonymousposting'));

if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $mform->get_data()) {
    if (isset($data->enabled)) {
        set_config('enabled' , 1, 'local_anonymousposting');
    } else {
        set_config('enabled' , 0, 'local_anonymousposting');
    }
    set_config('defaultcourserole' , $data->defaultcourserole, 'local_anonymousposting');
    set_config('defaultactivityrole' , $data->defaultactivityrole, 'local_anonymousposting');
    set_config('anonymousonly' , $data->anonymousonly, 'local_anonymousposting');
}


echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
