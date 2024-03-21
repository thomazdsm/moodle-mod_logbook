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

require_once($CFG->libdir. '/formslib.php');

class mod_logbook_submit_form extends moodleform {

    public function definition() {
        $id = required_param('id', PARAM_INT);
        list ($course, $cm) = get_course_and_cm_from_cmid($id, 'logbook');

        global $DB;
        $mform =& $this->_form;

        $mform->addElement('hidden', 'cmid', $this->_customdata['cmid']);
        $mform->setType('cmid', PARAM_INT);

        if (isset($this->_customdata['haserror']) && $this->_customdata['haserror'] == true) {
            $mform->addElement('html', '<div class="alert alert-danger">Você deve preencher o diário!</div>');
        }

        $mform->addElement('textarea', 'userlog', get_string("userlog", "logbook"), 'wrap="virtual" rows="20" cols="50"');


        $this->add_action_buttons();

    }
}