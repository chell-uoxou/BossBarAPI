<?php

namespace xenialdan\BossBarAPI;

use pocketmine\entity\Entity;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\BossEventPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\SetEntityDataPacket;
use pocketmine\network\protocol\UpdateAttributesPacket;
use pocketmine\Player;
use pocketmine\Server;

class API{

	/**
	 * Sends the text to all players
	 *
	 * @param Player[] $players
	 * To who to send
	 * @param string $title
	 * The title of the boss bar
	 * @param null|int $ticks
	 * How long it displays
	 * @return int EntityID NEEDED FOR CHANGING TEXT/PERCENTAGE! | null (No Players)
	 */
	public static function addBossBar($players, string $title, $ticks = null){
		if (empty($players)) return null;

		$eid = Entity::$entityCount++;

		$packet = new AddEntityPacket();
		$packet->entityRuntimeId = $eid;
		$packet->type = 52;
		$packet->yaw = 0;
		$packet->pitch = 0;
		$packet->speedX = 0;
		$packet->speedY = 0;
		$packet->speedZ = 0;
		$packet->metadata = [Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG, -1], Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 0 ^ 1 << Entity::DATA_FLAG_SILENT ^ 1 << Entity::DATA_FLAG_INVISIBLE ^ 1 << Entity::DATA_FLAG_NO_AI], Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $title], Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0], Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0]];
		foreach ($players as $player){
			$pk = clone $packet;
			$pk->x = $player->x;
			$pk->y = $player->y - 28;
			$pk->z = $player->z;
			$player->dataPacket($pk);
		}

		$bpk = new BossEventPacket(); // This updates the bar
		$bpk->bossEid = $eid;
		$bpk->eventType = $bpk->type;
		$bpk->title = $title;
		$bpk->healthPercent = 1;
		$bpk->unknownShort = 0;//TODO: remove. Shoghi deleted that unneeded mess that was copy-pasted from MC-JAVA
		$bpk->color = 0;//TODO: remove. Shoghi deleted that unneeded mess that was copy-pasted from MC-JAVA
		$bpk->overlay = 0;//TODO: remove. Shoghi deleted that unneeded mess that was copy-pasted from MC-JAVA
		$bpk->playerEid = 0;//TODO TEST!!!
		Server::getInstance()->broadcastPacket($players, $bpk);

		return $eid; // TODO: return EID from bosseventpacket?
	}

	/**
	 * Sends the text to one player
	 *
	 * @param Player $player
	 * @param int $eid
	 * The EID of an existing fake wither
	 * @param string $title
	 * The title of the boss bar
	 * @param null|int $ticks
	 * How long it displays
	 * @internal param Player $players To who to send* To who to send
	 */
	public static function sendBossBarToPlayer(Player $player, int $eid, string $title, $ticks = null){
		$packet = new AddEntityPacket();
		$packet->entityRuntimeId = $eid;
		$packet->type = 52;
		$packet->yaw = 0;
		$packet->pitch = 0;
		$packet->speedX = 0;
		$packet->speedY = 0;
		$packet->speedZ = 0;
		$packet->metadata = [Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG, -1], Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 0 ^ 1 << Entity::DATA_FLAG_SILENT ^ 1 << Entity::DATA_FLAG_INVISIBLE ^ 1 << Entity::DATA_FLAG_NO_AI], Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $title], Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0], Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0]];
		$packet->x = $player->x;
		$packet->y = $player->y - 28;
		$packet->z = $player->z;
		$player->dataPacket($packet);

		$bpk = new BossEventPacket(); // This updates the bar. According to shoghi this should not even be needed, but #blameshoghi, it doesn't update without
		$bpk->bossEid = $eid;
		$bpk->eventType = $bpk->type;
		$bpk->title = $title;
		$bpk->healthPercent = 1;
		$bpk->unknownShort = 0;//TODO: remove. Shoghi deleted that unneeded mess that was copy-pasted from MC-JAVA
		$bpk->color = 0;//TODO: remove. Shoghi deleted that unneeded mess that was copy-pasted from MC-JAVA
		$bpk->overlay = 0;//TODO: remove. Shoghi deleted that unneeded mess that was copy-pasted from MC-JAVA
		$bpk->playerEid = 0;//TODO TEST!!!
		$player->dataPacket($bpk);
	}

	/**
	 * Sets how many % the bar is full by EID
	 *
	 * @param int $percentage
	 * 0-100
	 * @param int $eid
	 * @param array $players
	 * If empty this will default to Server::getInstance()->getOnlinePlayers()
	 */
	public static function setPercentage(int $percentage, int $eid, $players = []){
		if (empty($players)) $players = Server::getInstance()->getOnlinePlayers();
		if (!count($players) > 0) return;

		$upk = new UpdateAttributesPacket(); // Change health of fake wither -> bar progress
		$upk->entries[] = new BossBarValues(1, 600, max(1, min([$percentage, 100])) / 100 * 600, 'minecraft:health'); // Ensures that the number is between 1 and 100; //Blame mojang, Ender Dragon seems to die on health 1
		$upk->entityRuntimeId = $eid;
		Server::getInstance()->broadcastPacket($players, $upk);

		$bpk = new BossEventPacket(); // This updates the bar
		$bpk->bossEid = $eid;
		$bpk->eventType = $bpk->type;
		$bpk->title = ""; //We can't get this -.-
		$bpk->healthPercent = $percentage / 100;
		$bpk->unknownShort = 0;//TODO: remove. Shoghi deleted that unneeded mess that was copy-pasted from MC-JAVA
		$bpk->color = 0;//TODO: remove. Shoghi deleted that unneeded mess that was copy-pasted from MC-JAVA
		$bpk->overlay = 0;//TODO: remove. Shoghi deleted that unneeded mess that was copy-pasted from MC-JAVA
		$bpk->playerEid = 0;//TODO TEST!!!
		Server::getInstance()->broadcastPacket($players, $bpk);
	}

	/**
	 * Sets the BossBar title by EID
	 *
	 * @param string $title
	 * @param int $eid
	 * @param Player[] $players
	 */
	public static function setTitle(string $title, int $eid, $players = []){
		if (!count(Server::getInstance()->getOnlinePlayers()) > 0) return;

		$npk = new SetEntityDataPacket(); // change name of fake wither -> bar text
		$npk->metadata = [Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $title]];
		$npk->entityRuntimeId = $eid;
		Server::getInstance()->broadcastPacket($players, $npk);

		$bpk = new BossEventPacket(); // This updates the bar
		$bpk->bossEid = $eid;
		$bpk->eventType = $bpk->type;
		$bpk->title = $title;
		$bpk->healthPercent = 1;
		$bpk->unknownShort = 0;//TODO: remove. Shoghi deleted that unneeded mess that was copy-pasted from MC-JAVA
		$bpk->color = 0;//TODO: remove. Shoghi deleted that unneeded mess that was copy-pasted from MC-JAVA
		$bpk->overlay = 0;//TODO: remove. Shoghi deleted that unneeded mess that was copy-pasted from MC-JAVA
		$bpk->playerEid = 0;//TODO TEST!!!
		Server::getInstance()->broadcastPacket($players, $bpk);
	}

	/**
	 * Remove BossBar from players by EID
	 *
	 * @param Player[] $players
	 * @param int $eid
	 * @return boolean removed
	 */
	public static function removeBossBar($players, int $eid){
		if (empty($players)) return false;

		$pk = new RemoveEntityPacket();
		$pk->entityUniqueId = $eid;
		Server::getInstance()->broadcastPacket($players, $pk);
		return true;
	}
}