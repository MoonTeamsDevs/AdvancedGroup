<?php

namespace MoonTeam\AdvancedGroup\provider;

use pocketmine\Player;
use pocketmine\utils\Config;

interface Provider{

    public function getGroups(): array;

    public function addGroup(string $name, bool $default = false);

    public function removeGroup(string $name);

    public function existGroup(string $name):bool;

    public function hasAccount(string|Player $player): bool;

    public function createAccount(string|Player $player);

    public function getDefaultGroup(): string;

    public function savePlayerData(string|Player $player);

    public function saveGroupData(array $groups);

    public function getPlayerGroup(string|Player $player);

    public function getFormatGroup(string $group);

    public function setGroup(string|Player $player, string $group);

    public function setFormat(string $group, string $format);

    public function addGroupPermission(string $group, string $perm);

    public function removeGroupPermission(string $group, string $perm);

    public function addPlayerPermission(string|Player $player, string $perm);

    public function removePlayerPermission(string|Player $player, string $perm);

    public function getGroupPermissions(string $group): array;

    public function getPlayerPermissions(string|Player $player): array;

    public function getPermissions(Player $player): array;

    public function existGroupPermission(string $group, string $permission): bool;

    public function existPlayerPermission(string|Player $player, string $permission): bool;

    public function updateGroupForPlayers(string $groupDelete);

}