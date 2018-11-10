<?PHP

namespace RedCraftPE\task;

use RedCraftPE\Logger;
use pocketmine\scheduler\Task;

class Log extends Task {

  public function __construct($entityName) {
  
    $this->entityName = $entityName;
  }
  public function onRun(int $tick) : void {
    
    if (in_array($this->entityName, $Logger::getInstance()->logger->get("Logged", []))) {
      $Logger::getInstance()->logger->removeNested("Logged", $this->entityName);
      $Logger::getInstance()->logger->save();
      return;
    } else {
    
      return;
    }
  }
}
