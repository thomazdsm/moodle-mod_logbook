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
 * Diário de Bordo
 *
 * @package mod_logbook
 * @copyright  2024 Thomaz Machado {@link https://xfera.tech}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');

global $DB;
global $OUTPUT;
global $PAGE;
global $USER;

$id = required_param('id', PARAM_INT);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'logbook');
$logbook = $DB->get_record('logbook', ['id' => $cm->instance], '*', MUST_EXIST);

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);

logbook_view($logbook, $course, $cm, $context);

$url = new moodle_url('/mod/logbook/view.php', ['id' => $id]);

$PAGE->set_url($url);
$PAGE->set_title(format_string($course->shortname) . ': ' .format_string($logbook->name));
$PAGE->set_heading(format_string($course->fullname));

$renderer = $PAGE->get_renderer('mod_logbook');

// Admin or Teacher User.
var_dump(has_capability('mod/logbook:viewadmin', $context));exit();

if (has_capability('mod/logbook:viewadmin', $context)) {
    require_capability('mod/logbook:viewadmin', $context);

    echo $OUTPUT->header();
    echo 'Is Admin or Teacher';
    echo $OUTPUT->footer();

    return;
}

// Render the activity information.
$completiondetails = \core_completion\cm_completion_details::get_instance($cm, $USER->id);
$activitydates = \core\activity_dates::get_dates_for_module($cm, $USER->id);

if ($_POST) {
    try {
        $data = $_POST;

        if (empty($_POST['userlog'])) {
            redirect($url, get_string('emptydata', 'mod_logbook'), null, \core\output\notification::NOTIFY_WARNING);
        }

        logbook_save_answers($logbook, $data, $course, $context, $cm);

        redirect($url, get_string('alertsuccess', 'mod_logbook'), null, \core\output\notification::NOTIFY_SUCCESS);

    } catch (\Exception $e) {
        redirect($url, $e->getMessage(), null, \core\output\notification::NOTIFY_ERROR);
    }
}

$contentrenderable = new \mod_logbook\output\viewsubmission($logbook, $context);

echo $OUTPUT->header();
echo $OUTPUT->activity_information($cm, $completiondetails, $activitydates);
echo $renderer->render($contentrenderable);
echo $OUTPUT->footer();
