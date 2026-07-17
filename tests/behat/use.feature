@mod @mod_wordcloud
Feature: Teachers and students are using the plugin, submitting multiple words and seeing an overview of them  

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email       |
      | teacher1  | Teacher | 1 | teacher@example.com  |
      | student1  | Student | 1 | student@example.com  |
      | student2 | Student | 2 | student2@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
      | student2 | C1 | student |
    And the following "activities" exist:
      | activity   | name                   | intro                         | course |
      | wordcloud  | Test wordcloud         | Test wordcloud description    | C1     | 
    And I log out

  @javascript
  Scenario: Teacher and student are able to add multiple words using Enter and the submit button
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Test wordcloud"
    And I set the field "mod-wordcloud-new-word" to "test word0"
    And I press "mod-wordcloud-btn"
    And I should see "test word0"
    And I set the field "mod-wordcloud-new-word" to "test word1"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "test word2"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "test word3"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "test word4"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "test word5"
    And I press "mod-wordcloud-btn"
    And I should see "test word1"
    And I should see "test word2"
    And I should see "test word3"
    And I should see "test word4"
    And I should see "test word5"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test wordcloud"
    And I set the field "mod-wordcloud-new-word" to "test word6"
    And I press "mod-wordcloud-btn"
    And I should see "test word6"
    And I set the field "mod-wordcloud-new-word" to "test word7"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "test word8"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "test word9"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "test word10"
    And I press "mod-wordcloud-btn"
    Then I should see "test word6"
    And I should see "test word7"
    And I should see "test word8"
    And I should see "test word9"
    And I should see "test word10"

  @javascript
  Scenario: Students add the same words over and over again
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test wordcloud"
    And I set the field "mod-wordcloud-new-word" to "test word"
    And I press "mod-wordcloud-btn"
    Then I should see "test word"
    And I set the field "mod-wordcloud-new-word" to "test word"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "test word"
    And I press "mod-wordcloud-btn"
    And I log out
    When I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Test wordcloud"
    Then I should see "test word"
    And I set the field "mod-wordcloud-new-word" to "test word"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "test word"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "test word"
    And I press "mod-wordcloud-btn"
    Then I should see "test word"

  @javascript
  Scenario: Teacher checks word counts
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Test wordcloud"
    And I set the field "mod-wordcloud-new-word" to "test word"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "test word"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "test word"
    And I press "mod-wordcloud-btn"
    Then I should see "Words submitted: 3"
    When I set the field "mod-wordcloud-new-word" to "test word2"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "test word2"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "test word3"
    And I press "mod-wordcloud-btn"
    Then I should see "Words submitted: 6"
