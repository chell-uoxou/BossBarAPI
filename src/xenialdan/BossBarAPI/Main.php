<?php

/*
 * BossBar
 * A plugin by XenialDan aka thebigsmileXD
 * http://github.com/thebigsmileXD/BossBarAPI
 * Sending the Bossbar independ from the Server software
 */
namespace xenialdan\BossBarAPI;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\protocol\SetEntityDataPacket;
use pocketmine\entity\Entity;

class Main extends PluginBase implements Listener{
	public $eid = [], $i = 0;

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getNetwork()->registerPacket(BossEventPacket::NETWORK_ID, BossEventPacket::class);
		$this->getServer()->getNetwork()->registerPacket(UpdateAttributesPacket::NETWORK_ID, UpdateAttributesPacket::class);
		$this->getServer()->getNetwork()->registerPacket(SetEntityDataPacket::NETWORK_ID, SetEntityDataPacket::class);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new SendTask($this), 20);
	}

	public function onJoin(PlayerJoinEvent $ev){
		$this->spawnBossBar($ev->getPlayer());
	}

	public function spawnBossBar(Player $player){
		$fakeboss = new FakeWither();
		$fakeboss->init();
		$fakeboss->spawnTo($player);
		// $this->eid[$player->getName()] = $fakeboss->eid;
	}

	public function sendBossBar(){
		if(count($this->getServer()->getOnlinePlayers()) > 0) $this->i < 100?$this->i++:$this->i = 0;
		else return;
		$eid = 1000; /* $this->eid[$player->getName()]; */ // TODO: fix
		
		$upk = new UpdateAttributesPacket(); // Change health of fake wither -> bar progress
		$upk->entries[] = new BossBarValues(0, 300, max(1, min([$this->i, 100])) / 100 * 300, 'minecraft:health'); // Ensures that the number is between 0 and 100;
		$upk->entityId = $eid;
		$this->getServer()->broadcastPacket($this->getServer()->getOnlinePlayers(), $upk);
		
		$npk = new SetEntityDataPacket(); // change name of fake wither -> bar text
		$npk->metadata = [Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, sprintf('Percentage: %s', $this->i)]];
		$npk->eid = $eid;
		$this->getServer()->broadcastPacket($this->getServer()->getOnlinePlayers(), $npk);
		
		$bpk = new BossEventPacket(); // TODO: check if can be removed
		$bpk->eid = $eid;
		$bpk->state = 0;
		$this->getServer()->broadcastPacket($this->getServer()->getOnlinePlayers(), $bpk);
	}
}