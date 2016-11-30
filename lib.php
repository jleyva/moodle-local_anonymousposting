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
 * Plugin lib.
 * This script includes also some hacks for adding new options manipulating the DOM
 *
 * @package    local
 * @subpackage anonymousposting
 * @copyright  2011 Juan Leyva <juanleyvadelgado@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Limit navigation to the forum context for anonymous users
 * Add new nodes to the settings blocks using javascript (Yui3 Node)
 *
 * @global moodle_user $USER
 * @global moodle_page $PAGE
 * @param navigation_node $nav Current navigation object
 */
function local_anonymousposting_extend_navigation($nav) {
    global $USER, $PAGE, $SESSION;

    // Check if we need to manipulate the DOM for adding links for managers and users
    if ($PAGE->context->contextlevel == CONTEXT_MODULE and get_config('local_anonymousposting', 'enabled')){
        if ($cm = get_coursemodule_from_id(false, $PAGE->context->instanceid, 0, false) and $cm->modname == 'forum') {
            // Hack for adding custom javascript
            $PAGE->requires->js(new moodle_url('/local/anonymousposting/layout.php', array('id' => $PAGE->context->instanceid)));
        }
    }

    if (isset($SESSION->aucontext)) {
        // Limit navigation to the forum context
        if ($PAGE->context->id != $SESSION->aucontext->id) {
            // context instanceid = course module id for this context (forum)
            redirect(new moodle_url('/mod/forum/view.php', array('id' => $SESSION->aucontext->instanceid)));
        }
    }
}