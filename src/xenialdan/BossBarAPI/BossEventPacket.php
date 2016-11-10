<?php

namespace xenialdan\BossBarAPI;

use pocketmine\network\protocol\DataPacket;
// include <rules/DataPacket.h>
class BossEventPacket extends DataPacket{
	const NETWORK_ID = \pocketmine\network\protocol\Info::BOSS_EVENT_PACKET??0x4a; // set if not exists
	public $eid;
	public $state;

	public function decode(){
		$this->eid = $this->getEntityId();
		$this->state = $this->getUnsignedVarInt();
	}

	public function encode(){
		$this->reset();
		$this->putEntityId($this->eid);
		$this->putUnsignedVarInt($this->state);
	}
}