<?php

namespace xenialdan\BossBarAPI;

use pocketmine\network\protocol\DataPacket;

class SetEntityDataPacket extends DataPacket {
	const NETWORK_ID = 0x27;

	public $eid;
	public $metadata;

	public function decode() {

	}

	public function encode() {
		$this->reset();
		$this->putEntityId($this->eid);
		$this->putEntityMetadata($this->metadata);
	}
}
