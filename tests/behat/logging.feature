@mod @mod_wordcloud
Feature: Wordcloud activity is recorded in the logs
  In order to review what happened in a course
  As an admin
  I need wordcloud events to appear in the log reports

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    And the following "activities" exist:
      | activity  | name           | intro                      | course |
      | wordcloud | Test wordcloud | Test wordcloud description | C1     |

  @javascript
  Scenario: Creating and viewing a wordcloud is logged
    Given I am on the "Test wordcloud" "wordcloud activity" page logged in as "admin"
    When I navigate to "Reports > Logs" in site administration
    And I set the field "id" to "Course 1"
    And I press "Get these logs"
    Then I should see "Course module viewed"
    And I should see "Wordcloud: Test wordcloud"

  @javascript
  Scenario: Pausing live log updates does not stop wordcloud activity from being logged
    Given I log in as "admin"
    And I navigate to "Reports > Live logs" in site administration
    And I click on "Pause live updates" "button"
    And I log out
    And I am on the "Test wordcloud" "wordcloud activity" page logged in as "student1"
    And I set the field "mod-wordcloud-new-word" to "test word"
    And I press "mod-wordcloud-btn"
    And I log out
    When I log in as "admin"
    And I navigate to "Reports > Logs" in site administration
    And I set the field "id" to "Course 1"
    And I press "Get these logs"
    # "Pause live updates" only freezes the auto-refreshing table on the Live logs
    # page itself; it has no effect on whether events get recorded, so student1's
    # actions must still show up in the persistent Logs report.
    Then I should see "Student 1"
    And I should see "Course module viewed"
