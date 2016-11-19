<?php

namespace xenialdan\BossBarAPI;

use pocketmine\network\protocol\DataPacket;

class BossEventPacket extends DataPacket{
	const NETWORK_ID = 0x4a;
	public $eid;
	public $state;

	public function decode(){
		$this->state = $this->getUnsignedVarInt();
		$this->eid = $this->getUUID();
		// $this->ka2 = $this->getString();
		// $this->ka3 = $this->getFloat();
		// $this->ka4 = $this->getShort();
		// $this->ka5 = $this->getUnsignedVarInt();
		// print $ka2 . '|' . $ka3 . '|' . $ka4 . '|' . $ka5 . '\n';
		print '|' . $this->eid . '|' . $this->state . PHP_EOL;
	}

	public function encode(){
		$this->reset();
		$this->putEntityId($this->eid);
		$this->putUnsignedVarInt($this->state);
	}
}