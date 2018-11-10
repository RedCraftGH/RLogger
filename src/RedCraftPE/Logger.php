<?php

namespace RedCraftPE;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use RedCraftPE\task\Log;

class Logger extends PluginBase implements Listener {
  
  public static $instance;

  public function onEnable(): void {
  
    //the usual onEnable stuff will go here:
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    self::$instance = $this;
    
    if (!file_exists($this->getDataFolder() . "logger.yml")) {
      
      @mkdir($this->getDataFolder());
      $this->saveResource("logger.yml");
      $this->logger = new Config($this->getDataFolder() . "logger.yml", Config::YAML);
      $this->logger->set("Logged", []);
    } else {
      
      $this->logger = new Config($this->getDataFolder() . "logger.yml", Config::YAML);
    }
  } 
  public function onDamageByEntity(EntityDamageByEntityEvent $event) {
  
    //start logging here for X seconds, if (log out) kill:
    $entity = $event->getEntity();
    $damager = $event->getDamager();
    $loggedArray = $this->logger->get("Logged", []);
    
    if ($entity instanceof Player && $damager instanceof Player) {
    
      $entityName = $entity->getName();
      $damagerName = $damager->getName();
      
      if (in_array($entityName, $loggedArray) && in_array($damagerName, $loggedArray)) {
    
        return;
      }
      if (in_array($entityName, $loggedArray) && !in_array($damagerName, $loggedArray)) {
        
        $loggedArray[] = $damagerName;
        $this->logger->set("Logged", $loggedArray);
        $this->logger->save();
        $this->getScheduler()->scheduleDelayedTask(new Log($damagerName), 200);
        $damager->sendMessage(TextFormat::RED . "Logging out in the next 10 seconds will kill you!");
        return;
      }
      if (!in_array($entityName, $loggedArray) && in_array($damagerName, $loggedArray)) {
        
        $loggedArray[] = $entityName;
        $this->logger->set("Logged", $loggedArray);
        $this->logger->save();
        $this->getScheduler()->scheduleDelayedTask(new Log($entityName), 200);
        $entity->sendMessage(TextFormat::RED . "Logging out in the next 10 seconds will kill you!");
        return;
      }
      
      $loggedArray[] = $entityName;
      $loggedArray[] = $damagerName;
      $this->logger->set("Logged", $loggedArray);
      $this->logger->save();
      $entity->sendMessage(TextFormat::RED . "Logging out in the next 10 seconds will kill you!");
      $damager->sendMessage(TextFormat::RED . "Logging out in the next 10 seconds will kill you!");
      $this->getScheduler()->scheduleDelayedTask(new Log($entityName), 200);
      $this->getScheduler()->scheduleDelayedTask(new Log($damagerName), 200);
      return;
    }
  }
  public function onLeave(PlayerQuitEvent $event) {
  
    $player = $event->getPlayer();
    $loggedArray = $this->logger->get("Logged", []);
    
    if (in_array($player->getName(), $loggedArray)) {
    
      $player->kill();
      $event->setQuitMessage(TextFormat::WHITE . $player->getName() . " has run away from a fight! And it will cost him his life!");
      $this->logger->removeNested("Logged", $entityName);
      $this->logger->save();
      return;
    }
  }
  public static function getInstance(): self {
  
    return self::$instance;
  }
}
