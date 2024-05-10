@mod @mod_wordcloud

Feature: Words can be edited in a wordcloud

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
    And I log in as "teacher"
    And I am on "Course 1" course homepage with editing mode on
    And I add a wordcloud activity to course "Course 1" section "1" and I fill the form with:
      | Wordcloud activity name | Test wordcloud |
    And I am on the "Test wordcloud" "wordcloud activity" page
    And I set the field "mod-wordcloud-new-word" to "word1"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "word2"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "word2"
    And I press "mod-wordcloud-btn"
    And I set the field "mod-wordcloud-new-word" to "word3"
    And I press "mod-wordcloud-btn"

  @javascript
  Scenario: I edit one word
    Given I am on the "Test wordcloud" "wordcloud activity" page
    And I should see "word2"
    And I follow "Edit words"
    And I set the field with xpath "//input[@value='word1']" to "neww1"
    And I press "Save"
    Then I should see "neww1"
    And I should not see "word1"

  @javascript
  Scenario: I change the count of one word
    Given I am on the "Test wordcloud" "wordcloud activity" page
    And I follow "Edit words"
    And I set the field with xpath "//input[@value='2']" to "10"
    And I press "Save"
    Then I should see "word2" in the ".w6" "css_element"
