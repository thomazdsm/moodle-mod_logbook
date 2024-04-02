<?php

namespace mod_logbook\output;

use context_module;
use moodle_url;
use moodleform;
use renderable;
use stdClass;
use templatable;
use renderer_base;

class viewsubmission implements renderable, templatable {

    public $logbook;
    public $context;

    public function __construct($logbook, $context) {
        $this->logbook = $logbook;
        $this->context = $context;
    }

    /**
     * Export the data
     *
     * @param renderer_base $output
     *
     * @return array|\stdClass
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function export_for_template(renderer_base $output) {
        global $DB;
        global $USER;

        $sql = "SELECT * FROM {logbook_answers} WHERE logbookid = ".$this->logbook->id." AND userid = ".$USER->id;
        $records = $DB->get_records_sql($sql);

        $data = new stdClass();

        foreach ($records as $record) {
            $record->timecreated = date('d/m/Y', $record->timecreated);
            $data->data[] = $record;
        }

        $data->user = $USER->firstname ." ". $USER->lastname;
        $data->url = new moodle_url('/mod/logbook/view.php');
        $data->cmid = required_param('id', PARAM_INT);

        $data->label = get_string('label', 'logbook');
        $data->titleh4 = get_string('titleh4', 'logbook');
        $data->nodataspan = get_string('nodataspan', 'logbook');
        $data->author = get_string('author', 'logbook');
        $data->writtedin = get_string('writtedin', 'logbook');
        $data->module = get_string('module', 'logbook');
        $data->class = get_string('class', 'logbook');

        $course = get_course($this->logbook->course);
        $data->coursename = $course->fullname;
        $data->category = get_category($course->category);

        return $data;
    }
}
