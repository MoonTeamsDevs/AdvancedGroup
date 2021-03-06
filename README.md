# AdvancedGroup

- Group plugin allowing easy permission dispersal. Possibility to add permissions to a player eventually. **Available only with PocketMine-MP version 4.**

# Coming soon

- Affiliate a name tag to a group.
- Added SQL provider. (uses SQLITE3)

# Feature

| Feature                                            | AdvancedGroup | PurePerms |
|----------------------------------------------------|---------------|-----------|
| MySQL & SQLITE3 Provider                           | ✅             | ✅         |
| YAML & JSON Provider                               | ✅             | ✅         |
| Cache                                              | ✅             | ❌         |
| Dependent on an additional plugin to work properly | ❌             | ✅         |
| Multi Language Support                             | ✅             | ✅         |
| Extension system/add-ons                           | ✅             | ✅         |
| Uses little perfomance                             | ✅             | ❌         |
| PM4 Support                             | ✅             | ❌         |

# List of available orders

| Command Name   	| Default Op 	| Description                                             	|
|----------------	|------------	|---------------------------------------------------------	|
| `/addgroup`    	| ✅          	| Allows you to add a new group.                          	|
| `/removegroup` 	| ✅          	| Allows you to delete a group.                           	|
| `/groups`      	| ✅          	| Allows you to see the existing groups.                  	|
| `/setgroup`    	| ✅          	| Allows you to define a player's group.                  	|
| `/setformat`   	| ✅          	| Allows you to redefine the format of a group.           	|
| `/addgperm`     	| ✅          	| Allows you to add a permission to a group.              	|
| `/removegperm`  	| ✅          	| Allows you to remove a permission to a group.           	|
| `/addpperm`     	| ✅          	| Allows you to add a permission to a player.             	|
| `/removepperm`  	| ✅          	| Allows you to remove a permission from a player.        	|
| `/listgperms`   	| ✅          	| Allows you to see the list of permissions for a group.  	|
| `/listpperms`   	| ✅          	| Allows you to see the list of permissions for a player. 	|

# Support extension

| Plugin Name         	| Availability 	| Argument available                                            	|
|---------------------	|--------------	|---------------------------------------------------------------	|
| [`SimpleFaction`](https://github.com/AyzrixYTB/SimpleFaction) 	| ✅            	| {faction_name} {faction_power} {faction_rank} {faction_money} 	|
| [`FactionsPro`](https://github.com/AyzrixYTB/SimpleFaction)   	| ✅            	| {faction_name} {faction_power}                                	|
| [`PiggyFactions`](https://github.com/DaPigGuy/PiggyFactions/tree/master) 	| ✅            	| {faction_name} {faction_power} {faction_rank}             |
| [`EconomyAPI`](https://github.com/poggit-orphanage/EconomyS/tree/master/EconomyAPI)     	| ✅            	| {money}                                   |
| [`RedSkyBlock`](https://github.com/RedCraftGH/RedSkyBlock/tree/master)     	| ✅            	| {island_members} {island_rank} {island_size} {island_value} {island_locked_status}  |
| [`SkyBlock`](https://github.com/andresbytes/SkyBlock/tree/stable)     	| ✅            	| {island_blocks} {island_members} {island_rank} {island_size}|

# Credit

- This plugin uses some function of another plugin called [PurePerms](https://github.com/poggit-orphanage/PurePerms/tree/master) made by **@poggit-orphanage**.
- The plugin is also inspired by the extension system made by **@AyzrixYTB** on the [Scoreboard](https://github.com/AyzrixYTB/Scoreboard) plugin.
