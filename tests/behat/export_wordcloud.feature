@mod @mod_wordcloud

Feature: As a teacher I want to export the wordcloud

  Background:
    Given the following "users" exist:
      | username | firstname | lastname  | email               |
      | teacher  | John      | Doe       | teacher@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |

  @javascript
  Scenario: Export wordcloud as csv
    When I log in as "teacher"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Wordcloud" to section "1" and I fill the form with:
      | Wordcloud activity name | Test wordcloud |
    And I am on the "Test wordcloud" "wordcloud activity" page
    And I set the field "mod-wordcloud-new-word" to "new entry in the wordcloud"
    And I press "mod-wordcloud-btn"
    Then following "Export CSV" should download between "40" and "50" bytes
