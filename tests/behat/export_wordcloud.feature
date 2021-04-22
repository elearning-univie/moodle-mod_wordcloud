@mod @mod_wordcloud

Feature: As a teacher I want to export the wordcloud

  Background:
    Given the following "users" exist:
      | username | firstname | lastname  | email               |
      | teacher  | John      | Doe       | teacher@example.com |
      | student  | Derpina   | Knowsalot | student@example.com |
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
    And I follow "Test wordcloud"
    And I set the field "mod-wordcloud-new-word" to "ääääääääääääääääääääääääääääääääääääääää"
    And I press "mod-wordcloud-btn"

  @javascript
  Scenario: Export wordcloud as csv
    When I am on the "C1" "Course" page
    And I follow "Test wordcloud"
    Then following "Export CSV" should download between "80" and "110" bytes
