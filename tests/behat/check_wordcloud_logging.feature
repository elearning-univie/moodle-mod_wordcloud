@mod @mod_wordcloud

Feature: As an admin I can check the wordcloud logs

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Wordcloud" to section "1" and I fill the form with:
      | Wordcloud activity name | Test wordcloud |
    And I follow "Test wordcloud"

  @javascript
  Scenario: I check the wordcloud logs
    Given I navigate to "Reports > Logs" in site administration
    When I set the field "id" to "Course 1"
    And I press "Get these logs"
    Then I should see "Course module viewed"
    And I should see "Course module created"
    And I should see "Wordcloud: Test wordcloud"
