@filters @listview
Feature: Filters & list views

	@configuration @asmonitor
	@bug-7012 @todo
	Scenario: Service multi-delete
		Given I have these hosts:
			| host_name     |
			| linux-server1 |
		And I have these services:
			| service_description | host_name     | check_command   | notifications_enabled | active_checks_enabled |
			| System Load         | linux-server1 | check_nrpe!load | 1                     | 1                     |
			| PING                | linux-server1   | check_ping      | 1                     | 0                     |
		And I have activated the configuration
		And I'm on the list view for query "[services] state != 200 and acknowledged = 0"
		Then I should see "System Load"
		And I should see "PING"
		When I check "select_all"
		And I click "Send multi action"
		And I select "Delete services" from "multi_action"
		And I click "Submit"
		Then I should be on the Configure page
		Then I should see "There are 2 changes to 2 service objects" within frame "iframe"
		When I click "More info" within frame "iframe"
		Then I should see "Deleted service object linux-server1;System Load" within frame "iframe"
		And I should see "Deleted service object linux-server1;PING" within frame "iframe"
		Then I should see button "Save objects I have changed" within frame "iframe"
		And I should see button "Save everything" within frame "iframe"
		And I click button "Save objects I have changed" within frame "iframe"
		Then I should see "Preflight configuration turned out ok." within frame "iframe"


	@configuration @asmonitor
	Scenario: List hosts
		Given I have these hosts:
			| host_name |
			| linux-server1 |
			| linux-server2 |
			| linux-server3 |
			| linux-server4 |
			| linux-server5 |
		And I have these services:
			| service_description | host_name		| check_command	|
			| PING                | linux-server1   | check_ping	|
			| PING                | linux-server2   | check_ping	|
			| PING                | linux-server3   | check_ping	|
			| PING                | linux-server4   | check_ping	|
			| PING                | linux-server5   | check_ping	|
		And I have activated the configuration
		And I'm on the list view for query "[hosts] all"
		Then I should see the configured hosts

	@configuration @asmonitor
	Scenario: List hosts
		Given I have these hosts:
			| host_name |
			| linux-server1 |
			| linux-server2 |
			| linux-server3 |
			| linux-server4 |
			| linux-server5 |
		And I have these services:
			| service_description | host_name		| check_command	|
			| PING                | linux-server1   | check_ping	|
			| PING                | linux-server2   | check_ping	|
			| PING                | linux-server3   | check_ping	|
			| PING                | linux-server4   | check_ping	|
			| PING                | linux-server5   | check_ping	|
		And I have activated the configuration
		And I'm on the list view for query "[services] all"
		Then I should see the configured services
		And I should see the configured hosts


	@configuration @asmonitor
	Scenario: List hosts
		Given I have these hosts:
			| host_name |
			| linux-server1 |
			| linux-server2 |
		And I have these services:
			| service_description | host_name		| check_command	|
			| PING                | linux-server1   | check_ping	|
			| PING                | linux-server2   | check_ping	|
		And I have activated the configuration
		And I'm on the list view for query "[services] all"
		Then I should see the configured services
		And I should see "linux-server1"
		And I should see "linux-server2"


	@configuration @asmonitor
	Scenario: List services with granular filter
		Ensure that filters work even when we specify more limiting
		filters.

		Given I have these hosts:
			| host_name |
			| linux-server1 |
			| linux-server2 |
		And I have these services:
			| service_description | host_name     | check_command   | notifications_enabled | active_checks_enabled |
			| PING                | linux-server1   | check_ping      | 1                     | 0                     |
			| PING                | linux-server2   | check_ping      | 0                     | 1                     |
		And I have activated the configuration
		And I'm on the list view for query "[services] active_checks_enabled = 0 and notifications_enabled = 1"
		And I should see "PING"
		And I should see "linux-server1"
		And I shouldn't see "linux-server2"

	@configuration @asmonitor @case-653
	Scenario: Service detail listing column sorting
		Ensure that it is possible to sort by the columns in the listing.
		Sort by description.

		Given I have these hosts:
			| host_name |
			| linux-server1 |
		And I have these services:
			| service_description	| host_name		| check_command |
			| A-service				| linux-server1 | check_ping	|
			| B-service				| linux-server1 | check_ping	|
			| C-service				| linux-server1 | check_ping	|
			| D-service				| linux-server1 | check_ping	|
		And I have activated the configuration
		Given I am on the Service details page
		When I sort the filter result table by "description"
		Then The first row of the filter result table should contain "A-service"
		And The last row of the filter result table should contain "D-service"
		When I sort the filter result table by "description"
		Then The first row of the filter result table should contain "D-service"
		And The last row of the filter result table should contain "A-service"


	@configuration @asmonitor @case-653
	Scenario: Service detail listing column sorting
		Ensure that it is possible to sort by the columns in the listing.
		Sort by last checked.

		Given I have these hosts:
			| host_name |
			| linux-server1 |
		And I have these services:
			| service_description	| host_name		| check_command |
			| A-service				| linux-server1 | check_ping	|
			| B-service				| linux-server1 | check_ping	|
			| C-service				| linux-server1 | check_ping	|
			| D-service				| linux-server1 | check_ping	|
		And I have activated the configuration
		Given I have submitted a passive service check result "linux-server1;C-service;0;some output"
		And I am on the Service details page
		When I sort the filter result table by "last_check"
		Then The last row of the filter result table should contain "C-service"
		When I sort the filter result table by "last_check"
		And The first row of the filter result table should contain "C-service"

	@configuration @asmonitor @case-653
	Scenario: Service detail listing column sorting
		Ensure that it is possible to sort by the columns in the listing.
		Sort by duration.

		Given I have these hosts:
			| host_name |
			| linux-server1 |
		And I have these services:
			| service_description	| host_name		| check_command |
			| A-service				| linux-server1 | check_ping	|
			| B-service				| linux-server1 | check_ping	|
			| C-service				| linux-server1 | check_ping	|
			| D-service				| linux-server1 | check_ping	|
		And I have activated the configuration
		Given I have submitted a passive service check result "linux-server1;B-service;0;some output"
		And I am on the Service details page
		When I sort the filter result table by "duration"
		Then The first row of the filter result table should contain "B-service"
		When I sort the filter result table by "duration"
		And The last row of the filter result table should contain "B-service"