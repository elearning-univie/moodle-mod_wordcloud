@mod @mod_wordcloud @amc

Feature: As a teacher I want to add a wordcloud activity

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher  | Teacher   | Teacher  | teacher@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
    And I log in as "teacher"
    And I am on "Course 1" course homepage with editing mode on

  @javascript
  Scenario: I add a wordcloud activity
    When I add a "Wordcloud" to section "1" and I fill the form with:
      | Wordcloud activity name | Test wordcloud |
    And I follow "Test wordcloud"
    Then I should see "Test wordcloud"
