@api @basic
Feature: Test if site front page actually working

  Scenario: Make sure that site front page works
    Given I am not logged in
    When I visit "/"
    Then print last response
    Then I should see the text "გირჩი"