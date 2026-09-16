@mod @mod_wordcloud
Feature: Controlling when words can be submitted and seen
  In order to run a wordcloud at a specific point in a course
  As a teacher
  I need the open/close dates and the visibility setting to be enforced

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

  @javascript
  Scenario: Words cannot be submitted before the open date
    Given the following "activities" exist:
      | activity  | name           | intro       | course | timeopen     |
      | wordcloud | Test wordcloud | Not yet on  | C1     | ##tomorrow## |
    When I am on the "Test wordcloud" "wordcloud activity" page logged in as "student1"
    Then "mod-wordcloud-new-word" "field" should not exist
    And "mod-wordcloud-btn" "button" should not exist

  @javascript
  Scenario: Words cannot be submitted after the due date
    Given the following "activities" exist:
      | activity  | name           | intro     | course | timeclose     |
      | wordcloud | Test wordcloud | Closed    | C1     | ##yesterday## |
    When I am on the "Test wordcloud" "wordcloud activity" page logged in as "student1"
    Then "mod-wordcloud-new-word" "field" should not exist
    And "mod-wordcloud-btn" "button" should not exist

  @javascript
  Scenario: Words can be submitted while the activity is open
    Given the following "activities" exist:
      | activity  | name           | intro | course | timeopen      | timeclose    |
      | wordcloud | Test wordcloud | Open  | C1     | ##yesterday## | ##tomorrow## |
    When I am on the "Test wordcloud" "wordcloud activity" page logged in as "student1"
    And I set the field "mod-wordcloud-new-word" to "in time"
    And I press "mod-wordcloud-btn"
    Then I should see "in time"

  @javascript
  Scenario: With visibility "only after own submission" words stay hidden until the student contributes
    Given the following "activities" exist:
      | activity  | name           | intro      | course | visibility |
      | wordcloud | Test wordcloud | Hidden yet | C1     | 1          |
    And the following "mod_wordcloud > entries" exist:
      | wordcloud      | word       | count | user     |
      | Test wordcloud | other word | 1     | student2 |
    When I am on the "Test wordcloud" "wordcloud activity" page logged in as "student1"
    Then I should see "Words are displayed only after submission!"
    And I should not see "other word"
    When I set the field "mod-wordcloud-new-word" to "my word"
    And I press "mod-wordcloud-btn"
    Then I should see "my word"
    And I should see "other word"

  @javascript
  Scenario: With visibility "only after due date" words stay hidden until the activity closes
    Given the following "activities" exist:
      | activity  | name           | intro     | course | visibility | timeclose    |
      | wordcloud | Test wordcloud | Hidden    | C1     | 2          | ##tomorrow## |
    And the following "mod_wordcloud > entries" exist:
      | wordcloud      | word       | count | user     |
      | Test wordcloud | other word | 1     | student2 |
    When I am on the "Test wordcloud" "wordcloud activity" page logged in as "student1"
    Then I should see "Words are displayed only after the submission deadline!"
    And I should not see "other word"

  @javascript
  Scenario: A teacher who can edit entries always sees the words
    Given the following "activities" exist:
      | activity  | name           | intro      | course | visibility |
      | wordcloud | Test wordcloud | Hidden yet | C1     | 1          |
    And the following "mod_wordcloud > entries" exist:
      | wordcloud      | word       | count | user     |
      | Test wordcloud | other word | 1     | student2 |
    When I am on the "Test wordcloud" "wordcloud activity" page logged in as "teacher1"
    Then I should see "other word"
