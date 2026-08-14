@mod @mod_wordcloud

Feature: Teachers exports the entered words

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    And the following "activities" exist:
      | activity   | name                   | intro                         | course |
      | wordcloud  | Test wordcloud         | Test wordcloud description    | C1     |

  @javascript
  Scenario: Teacher exports the words they and a student added
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Test wordcloud"
    And I set the field "mod-wordcloud-new-word" to "test word0"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "test word1"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "test word2"
    And I press "mod-wordcloud-btn"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test wordcloud"
    And I set the field "mod-wordcloud-new-word" to "test word3"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "test word4"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "test word5"
    And I press "mod-wordcloud-btn"
    Then I should see "Submitted words: 6"
    And I click the export button
    And I select "Export CSV" from the export dropdown
    Then the download page should be "/mod/wordcloud/export.php?id="
    And the downloaded file should contain "test word0"
    And the downloaded file should contain "test word3"
    And the downloaded file should contain "test word5"
