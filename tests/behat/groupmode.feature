@mod @mod_wordcloud
Feature: Group modes in a wordcloud
  In order to run separate discussions per group
  As a course participant
  I need the wordcloud to respect the activity's group mode

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
    And the following "groups" exist:
      | name    | course | idnumber |
      | Group A | C1     | G1       |
      | Group B | C1     | G2       |
      | Group C | C1     | G3       |
    And the following "group members" exist:
      | user     | group |
      | student1 | G1    |
      | student2 | G1    |
      | student2 | G2    |

  @javascript
  Scenario Outline: A teacher with accessallgroups sees every group's words
    Given the following "activities" exist:
      | activity  | name           | intro                      | course | idnumber | groupmode   |
      | wordcloud | Test wordcloud | Test wordcloud description | C1     | groups   | <groupmode> |
    And the following "mod_wordcloud > entries" exist:
      | wordcloud      | word        | group | user     |
      | Test wordcloud | word1GroupA | G1    | student2 |
      | Test wordcloud | word1GroupB | G2    | student2 |
    And I am on the "Test wordcloud" "wordcloud activity" page logged in as "teacher1"
    Then the "<label>" select box should contain "All participants"
    And the "<label>" select box should contain "Group A"
    And the "<label>" select box should contain "Group B"
    When I select "All participants" from the "<label>" singleselect
    Then I should see "word1GroupA"
    And I should see "word1GroupB"
    When I select "Group A" from the "<label>" singleselect
    Then I should see "word1GroupA"
    And I should not see "word1GroupB"
    When I select "Group B" from the "<label>" singleselect
    Then I should see "word1GroupB"
    And I should not see "word1GroupA"

    Examples:
      | groupmode | label           |
      | 1         | Separate groups |
      | 2         | Visible groups  |

  @javascript
  Scenario: With separate groups a student only sees their own group
    Given the following "activities" exist:
      | activity  | name           | intro                      | course | idnumber | groupmode |
      | wordcloud | Test wordcloud | Test wordcloud description | C1     | groups   | 1         |
    And the following "mod_wordcloud > entries" exist:
      | wordcloud      | word        | group | user     |
      | Test wordcloud | word1GroupA | G1    | student2 |
      | Test wordcloud | word1GroupB | G2    | student2 |
    When I am on the "Test wordcloud" "wordcloud activity" page logged in as "student1"
    # student1 belongs to Group A only, so there is no group selector at all.
    Then I should see "word1GroupA"
    And I should not see "word1GroupB"
    And "Separate groups" "select" should not exist
    And "mod-wordcloud-btn" "button" should exist

  @javascript
  Scenario: With separate groups a student in two groups can switch between them
    Given the following "activities" exist:
      | activity  | name           | intro                      | course | idnumber | groupmode |
      | wordcloud | Test wordcloud | Test wordcloud description | C1     | groups   | 1         |
    And the following "mod_wordcloud > entries" exist:
      | wordcloud      | word        | group | user     |
      | Test wordcloud | word1GroupA | G1    | student2 |
      | Test wordcloud | word1GroupB | G2    | student2 |
    When I am on the "Test wordcloud" "wordcloud activity" page logged in as "student2"
    Then the "Separate groups" select box should not contain "All participants"
    When I select "Group A" from the "Separate groups" singleselect
    Then I should see "word1GroupA"
    And "mod-wordcloud-btn" "button" should exist
    When I select "Group B" from the "Separate groups" singleselect
    Then I should see "word1GroupB"
    And "mod-wordcloud-btn" "button" should exist

  @javascript
  Scenario: With visible groups a student sees all groups but only submits to their own
    Given the following "activities" exist:
      | activity  | name           | intro                      | course | idnumber | groupmode |
      | wordcloud | Test wordcloud | Test wordcloud description | C1     | groups   | 2         |
    And the following "mod_wordcloud > entries" exist:
      | wordcloud      | word        | group | user     |
      | Test wordcloud | word1GroupA | G1    | student2 |
      | Test wordcloud | word1GroupB | G2    | student2 |
    When I am on the "Test wordcloud" "wordcloud activity" page logged in as "student1"
    And I select "All participants" from the "Visible groups" singleselect
    Then I should see "word1GroupA"
    And I should see "word1GroupB"
    # student1 is a member of Group A, so may submit there.
    When I select "Group A" from the "Visible groups" singleselect
    Then I should see "word1GroupA"
    And "mod-wordcloud-btn" "button" should exist
    # ... but not in Group B, which they can see but do not belong to.
    When I select "Group B" from the "Visible groups" singleselect
    Then I should see "word1GroupB"
    And "mod-wordcloud-btn" "button" should not exist
