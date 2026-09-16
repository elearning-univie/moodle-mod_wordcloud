@mod @mod_wordcloud
Feature: Deleting and restoring a wordcloud activity
  In order to recover from mistakes
  As a teacher
  I need a deleted wordcloud and its words to be restorable from the recycle bin

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher  | Teacher   | 1        | teacher@example.com |
      | student  | Student   | 1        | student@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
      | student | C1     | student        |
    And the following "activities" exist:
      | activity  | name           | intro                      | course |
      | wordcloud | Test wordcloud | Test wordcloud description | C1     |
    And the following "mod_wordcloud > entries" exist:
      | wordcloud      | word       | count | user    |
      | Test wordcloud | test word0 | 1     | student |
    And the following config values are set as admin:
      | autohide | 0 | tool_recyclebin |

  @javascript
  Scenario: Teacher deletes the wordcloud activity
    Given I log in as "teacher"
    And I am on "Course 1" course homepage with editing mode on
    When I delete "Test wordcloud" activity
    # Deletion is handed off to an ad-hoc task, so it has to be run before the
    # recycle bin reflects the change.
    And I run all adhoc tasks
    And I navigate to "Recycle bin" in current page administration
    Then I should see "Test wordcloud"
    When I am on the "Course 1" "course" page logged in as "student"
    Then I should not see "Test wordcloud"

  @javascript
  Scenario: Teacher restores the wordcloud activity with its words
    Given I log in as "teacher"
    And I am on "Course 1" course homepage with editing mode on
    When I delete "Test wordcloud" activity
    And I run all adhoc tasks
    Then I should not see "Test wordcloud"
    When I navigate to "Recycle bin" in current page administration
    Then I should see "Test wordcloud"
    When I click on "Restore" "link" in the "region-main" "region"
    And I run all adhoc tasks
    And I am on the "Test wordcloud" "wordcloud activity" page logged in as "student"
    Then I should see "test word0"
