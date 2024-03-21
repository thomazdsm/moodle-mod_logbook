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

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_logbook_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG;
        $mform = $this->_form;

        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements(get_string('moduleintro'));

        $this->add_specific_elements();

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    public function add_specific_elements() {
        // TODO: Verificar nome do método
        $formgroup = array();
        $mform =& $this->_form;

        $options = array(
            0 => get_string('none', 'logbook'),
            1 => get_string('onlyteachers', 'logbook'),
        );
        $formgroup[] =& $mform->createElement('select', 'hascomments', get_string('specifcelements', 'logbook'), $options);

        $mform->addGroup($formgroup,
            'specifcelements',
            get_string('specifcelements', 'logbook'),
            ' ',
            false);

        return array('specifcelements');

    }

}
