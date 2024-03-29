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
 *
 *
 * @package    format_sectionim
 * @copyright  2012 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/completionlib.php');

$context = context_course::instance($course->id);

if (($marker >=0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

// make sure section 0 is created
course_create_sections_if_missing($course, 0);

$renderer = $PAGE->get_renderer('format_sectionim');
//TODO refactor sub section function
if (($deletesection = optional_param('deletesection', 0, PARAM_INT)) && confirm_sesskey()) {
    $renderer->confirm_delete_section($course, $displaysection, $deletesection);
} elseif (format_sectionim_check_params()) {
  $renderer->display_section($course, $displaysection, $displaysection, 1, false, 1);
} else {
  $renderer->display_section($course, $displaysection, $displaysection, 1, true); //pinned sections first
  $renderer->display_section($course, $displaysection, $displaysection);
}

// SG - Update custom section numbers in DB - only in editing mode
if ($PAGE->user_is_editing()) {
  $courseformat = course_get_format($course);
  foreach($renderer->sectionscustomenumeration as $secnum => $customnum) {
    $secdata = $courseformat->get_section($secnum);
    if ($secdata->customnumber != $customnum) {
      $courseformat->update_section_format_options(array('customnumber' => $customnum, 'id' => $secdata->id));
    }
  }
}

// Include course format js module
$PAGE->requires->js('/course/format/sectionim/format.js');
$PAGE->requires->string_for_js('confirmdelete', 'format_sectionim');
$PAGE->requires->js_init_call('M.course.format.init_flexsections');

$PAGE->requires->js_call_amd('format_sectionim/toggleSection', 'init');
