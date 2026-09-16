@mod @mod_wordcloud
Feature: Wordcloud activity completion
  In order to track participation
  As a teacher
  I need the activity to complete once a student has submitted enough words

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | Course 1 | C1        | 0        | 1                |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity  | name           | intro       | course | completion | completionsubmits |
      | wordcloud | Test wordcloud | Submit two! | C1     | 2          | 2                 |

  @javascript
  Scenario: The completion condition is not met before enough words are submitted
    Given I am on the "Test wordcloud" "wordcloud activity" page logged in as "student1"
    When I set the field "mod-wordcloud-new-word" to "first word"
    And I press "mod-wordcloud-btn"
    And I am on "Course 1" course homepage
    Then the "Submit words: 2" completion condition of "Test wordcloud" is displayed as "todo"

  @javascript
  Scenario: The completion condition is met once enough words are submitted
    Given I am on the "Test wordcloud" "wordcloud activity" page logged in as "student1"
    When I set the field "mod-wordcloud-new-word" to "first word"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "second word"
    And I press "mod-wordcloud-btn"
    And I am on "Course 1" course homepage
    Then the "Submit words: 2" completion condition of "Test wordcloud" is displayed as "done"

  @javascript
  Scenario: Submitting the same word twice does not satisfy the condition
    Given I am on the "Test wordcloud" "wordcloud activity" page logged in as "student1"
    # The rule counts distinct words the user is linked to, not raw submissions.
    When I set the field "mod-wordcloud-new-word" to "same word"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "same word"
    And I press "mod-wordcloud-btn"
    And I am on "Course 1" course homepage
    Then the "Submit words: 2" completion condition of "Test wordcloud" is displayed as "todo"
