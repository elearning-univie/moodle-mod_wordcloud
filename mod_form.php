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
 * Defines the wordcloud module settings form.
 *
 * @package    mod_wordcloud
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->libdir . '/formslib.php');

/**
 * Settings form for the wordcloud module
 *
 * @package    mod_wordcloud
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_wordcloud_mod_form extends moodleform_mod {
    /** @var array options to be used with date_time_selector fields in the wordcloud. */
    public static $datefieldoptions = array('optional' => true);

    /**
     * definition
     * @throws coding_exception
     */
    public function definition() {
        $wordcloudconfig = get_config('wordcloud');

        $mform =& $this->_form;

        $mform->addElement('text', 'name', get_string('wordcloudname', 'wordcloud'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements();

        $mform->addElement('header', 'appearance', get_string('appearance', 'wordcloud'));

        $radioscheme = array();
        $radioscheme[] = $mform->createElement('radio', 'usemonocolor', '', get_string('usemonocolor_random', 'wordcloud'), 0);
        $radioscheme[] = $mform->createElement('radio', 'usemonocolor', '', get_string('usemonocolor_sequential', 'wordcloud'), 1);
        $mform->addGroup($radioscheme, 'radioscheme', get_string('usemonocolor', 'wordcloud'), array(' '), false);
        $mform->addHelpButton('radioscheme', 'usemonocolor', 'wordcloud');

        $radiocolor = array();
        for ($i = 1; $i <= 6; $i++) {
            $fontcolor = 'fontcolor' . $i;
            $radiocolor[] = $mform->createElement('radio', 'monocolor', '',
                '<span style="color: #' . $wordcloudconfig->$fontcolor . '">⬤</span>', $i);
        }
        $radiocolor[] = $mform->createElement('radio', 'monocolor', '', get_string('monocolor_hex', 'wordcloud'), 0);
        $mform->addGroup($radiocolor, 'radiocolor', get_string('monocolor', 'wordcloud'), array(' '), false);
        $mform->setDefault('monocolor', 1);
        $mform->hideIf('monocolor', 'usemonocolor');
        $mform->hideIf('radiocolor', 'usemonocolor');
        $mform->addHelpButton('radiocolor', 'monocolor', 'wordcloud');
        $mform->addElement('text', 'monocolorhex', get_string('monocolor_hex', 'wordcloud'), array('size' => '6'));
        $mform->setType('monocolorhex', PARAM_TEXT);
        $mform->setDefault('monocolorhex', '000000');
        $mform->hideIf('monocolorhex', 'monocolor', 'neq', 0);
        $mform->hideIf('monocolorhex', 'usemonocolor');
        $mform->addHelpButton('monocolorhex', 'monocolor_hex', 'wordcloud');

        $mform->addElement('header', 'timing', get_string('timing', 'wordcloud'));
        $mform->addElement('date_time_selector', 'timeopen', get_string('activityopen', 'wordcloud'),
            self::$datefieldoptions);
        $mform->addHelpButton('timeopen', 'activityopen', 'wordcloud');
        $mform->addElement('date_time_selector', 'timeclose', get_string('activityclose', 'wordcloud'),
            self::$datefieldoptions);
        $mform->addHelpButton('timeclose', 'activityclose', 'wordcloud');

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Check if everything is correct and check also the user rights for the action;
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (!ctype_xdigit($data['monocolorhex']) || strlen($data['monocolorhex']) != 6) {
            $errors['monocolorhex'] = get_string('errormonocolorhex', 'wordcloud');
        }
        if ($data['timeopen'] != 0 && $data['timeclose'] != 0 && $data['timeclose'] < $data['timeopen']) {
            $errors['timeclose'] = get_string('closebeforeopen', 'wordcloud');
        }
        return $errors;
    }
}
