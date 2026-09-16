@mod @mod_wordcloud
Feature: Editing the words of a wordcloud
  In order to correct or curate contributions
  As a teacher
  I need to rename, recount and remove submitted words

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher  | John      | Doe      | teacher@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
    And the following "activities" exist:
      | activity  | name           | intro                      | course |
      | wordcloud | Test wordcloud | Test wordcloud description | C1     |
    And the following "mod_wordcloud > entries" exist:
      | wordcloud      | word  | count | user    |
      | Test wordcloud | word1 | 1     | teacher |
      | Test wordcloud | word2 | 2     | teacher |
      | Test wordcloud | word3 | 1     | teacher |

  @javascript
  Scenario: Teacher renames a word
    Given I am on the "Test wordcloud" "wordcloud activity" page logged in as "teacher"
    And I follow "Edit words"
    When I set the field with xpath "//input[@value='word1']" to "neww1"
    And I press "Save"
    Then I should see "neww1"
    And I should not see "word1"

  @javascript
  Scenario: Teacher changes the count of a word
    Given I am on the "Test wordcloud" "wordcloud activity" page logged in as "teacher"
    And I follow "Edit words"
    When I set the field with xpath "//input[@value='2']" to "10"
    And I press "Save"
    # The largest count maps to the biggest font-size class in the classic view.
    Then I should see "word2" in the ".w6" "css_element"

  @javascript
  Scenario: Teacher removes a word
    Given I am on the "Test wordcloud" "wordcloud activity" page logged in as "teacher"
    And I should see "word1"
    And I follow "Edit words"
    # The remove control is a link per table row, so the row is located by the
    # value of its word input.
    When I click on "//tr[.//input[@value='word1']]//a[contains(@href, 'deleteselected')]" "xpath_element"
    Then I should see "Are you sure you want to remove the word"
    When I press "Remove"
    And I am on the "Test wordcloud" "wordcloud activity" page
    Then I should not see "word1"
    And I should see "word2"
    And I should see "word3"

  @javascript
  Scenario: Pressing Enter submits a word without clicking the button
    Given I am on the "Test wordcloud" "wordcloud activity" page logged in as "teacher"
    When I set the field "mod-wordcloud-new-word" to "entered by keyboard"
    # The key is sent to whatever currently has focus, so the field is clicked
    # first to make sure it is the active element.
    And I click on "mod-wordcloud-new-word" "field"
    And I press the enter key
    Then I should see "entered by keyboard"
