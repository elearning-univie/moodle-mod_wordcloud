@mod @mod_wordcloud
Feature: Wordcloud appearance
  In order to present the words the way I want
  As a teacher
  I need the font and text alignment to pick the renderer, and a preview of them

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

  @javascript
  Scenario Outline: The classic renderer is only used for horizontal Arial
    Given the following "activities" exist:
      | activity  | name           | intro                      | course |
      | wordcloud | Test wordcloud | Test wordcloud description | C1     |
    And the following "mod_wordcloud > entries" exist:
      | wordcloud      | word   | count |
      | Test wordcloud | apple  | 3     |
      | Test wordcloud | banana | 1     |
    And I am on the "Test wordcloud" "wordcloud activity editing" page logged in as "teacher1"
    When I set the following fields to these values:
      | Font           | <font>      |
      | Text alignment | <alignment> |
    And I press "Save and display"
    Then "<shown>" "css_element" should exist
    And "<notshown>" "css_element" should not exist

    Examples:
      | font               | alignment             | shown                              | notshown                           |
      | Arial (sans-serif) | Horizontal only       | #mod-wordcloud-words-box span.word | #mod-wordcloud-words-canvas        |
      | Georgia (serif)    | Horizontal only       | #mod-wordcloud-words-canvas        | #mod-wordcloud-words-box span.word |
      | Arial (sans-serif) | Horizontal & vertical | #mod-wordcloud-words-canvas        | #mod-wordcloud-words-box span.word |

  @javascript
  Scenario: The preview shows the renderer the chosen settings will use
    Given the following "activities" exist:
      | activity  | name           | intro                      | course |
      | wordcloud | Test wordcloud | Test wordcloud description | C1     |
    And I am on the "Test wordcloud" "wordcloud activity editing" page logged in as "teacher1"
    When I set the following fields to these values:
      | Font           | Arial (sans-serif) |
      | Text alignment | Horizontal only    |
    And I click on "Show preview" "button"
    Then I should see "Moodle" in the "#wordcloud-preview-words-box" "css_element"
    And "#wordcloud-preview-canvas" "css_element" should not exist
    And I click on "Cancel" "button" in the ".modal-dialog" "css_element"
    When I set the field "Font" to "Georgia (serif)"
    And I click on "Show preview" "button"
    Then "#wordcloud-preview-canvas" "css_element" should exist

  @javascript
  Scenario: The canvas uses the full width of a growing cloud
    Given the following "activities" exist:
      | activity  | name           | intro                      | course | renderstyle | font           | textalignment |
      | wordcloud | Test wordcloud | Test wordcloud description | C1     | 1           | Georgia, serif | h             |
    And the following "mod_wordcloud > entries" exist:
      | wordcloud      | word          | count |
      | Test wordcloud | Lernen        | 9     |
      | Test wordcloud | Motivation    | 8     |
      | Test wordcloud | Feedback      | 8     |
      | Test wordcloud | Kreativität   | 7     |
      | Test wordcloud | Neugier       | 7     |
      | Test wordcloud | Prüfung       | 6     |
      | Test wordcloud | Vorlesung     | 6     |
      | Test wordcloud | Seminar       | 5     |
      | Test wordcloud | Gruppenarbeit | 5     |
      | Test wordcloud | Diskussion    | 4     |
      | Test wordcloud | Wissen        | 4     |
      | Test wordcloud | Forschung     | 3     |
      | Test wordcloud | Methode       | 3     |
      | Test wordcloud | Theorie       | 2     |
      | Test wordcloud | Praxis        | 2     |
      | Test wordcloud | Reflexion     | 1     |
    When I am on the "Test wordcloud" "wordcloud activity" page logged in as "teacher1"
    Then "#mod-wordcloud-words-canvas" "css_element" should exist
    And the wordcloud canvas should fill its box
