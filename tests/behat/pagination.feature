@mod @mod_wordcloud
Feature: Paginated word lists
  In order to read long word lists
  As a teacher
  I need the list views to be split into pages

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity  | name           | intro                      | course |
      | wordcloud | Test wordcloud | Test wordcloud description | C1     |
    And the following "mod_wordcloud > entries" exist:
      | wordcloud      | word    | count |
      | Test wordcloud | entry01 | 1     |
      | Test wordcloud | entry02 | 2     |
      | Test wordcloud | entry03 | 3     |
      | Test wordcloud | entry04 | 4     |
      | Test wordcloud | entry05 | 5     |
      | Test wordcloud | entry06 | 6     |
      | Test wordcloud | entry07 | 7     |
      | Test wordcloud | entry08 | 8     |
      | Test wordcloud | entry09 | 9     |
      | Test wordcloud | entry10 | 10    |
      | Test wordcloud | entry11 | 11    |
      | Test wordcloud | entry12 | 12    |
      | Test wordcloud | entry13 | 13    |
      | Test wordcloud | entry14 | 14    |
      | Test wordcloud | entry15 | 15    |
      | Test wordcloud | entry16 | 16    |
      | Test wordcloud | entry17 | 17    |
      | Test wordcloud | entry18 | 18    |
      | Test wordcloud | entry19 | 19    |
      | Test wordcloud | entry20 | 20    |
      | Test wordcloud | entry21 | 21    |
      | Test wordcloud | entry22 | 22    |
      | Test wordcloud | entry23 | 23    |
      | Test wordcloud | entry24 | 24    |
      | Test wordcloud | entry25 | 25    |

  @javascript
  Scenario: The list view shows 20 words per page
    Given I am on the "Test wordcloud" "wordcloud activity" page logged in as "teacher1"
    When I set the field "mod-wordcloud-view-menu" to "List"
    Then I should see "entry25" in the "mod-wordcloud-wordlist" "table"
    And I should see "entry06" in the "mod-wordcloud-wordlist" "table"
    And I should not see "entry05" in the "mod-wordcloud-wordlist" "table"
    When I click on "2" "link" in the ".pagination" "css_element"
    Then I should see "entry05" in the "mod-wordcloud-wordlist" "table"
    And I should see "entry01" in the "mod-wordcloud-wordlist" "table"
    And I should not see "entry06" in the "mod-wordcloud-wordlist" "table"
    When I click on "Previous" "link" in the ".pagination" "css_element"
    Then I should see "entry25" in the "mod-wordcloud-wordlist" "table"

  @javascript
  Scenario: Sorting the list view returns to the first page
    Given I am on the "Test wordcloud" "wordcloud activity" page logged in as "teacher1"
    And I set the field "mod-wordcloud-view-menu" to "List"
    And I click on "2" "link" in the ".pagination" "css_element"
    When I click on "Word" "link" in the "mod-wordcloud-wordlist" "table"
    Then I should see "entry25" in the "mod-wordcloud-wordlist" "table"
    And I should see "entry06" in the "mod-wordcloud-wordlist" "table"
    And I should not see "entry05" in the "mod-wordcloud-wordlist" "table"
    When I click on "Word" "link" in the "mod-wordcloud-wordlist" "table"
    Then I should see "entry01" in the "mod-wordcloud-wordlist" "table"
    And I should see "entry20" in the "mod-wordcloud-wordlist" "table"
    And I should not see "entry21" in the "mod-wordcloud-wordlist" "table"

  @javascript
  Scenario: The list view's entries per page can be changed
    Given I am on the "Test wordcloud" "wordcloud activity" page logged in as "teacher1"
    And I set the field "mod-wordcloud-view-menu" to "List"
    When I select "5" from the "Entries per page" singleselect
    Then the field "Entries per page" matches value "5"
    And I should see "entry25" in the "mod-wordcloud-wordlist" "table"
    And I should see "entry21" in the "mod-wordcloud-wordlist" "table"
    And I should not see "entry20" in the "mod-wordcloud-wordlist" "table"
    When I click on "2" "link" in the ".pagination" "css_element"
    Then I should see "entry20" in the "mod-wordcloud-wordlist" "table"
    And I should not see "entry15" in the "mod-wordcloud-wordlist" "table"

  @javascript
  Scenario: The chosen entries per page apply to all word lists and are remembered
    Given I am on the "Test wordcloud" "mod_wordcloud > Wordlist" page logged in as "teacher1"
    When I select "All" from the "Entries per page" singleselect
    Then I should see "entry25"
    And I should see "entry01"
    And ".pagination" "css_element" should not exist
    When I am on the "Test wordcloud" "mod_wordcloud > Edit words" page
    Then the field "Entries per page" matches value "All"
    And "//input[@value='entry01']" "xpath_element" should exist
    When I am on the "Test wordcloud" "wordcloud activity" page
    And I set the field "mod-wordcloud-view-menu" to "List"
    Then I should see "entry01" in the "mod-wordcloud-wordlist" "table"
    And ".pagination" "css_element" should not exist

  @javascript
  Scenario: The edit words page's entries per page can be changed
    Given I am on the "Test wordcloud" "mod_wordcloud > Edit words" page logged in as "teacher1"
    # Sorted by count, highest first, like the other word lists.
    And "(//input[contains(@class, 'mod-wordcloud-edit-word')])[1][@value='entry25']" "xpath_element" should exist
    And "(//input[contains(@class, 'mod-wordcloud-edit-word')])[20]" "xpath_element" should exist
    And "(//input[contains(@class, 'mod-wordcloud-edit-word')])[21]" "xpath_element" should not exist
    When I select "10" from the "Entries per page" singleselect
    Then "(//input[contains(@class, 'mod-wordcloud-edit-word')])[10]" "xpath_element" should exist
    And "(//input[contains(@class, 'mod-wordcloud-edit-word')])[11]" "xpath_element" should not exist

  @javascript
  Scenario: The wordlist page shows 20 words per page
    Given I am on the "Test wordcloud" "mod_wordcloud > Wordlist" page logged in as "teacher1"
    Then I should see "entry25"
    And I should see "entry06"
    And I should not see "entry05"
    When I click on "2" "link" in the ".pagination" "css_element"
    Then I should see "entry05"
    And I should see "entry01"
    And I should not see "entry06"
