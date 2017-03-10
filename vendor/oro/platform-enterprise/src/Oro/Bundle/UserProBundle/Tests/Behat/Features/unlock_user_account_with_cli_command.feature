@not-automated
Feature: Unlock user account with CLI command
  In order to unlock users locked by mistake
  as Administrator
  I should have a possibility to unlock users from console

  Scenario: Feature Background
    Given the following users:
      | Username   | Password  | Role          |
      | mattjohnes | Qwe123qwe | Administrator |

  Scenario: User locks themselves
    Given I am on login page
    When I input incorrect password for 8 times
    Then I should see "Account is locked." error message

  Scenario: User unlock themselves
    Given I open the console
    And go to the instance location
    #command should be corrected
    When I execute "sudo -uorocrmdeployer pgp app/console oro:user:unlock mattjohnes"
    Then I could log in to the system
