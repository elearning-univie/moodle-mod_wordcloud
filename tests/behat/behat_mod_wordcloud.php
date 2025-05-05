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
 * provider
 *
 * @package       mod_wordcloud
 * @author        Karri Pajarinen
 * @copyright     Universitaet Wien
 * @since         Moodle 4.4
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Steps definitions for the wordcloud behat tests.
 */
class behat_mod_wordcloud extends behat_base {
    /**
     * Focuses on a field and simulates pressing enter
     *
     * @Then I press Enter in the field with id :fieldId
     *
     * @param string $fieldid ID of the field
     * @throws moodle_exception the field is not found or the action fails
     */
    public function i_press_enter_in_field($fieldid) {
        $session = $this->getSession();
        $driver = $session->getDriver();
        $page = $session->getPage();

        $field = $page->find('css', '#' . $fieldid);
        if (!$field) {
            throw new \moodle_exception(get_string('path_error', 'mod_wordcloud'));
        }

        // Field is visible and enabled.
        if (!$field->isVisible()) {
            throw new \moodle_exception(get_string('path_error', 'mod_wordcloud'));
        }
        if ($field->getAttribute('disabled')) {
            throw new \moodle_exception(get_string('path_error', 'mod_wordcloud'));
        }

        try {
            $field->click();
        } catch (\Exception $e) {
            throw new \moodle_exception(get_string('path_error', 'mod_wordcloud'));
        }

        try {
            $driver->getWebDriverSession()->element('css selector', '#' . $fieldid)->sendKeys("\uE007");
        } catch (\Exception $e) {
            $session->executeScript("
                var input = document.getElementById('" . $fieldid . "');
                if (input) {
                    var event = new KeyboardEvent('keydown', {
                        key: 'Enter',
                        code: 'Enter',
                        keyCode: 13,
                        which: 13,
                        bubbles: true,
                        cancelable: true
                    });
                    input.dispatchEvent(event);
                } else {
                    throw new Error('JavaScript could not find the field with ID \"" . $fieldid . "\".');
                }
            ");
        }
        $session->wait(2000, "document.readyState === 'complete'");
    }

    /**
     * Custom defintion for deleting
     * @When I click the delete button
     * @throws moodle_exception button not found
     */
    public function i_click_the_delete_button() {
        $button = $this->getSession()->getPage()->find('css', 'button[data-action="delete"]');
        if (null === $button) {
            throw new \moodle_exception('path_error', 'mod_wordcloud');
        }
        $button->click();
    }


    /**
     * Custom defintion for exporting
     *
     * @When I click the export button
     * @throws moodle_exception button not found
     */
    public function i_click_the_export_button() {
        $button = $this->getSession()->getPage()->find('css', '.path-mod-wordcloud .wc-right-align');
        if (null === $button) {
            throw new \moodle_exception('path_error', 'mod_wordcloud');
        }
        $button->click();
    }

    /**
     * Selects export from dropdown menu.
     * @When I select :option from the export dropdown
     */
    public function i_select_from_export_dropdown($option) {
        $select = $this->getSession()->getPage()->find('css', '#mod-wordcloud-export-menu');
        if (null === $select) {
            throw new \moodle_exception('path_error', 'mod_wordcloud');
        }
        $select->selectOption($option);
    }

    /**
     * Verifies that the URL of the opened tab matches the expected URL.
     *
     * @Then the download page should be :url
     * @param string $url The expected URL.
     * @throws moodle_exception If the URL of the newly opened tab does not match the expected URL.
     */
    public function the_download_page_should_be($url) {
        $session = $this->getSession();
        $driver = $session->getDriver();

        $windownames = $driver->getWindowNames();

        if (count($windownames) <= 1) {
            throw new \moodle_exception(get_string('error_notab', 'mod_oercollection'));
        }

        $driver->switchToWindow(end($windownames));
        $session->wait(5000, "document.readyState === 'complete'");
        $currenturl = $session->getCurrentUrl();

        if (strpos($currenturl, $url) === false) {
            throw new \moodle_exception(get_string('url_mismatch', 'mod_oercollection', (object)[
                'actual' => $currenturl,
                'expected' => $url,
            ]));
        }
    }
}
