@mod @mod_wordcloud
Feature: Submitting words to a wordcloud
  In order to collect and visualise contributions
  As a course participant
  I need to be able to submit words and see them counted in the cloud

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
    And the following "activities" exist:
      | activity  | name           | intro                      | course |
      | wordcloud | Test wordcloud | Test wordcloud description | C1     |

  @javascript
  Scenario Outline: Participants can submit a word and see it in the cloud
    Given I am on the "Test wordcloud" "wordcloud activity" page logged in as "<user>"
    When I set the field "mod-wordcloud-new-word" to "<word>"
    And I press "mod-wordcloud-btn"
    Then I should see "<word>"
    And I should see "Submitted words: 1"

    Examples:
      | user     | word                                     |
      | teacher1 | teacher word                             |
      | student1 | student word                             |
      | student2 | ääääääääääääääääääääääääääääääääääääääää |

  @javascript
  Scenario: Submitting several different words counts each of them
    Given I am on the "Test wordcloud" "wordcloud activity" page logged in as "student1"
    When I set the field "mod-wordcloud-new-word" to "alpha"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "beta"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "gamma"
    And I press "mod-wordcloud-btn"
    Then I should see "alpha"
    And I should see "beta"
    And I should see "gamma"
    And I should see "Submitted words: 3"

  @javascript
  Scenario: The same word submitted repeatedly is merged and counted up
    Given the following "mod_wordcloud > entries" exist:
      | wordcloud      | word      | count | user     |
      | Test wordcloud | test word | 3     | student1 |
    And I am on the "Test wordcloud" "wordcloud activity" page logged in as "student2"
    Then I should see "test word"
    And I should see "Submitted words: 3"
    When I set the field "mod-wordcloud-new-word" to "test word"
    And I press "mod-wordcloud-btn"
    Then I should see "Submitted words: 4"
    And I should see "test word"

  @javascript
  Scenario: Words submitted by one participant are visible to another
    Given the following "mod_wordcloud > entries" exist:
      | wordcloud      | word          | count | user     |
      | Test wordcloud | shared word   | 1     | student1 |
    When I am on the "Test wordcloud" "wordcloud activity" page logged in as "student2"
    Then I should see "shared word"
