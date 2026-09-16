@mod @mod_wordcloud
Feature: Context freezing for a wordcloud
  In order to preserve a wordcloud unchanged
  As an admin
  I need freezing the context to stop everyone from submitting words

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
      | activity  | name           | intro                      | course | idnumber   |
      | wordcloud | Test wordcloud | Test wordcloud description | C1     | wcnogroups |

  @javascript
  Scenario: Freezing the context removes the submission field for everyone
    Given I am on the "Test wordcloud" "wordcloud activity" page logged in as "student1"
    And "mod-wordcloud-new-word" "field" should exist
    When the "wcnogroups" "Activity module" is context frozen
    And I am on the "Test wordcloud" "wordcloud activity" page
    Then "mod-wordcloud-new-word" "field" should not exist
    When I am on the "Test wordcloud" "wordcloud activity" page logged in as "teacher1"
    Then "mod-wordcloud-new-word" "field" should not exist
