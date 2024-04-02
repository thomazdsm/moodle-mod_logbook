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

defined('MOODLE_INTERNAL') || die;

/**
 * Add logbook instance.
 *
 * @param stdClass $data
 * @param stdClass $mform
 * @return int new logbook instance id
 */
function logbook_add_instance($logbook, $mform) {
    global $DB;

    $logbook->timecreated = time();
    $logbook->timemodified = time();

    $id = $DB->insert_record('logbook', $logbook);

    $completiontimeexpected = !empty($logbook->completionexpected) ? $logbook->completionexpected : null;
    \core_completion\api::update_completion_date_event($logbook->coursemodule,
        'logbook',
        $id,
        $completiontimeexpected);

    return $id;
}

function logbook_update_instance($logbook, $mform) {
    global $DB;

    $logbook->timemodified = time();
    $logbook->id = $logbook->instance;

    $completiontimeexpected = !empty($logbook->completionexpected) ? $logbook->completionexpected : null;
    \core_completion\api::update_completion_date_event($logbook->coursemodule,
        'logbook',
        $logbook->id,
        $completiontimeexpected);

    return $DB->update_record('logbook', $logbook);
}

function logbook_delete_instance($id) {
    global $DB;

    if (!$logbook = $DB->get_record('logbook', array('id' => $id))) {
        return false;
    }

    $cm = get_coursemodule_from_instance('logbook', $id);
    \core_completion\api::update_completion_date_event($cm->id,
        'logbook',
        $id,
        null);

    $DB->delete_records('logbook', array('id' => $logbook->id));

    return true;
}

function logbook_has_submission($logbookid, $userid) {
    global $DB;

    return $DB->record_exists('logbook_answers',
        ['logbookid' => $logbookid, 'userid' => $userid]);
}

/**
 * Save the answer for the given logbook
 *
 * @param  stdClass $logbook   a logbook object
 * @param  array $answersrawdata the answers to be saved
 * @param  stdClass $course   a course object (required for trigger the submitted event)
 * @param  stdClass $context  a context object (required for trigger the submitted event)
 * @since Moodle 3.0
 */
function logbook_save_answers($logbook, $answersrawdata, $course, $context, $cm) {
    global $DB, $USER;

    $log = new \stdClass();
    $log->logbookid = $logbook->id;
    $log->courseid = $course->id;
    $log->userid = $USER->id;
    $log->userlog = $answersrawdata['userlog'];
    $log->timecreated = time();
    $log->timemodified = time();

    $DB->insert_record("logbook_answers", $log);

    // Update completion state.
    $completion = new completion_info($course);
    if (isloggedin() && !isguestuser() && $logbook->completionsubmit) {
        $completion->update_state($cm, COMPLETION_COMPLETE);
    }

//    $params = array(
//        'context' => $context,
//        'courseid' => $course->id,
//        'other' => array('logbookid' => $logbook->id)
//    );
//    $event = \mod_logbook\event\response_submitted::create($params);
//    $event->trigger();
}


/**
 * Returns HTML to display course category name.
 *
 * @return string
 *
 * @throws \moodle_exception
 */
function get_category($category): string {
    $cat = core_course_category::get($category, IGNORE_MISSING);

    if (!$cat) {
        return '';
    }

    return $cat->get_formatted_name();
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $logbook     logbook object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @param  string $viewed       which page viewed
 * @since Moodle 3.0
 */
function logbook_view($logbook, $course, $cm, $context) {
    $event = \mod_logbook\event\course_module_viewed::create(array(
        'context' => $context,
        'objectid' => $logbook->id
    ));
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('logbook', $logbook);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}