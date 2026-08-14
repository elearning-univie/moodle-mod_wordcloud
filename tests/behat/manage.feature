@mod @mod_wordcloud
Feature: Teacher adds and deletes the wordcloud activity

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher  | teacher   | 1  | teacher@example.com |
      | student  | student   | 1  | student@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
      | student | C1     | student        |
    And the following "activities" exist:
      | activity   | name               | intro                       | course |
      | wordcloud  | Test wordcloud     | Test wordcloud description  | C1     |
    And I log in as "admin"
    And I navigate to "Plugins > Admin tools > Recycle bin" in site administration
    And I click on "id_s_tool_recyclebin_autohide" "checkbox"
    And I click on "Save changes" "button"
    And I log out

  @javascript
  Scenario: Teacher deletes the wordcloud activity
    When I log in as "teacher"
    And I am on "Course 1" course homepage with editing mode on
    And I delete "Test wordcloud" activity
    And I run all adhoc tasks
    And I navigate to "Recycle bin" in current page administration
    Then I should see "Test wordcloud"
    When I log in as "student"
    And I am on "Course 1" course homepage
    Then I should not see "Test wordcloud"

  @javascript
  Scenario: Teacher restores activity
    When I log in as "student"
    And I am on "Course 1" course homepage
    And I follow "Test wordcloud"
    And I set the field "mod-wordcloud-new-word" to "test word0"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "test word1"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "test word2"
    And I press "mod-wordcloud-btn"
    Then I log out
    When I log in as "teacher"
    And I am on "Course 1" course homepage with editing mode on
    And I delete "Test wordcloud" activity
    And I run all adhoc tasks
    Then I should not see "Test wordcloud"
    When I navigate to "Recycle bin" in current page administration
    Then I should see "Test wordcloud"
    When I click on "Restore" "link" in the "region-main" "region"
    And I run all adhoc tasks
    And I log out
    And I log in as "student"
    And I am on "Course 1" course homepage
    Then I should see "Test wordcloud"
    And I follow "Test wordcloud"
    And I should see "test word0"
