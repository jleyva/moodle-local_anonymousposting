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
 * Global settings form.
 *
 * @package    local
 * @subpackage anonymousposting
 * @copyright  2011 Juan Leyva <juanleyvadelgado@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/course/lib.php');

/// get url variables
class local_anonymousposting_form extends moodleform {

    // Define the form
    public function definition () {
        $mform =& $this->_form;
        
        $mform->addElement('checkbox', 'enabled', null, get_string('enabled', 'local_anonymousposting'));
        $mform->setDefault('enabled', 1);

        $context = context_course::instance(SITEID,MUST_EXIST);
        $assignableroles = get_assignable_roles($context);

        $mform->addElement('select', 'defaultcourserole', get_string('defaultcourserole', 'local_anonymousposting'), $assignableroles);
        $mform->setDefault('defaultcourserole', '5');

        $mform->addElement('select', 'defaultactivityrole', get_string('defaultactivityrole', 'local_anonymousposting'), $assignableroles);
        $mform->setDefault('defaultactivityrole', '5');

        $mform->addElement('checkbox', 'anonymousonly', get_string('anonymousonly', 'local_anonymousposting'),
                get_string('anonymousonlydesc', 'local_anonymousposting'));
        $mform->setDefault('anonymousonly', 0);

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        global $COURSE, $DB, $CFG;

        $errors = parent::validation($data, $files);

        return $errors;
    }

}
