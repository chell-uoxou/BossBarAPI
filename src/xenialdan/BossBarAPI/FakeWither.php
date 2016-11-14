<?php

namespace xenialdan\BossBarAPI;

use pocketmine\entity\Entity;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\level\Location;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\SetEntityLinkPacket;

class FakeWither extends Location{
	public $eid, $text = "", $health;
	public $entityId = 52;

	public function init($eid, $text){
		$this->eid = $eid;
		$this->text = $text;
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->eid;
		$pk->type = $this->entityId;
		$pk->x = $player->x;
		$pk->y = $player->y;
		$pk->z = $player->z;
		$pk->yaw = $player->yaw;
		$pk->pitch = $player->pitch;
		$pk->metadata = [Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG, -1], Entity::DATA_FLAG_SILENT => [Entity::DATA_TYPE_BYTE, 1], Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0.25], Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->text], 
				Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0], Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0]];
		$player->dataPacket($pk);

		#$upk = new SetEntityLinkPacket();
		#$upk->from = $this->eid;
		#$upk->to = 0;
		#$upk->type = SetEntityLinkPacket::TYPE_PASSENGER;
		
		#$player->dataPacket($upk);
		return true;
	}

	public function despawnFrom(Player $player){
		$pk = new RemoveEntityPacket();
		$pk->eid = $this->eid;
		$player->dataPacket($pk);
	}
}