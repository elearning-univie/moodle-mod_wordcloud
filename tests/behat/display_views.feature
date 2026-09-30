@mod @mod_wordcloud
Feature: Switching how the wordcloud is displayed
  In order to read the collected words in the way that suits me
  As a course participant
  I need to switch between the cloud and the accessible list view

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity  | name           | intro                      | course |
      | wordcloud | Test wordcloud | Test wordcloud description | C1     |
    And the following "mod_wordcloud > entries" exist:
      | wordcloud      | word   | count | user     |
      | Test wordcloud | apple  | 5     | student1 |
      | Test wordcloud | banana | 3     | student1 |
      | Test wordcloud | cherry | 1     | student1 |

  @javascript
  Scenario: The display menu offers the cloud and list views
    Given I am on the "Test wordcloud" "wordcloud activity" page logged in as "student1"
    Then the "mod-wordcloud-view-menu" select box should contain "Wordcloud"
    And the "mod-wordcloud-view-menu" select box should contain "List"

  @javascript
  Scenario: The list view shows every word with its count
    Given I am on the "Test wordcloud" "wordcloud activity" page logged in as "student1"
    When I set the field "mod-wordcloud-view-menu" to "List"
    Then I should see "Word" in the "mod-wordcloud-wordlist" "table"
    And I should see "Count" in the "mod-wordcloud-wordlist" "table"
    And I should see "apple" in the "mod-wordcloud-wordlist" "table"
    And I should see "banana" in the "mod-wordcloud-wordlist" "table"
    And I should see "cherry" in the "mod-wordcloud-wordlist" "table"
    And ".pagination" "css_element" should not exist

  @javascript
  Scenario: The list view is sorted by count and can be re-sorted by word
    Given I am on the "Test wordcloud" "wordcloud activity" page logged in as "student1"
    When I set the field "mod-wordcloud-view-menu" to "List"
    Then "//table[@id='mod-wordcloud-wordlist']/tbody/tr[1]/td[contains(., 'apple')]" "xpath_element" should exist
    # A newly chosen column uses the table's default direction (descending).
    When I click on "Word" "link" in the "mod-wordcloud-wordlist" "table"
    Then "//table[@id='mod-wordcloud-wordlist']/tbody/tr[1]/td[contains(., 'cherry')]" "xpath_element" should exist
    When I click on "Word" "link" in the "mod-wordcloud-wordlist" "table"
    Then "//table[@id='mod-wordcloud-wordlist']/tbody/tr[1]/td[contains(., 'apple')]" "xpath_element" should exist
    And "//table[@id='mod-wordcloud-wordlist']/tbody/tr[3]/td[contains(., 'cherry')]" "xpath_element" should exist
    When I click on "Reset table preferences" "link"
    Then "//table[@id='mod-wordcloud-wordlist']/tbody/tr[1]/td[contains(., 'apple')]" "xpath_element" should exist

  @javascript
  Scenario: Submitted HTML is shown as text, not markup
    Given the following "mod_wordcloud > entries" exist:
      | wordcloud      | word        | count |
      | Test wordcloud | <b>bold</b> | 1     |
    When I am on the "Test wordcloud" "wordcloud activity" page logged in as "student1"
    Then I should see "<b>bold</b>" in the "#mod-wordcloud-words-box" "css_element"
    And "#mod-wordcloud-words-box b" "css_element" should not exist
    When I set the field "mod-wordcloud-view-menu" to "List"
    Then I should see "<b>bold</b>" in the "mod-wordcloud-wordlist" "table"
    And "#mod-wordcloud-wordlist b" "css_element" should not exist
