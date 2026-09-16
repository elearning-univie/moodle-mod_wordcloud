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
     *
     * @param string $option The option to select from the dropdown.
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
            throw new \moodle_exception('error_notab', 'mod_wordcloud');
        }

        $driver->switchToWindow(end($windownames));
        $session->wait(5000, "document.readyState === 'complete'");
        $currenturl = $session->getCurrentUrl();

        if (strpos($currenturl, $url) === false) {
            $debuginfo = "Expected the URL to contain '$url' but it was '$currenturl'.";
            throw new \moodle_exception('url_mismatch', 'mod_wordcloud', '', null, $debuginfo);
        }
    }

    /**
     * Downloads the file at the currently open tab's URL (using the logged-in user's
     * session) and asserts the given text is present in its content.
     *
     * Checking the actual downloaded content is more reliable than only comparing the
     * tab's URL: the URL check is sensitive to things that legitimately differ between
     * environments (wwwroot, course module id), while this confirms the export genuinely
     * contains the expected data. Call this after "the download page should be ...", once
     * the new tab has been switched to.
     *
     * @Then the downloaded file should contain :text
     *
     * @param string $text Text expected to be present in the downloaded content.
     * @throws moodle_exception if the download fails or the text is not found.
     */
    public function the_downloaded_file_should_contain($text) {
        $session = $this->getSession();
        $url = $session->getCurrentUrl();
        $cookie = $session->getCookie('MoodleSession');

        if (empty($cookie)) {
            throw new \moodle_exception('path_error', 'mod_wordcloud');
        }

        $content = download_file_content($url, ['Cookie' => 'MoodleSession=' . $cookie]);

        if (strpos($content, $text) === false) {
            throw new \moodle_exception('export_content_mismatch', 'mod_wordcloud', '', $text);
        }
    }
}
