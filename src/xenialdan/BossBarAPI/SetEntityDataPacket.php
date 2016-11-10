<?php

namespace xenialdan\BossBarAPI;

use pocketmine\utils\Binary;

class SetEntityDataPacket extends DataPacket{
	const NETWORK_ID = \pocketmine\network\protocol\Info::SET_ENTITY_DATA_PACKET??0x26;
	public $eid;
	public $metadata;

	public function decode(){}

	public function encode(){
		$this->reset();
		$this->putEntityId($this->eid);
		$meta = Binary::writeMetadata($this->metadata);
		$this->put($meta);
	}
}
