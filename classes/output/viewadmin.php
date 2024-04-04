<?php

namespace mod_logbook\output;

use context_module;
use moodle_url;
use moodleform;
use renderable;
use stdClass;
use templatable;
use renderer_base;

class viewadmin implements renderable, templatable {

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
        $output = new stdClass();

        $records = get_enrolled_users($this->context);
        foreach ($records as $record) {
            $userdata = new stdClass();

            $userdata->userid = $record->id;
            $userdata->fullname = $record->firstname . " " . $record->lastname;
            $userdata->useranswers = $this->get_logbook_answers($record->id);

            $output->userdata[] = $userdata;
        }

        $output->label = get_string('label', 'logbook');
        $output->titleh4 = get_string('titleh4', 'logbook');
        $output->nodataspan = get_string('nodataspan', 'logbook');
        $output->author = get_string('author', 'logbook');
        $output->writtedin = get_string('writtedin', 'logbook');
        $output->module = get_string('module', 'logbook');
        $output->class = get_string('class', 'logbook');

        $course = get_course($this->logbook->course);
        $output->coursename = $course->fullname;
        $output->category = get_category($course->category);

        return $output;
    }

    protected function get_logbook_answers($userid) {
        global $DB;
        $query = "SELECT * FROM {logbook_answers} WHERE userid = ".$userid;
        $records = $DB->get_records_sql($query);

        $output = array();
        foreach ($records as $record) {
            $data = new stdClass();
            $data->datecreated = date('d/m/Y', $record->timecreated);
            $data->answer = $record->userlog;
            $data->comment = $record->comment;

            $output[] = $data;
        }

        return $output;
    }

}