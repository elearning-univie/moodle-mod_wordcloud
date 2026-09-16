@mod @mod_wordcloud
Feature: Exporting the submitted words
  In order to work with the collected words outside Moodle
  As a teacher
  I need to export the wordcloud contents

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity  | name           | intro                      | course |
      | wordcloud | Test wordcloud | Test wordcloud description | C1     |
    And the following "mod_wordcloud > entries" exist:
      | wordcloud      | word       | count | user     |
      | Test wordcloud | test word0 | 2     | teacher1 |
      | Test wordcloud | test word3 | 1     | student1 |
      | Test wordcloud | test word5 | 3     | student1 |

  @javascript
  Scenario: Teacher exports the words as CSV
    Given I am on the "Test wordcloud" "wordcloud activity" page logged in as "teacher1"
    And I should see "Submitted words: 6"
    When I click the export button
    And I select "Export CSV" from the export dropdown
    Then the download page should be "/mod/wordcloud/export.php?id="
    # Checking the downloaded content rather than only the URL, so the export is
    # verified end to end regardless of wwwroot or course module id.
    And the downloaded file should contain "test word0"
    And the downloaded file should contain "test word3"
    And the downloaded file should contain "test word5"
