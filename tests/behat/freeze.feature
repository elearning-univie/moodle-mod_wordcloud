@mod @mod_wordcloud

Feature: As an admin I can freeze the wordcloud logs

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student   | 1 | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    
  @javascript
  Scenario: the logs are freezed
    Given I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I add a wordcloud activity to course "Course 1" section "1" and I fill the form with:
      | Wordcloud activity name | Test wordcloud |
    And I am on the "Test wordcloud" "wordcloud activity" page
    And I navigate to "Reports > Logs" in site administration
    When I set the field "id" to "Course 1"
    And I press "Get these logs"
    Then I should see "Course viewed"
    And I should see "Course module created"
    And I should see "Wordcloud: Test wordcloud"
    When I navigate to "Reports > Live logs" in site administration
    And I click on "Pause live updates" "button"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test wordcloud"
    And I set the field "mod-wordcloud-new-word" to "test word"
    And I press "mod-wordcloud-btn"
    Then I log out
    When I log in as "admin"
    And I navigate to "Reports > Logs" in site administration
    When I set the field "id" to "Course 1"
    And I press "Get these logs"
    # fails  here
    Then I should not see "Student 1" 