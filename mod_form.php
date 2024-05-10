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
    public static $datefieldoptions = ['optional' => true];

    /**
     * definition
     * @throws coding_exception
     */
    public function definition() {
        $wordcloudconfig = get_config('wordcloud');

        $mform =& $this->_form;

        $mform->addElement('text', 'name', get_string('wordcloudname', 'wordcloud'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements();

        $mform->addElement('header', 'appearance', get_string('appearance', 'wordcloud'));

        $radioscheme = [];
        $radioscheme[] = $mform->createElement('radio', 'usemonocolor', '', get_string('usemonocolor_random', 'wordcloud'), 0);
        $radioscheme[] = $mform->createElement('radio', 'usemonocolor', '', get_string('usemonocolor_sequential', 'wordcloud'), 1);
        $mform->addGroup($radioscheme, 'radioscheme', get_string('usemonocolor', 'wordcloud'), [' '], false);
        $mform->addHelpButton('radioscheme', 'usemonocolor', 'wordcloud');

        $radiocolor = [];
        for ($i = 1; $i <= 6; $i++) {
            $fontcolor = 'fontcolor' . $i;
            $radiocolor[] = $mform->createElement('radio', 'monocolor', '',
                '<span style="color: #' . $wordcloudconfig->$fontcolor . '">⬤</span>', $i);
        }
        $radiocolor[] = $mform->createElement('radio', 'monocolor', '', get_string('monocolor_hex', 'wordcloud'), 0);
        $mform->addGroup($radiocolor, 'radiocolor', get_string('monocolor', 'wordcloud'), [' '], false);
        $mform->setDefault('monocolor', 1);
        $mform->hideIf('monocolor', 'usemonocolor');
        $mform->hideIf('radiocolor', 'usemonocolor');
        $mform->addHelpButton('radiocolor', 'monocolor', 'wordcloud');
        $mform->addElement('text', 'monocolorhex', get_string('monocolor_hex', 'wordcloud'), ['size' => '6']);
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

        $cloudvisoptions = [
            0  => get_string('visibilityalways', 'wordcloud'),
            1 => get_string('visibilitysubmit', 'wordcloud'),
            2 => get_string('visibilitytime', 'wordcloud'),
        ];
        $mform->addElement('select', 'visibility', get_string('cloudvisibility', 'wordcloud'),
            $cloudvisoptions);
        $mform->addHelpButton('visibility', 'cloudvisibility', 'wordcloud');

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
        if ($data['timeclose'] == 0 && $data['visibility'] == 2) {
            $errors['visibility'] = get_string('visibilitytimeerror', 'wordcloud');
        }
        return $errors;
    }

    public function data_preprocessing(&$defaultvalues) {
        parent::data_preprocessing($defaultvalues);

        $suffix = $this->get_suffix();
        $completionsubmitsel = 'completionsubmits' . $suffix;
        $completionsubmitsenabledel = 'completionsubmitsenabled' . $suffix;

        // Tick by default if Add mode or if completion posts settings is set to 1 or more.
        if (empty($this->_instance) || !empty($defaultvalues[$completionsubmitsel])) {
            $defaultvalues[$completionsubmitsenabledel] = 1;
        } else {
            $defaultvalues[$completionsubmitsenabledel] = 0;
        }
        if (empty($defaultvalues[$completionsubmitsel])) {
            $defaultvalues[$completionsubmitsel] = 1;
        }
    }

    /**
     * Add elements for setting the custom completion rules.
     *
     * @category completion
     * @return array List of added element names, or names of wrapping group elements.
     */
    public function add_completion_rules() {
        $mform = $this->_form;

        $suffix = $this->get_suffix();

        $group = [];
        $completionsubmitsenabledel = 'completionsubmitsenabled' . $suffix;
        $group[] =& $mform->createElement('checkbox', $completionsubmitsenabledel, '', get_string('completionsubmits', 'wordcloud'));
        $completionsubmitsel = 'completionsubmits' . $suffix;
        $group[] =& $mform->createElement('text', $completionsubmitsel, '', ['size' => 3]);
        $mform->setType($completionsubmitsel, PARAM_INT);
        $completionsubmitsgroupel = 'completionsubmitsgroup' . $suffix;
        $mform->addGroup($group, $completionsubmitsgroupel, '', ' ', false);
        $mform->hideIf($completionsubmitsel, $completionsubmitsenabledel);

        return [$completionsubmitsgroupel];
    }

    /**
     * Called during validation to see whether some activity-specific completion rules are selected.
     *
     * @param array $data Input data not yet validated.
     * @return bool True if one or more rules is enabled, false if none are.
     */
    public function completion_rule_enabled($data) {
        return (!empty($data['completionsubmitsenabled']) && $data['completionsubmits'] != 0);
    }

    /**
     * Allows module to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param stdClass $data the form data to be modified.
     */
    public function data_postprocessing($data) {
        parent::data_postprocessing($data);
        // Turn off completion settings if the checkboxes aren't ticked.
        if (!empty($data->completionunlocked)) {
            $suffix = $this->get_suffix();
            $completion = $data->{'completion' . $suffix};
            $autocompletion = !empty($completion) && $completion == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->{'completionsubmitsenabled' . $suffix}) || !$autocompletion) {
                $data->{'completionsubmits' . $suffix} = 0;
            }
        }
    }
}
