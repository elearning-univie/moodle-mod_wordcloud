@mod @mod_wordcloud

Feature: As a student I can add words to a wordcloud

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher  | John   | Doe  | teacher@example.com |
      | student  | Derpina   | Knowsalot  | student@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
      | student | C1     | student        |
    And I log in as "teacher"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Wordcloud" to section "1" and I fill the form with:
      | Wordcloud activity name | Test wordcloud |
    And I log out

  @javascript
  Scenario: I submit a word to a wordcloud as a student
    Given I log in as "student"
    And I am on "Course 1" course homepage
    And I follow "Test wordcloud"
    And I set the field "mod-wordcloud-new-word" to "ääääääääääääääääääääääääääääääääääääääää"
    And I press "mod-wordcloud-btn"
    Then I should see "ääääääääääääääääääääääääääääääääääääääää"
