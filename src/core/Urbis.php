<?php

declare(strict_types=1);

namespace core;

use core\announcement\AnnouncementManager;
use core\area\AreaManager;
use core\auctionhouse\AuctionhouseManager;
use core\cannons\CannonManager;
use core\clearlag\ClearLagManager;
use core\combat\boss\BossException;
use core\combat\boss\tasks\SpawnRandomBoss;
use core\combat\CombatManager;
use core\command\CommandManager;
use core\command\task\CheckVoteTask;
use core\crate\CrateManager;
use core\custompotion\CustomPotionListener;
use core\entity\EntityManager;
use core\envoy\EnvoyManager;
use core\event\EventManager;
use core\faction\FactionManager;
use core\gamble\GambleManager;
use core\item\ItemManager;
use core\bounty\BountyManager;
use core\classes\ClassManager;
use core\kit\KitException;
use core\level\LevelManager;
use core\mask\MaskManager;
use core\price\PriceManager;
use core\provider\MySQLProvider;
use core\quest\QuestManager;
use core\rank\RankManager;
use core\tag\TagManager;
use core\trade\TradeManager;
use core\provider\Session;
use core\traits\ManagerLoader;
use core\update\UpdateManager;
use core\watchdog\WatchdogManager;
use core\kit\KitManager;
use core\libs\muqsit\invmenu\InvMenuHandler;
use Exception;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\utils\Config;
use pocketmine\utils\Internet;
use ReflectionException;
use core\outpost\OutpostManager;

class Urbis extends PluginBase
{
    use ManagerLoader;

    static $debug = true
	       
	    
    private static $STARTUP_WORLDS = ["wild", "pvp", "bossarena"];
    private static $DIRECTORIES = ["factions", "players", "kits"];
    private static $SERVER_NAME = "§l§cMuilti§eCraft§r §7- §l§b Op Factions§r§7";

    const MESSAGES = [
        "§l§6Multi§6Craft§r §7- §l§bSERVER RESET!§r§7",
        "§l§6Multi§6Craft§r §7- §l§bSERVER RELEASED!§r§7",
        "§l§6Multi§6Craft§r §7- §l§bSEASON I§r§7",
    ];

    const BORDER = 20000;

    const WORLD = "260";

    const EXTRA_SLOTS = 20;
  //  public static $STARTUP_WORLDS = ["FactionsWorld", "boss", "koth"];
   // public static $DIRECTORIES = ["factions", "players", "kits", "scoreboard", "cape"];
   // public static $SERVER_NAME = "§7UrbisPE Factions";

    //const MESSAGES = [
      //  "UrbisMC Factions",
    //];

    //const BORDER        = 22000;
    //const EXTRA_SLOTS   = 70;
    //const WORLD         = "FactionsWorld";

    public static $instance;
    public static $nbtWriter;

    private $areaManager;
    private $announcementManager;
    private $commandManager;
    private $watchdogManager;
    private $rankManager;
    private $factionManager;
    private $entityManager;
    private $combatManager;
    /** @var KitManager */
    private $kitManager;
    private $levelManager;
    private $updateManager;
    private $itemManager;
    private $crateManager;
    private $priceManager;
    private $envoyManager;
    private $questManager;
    private $tradeManager;
    private $gambleManager;
    private $eventManager;
    private $cannonManager;
    private $maskManager;
    private $clearlagManager;
    private $tagManager;
    private $bountyManager;
    private $classManager;
    private $auctionManager;
    private $outpostManager;

    private $provider;

    private $votes = 0;
    private $rewards = [];
    private $inbox = [];
    public $sessions = [];

    /** @var Config */
    public $capeData;

    /** @var Config */
    public $bossData;

    /** @var string */
    public $scoreBoardTitle;

    /** @var array */
    public $scoreBoardLines = [];


    /** @var array */
    public $pvpScoreBoardLines = [];

    function onLoad(): void
    {
        self::$nbtWriter = new BigEndianNBTStream();
        self::$instance = $this;
        $this->getServer()->getNetwork()->setName(self::$SERVER_NAME);
    }

    static function log(string $message): void
    {
        self::$instance->getLogger()->info($message);
    }

    static function debug(string $message): void
    {
        if (self::$debug) self::log("[DEBUG] " . $message);
    }

    function onEnable()
    {
        @mkdir($this->getDataFolder() . "factions");
        @mkdir($this->getDataFolder() . "players");
        if (!InvMenuHandler::isRegistered()) InvMenuHandler::register($this);
        $this->initFolders();
        $this->saveResources();
        $this->loadWorlds();

        // Setup a data provider first. Disable plugin, if failed
        if(!$this->initProviders()) return;

        if(!$this->initiateManagers()) {
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        } else {
            self::debug("Successfully initiated all managers");
        }

        $this->initCommands();
        $this->initEvents();
        $this->initTasks();
        $this->bossData = new Config($this->getDataFolder() . "npc/boss/argus.json");
        $scoreBoardData = new Config($this->getDataFolder() . "scoreboard/scoreboard.yml");
        $this->capeData = new Config($this->getDataFolder() . "cape.yml");
        $this->scoreBoardTitle = $scoreBoardData->get("title");
        $this->scoreBoardLines = $scoreBoardData->get("statshud");
        $this->pvpScoreBoardLines = $scoreBoardData->get("pvphud");
        $get = Internet::getURL(CheckVoteTask::STATS_URL);
        if ($get !== false) {
            $get = json_decode($get, true);
            if (isset($get["votes"])) {
                $this->votes = (int)$get["votes"];
            }
        }
    }

    public function onDisable(): void{
        $this->getKitManager()->save();
        foreach($this->sessions as $name => $session){
            if($session instanceof Session){
                $session->save();
            }
		}
		$this->getServer()->shutdown();
    }

    function initCommands(): void
    {

    }

    function initEvents(): void
    {
        $this->getServer()->getPluginManager()->registerEvents(new UrbisListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new CustomPotionListener(), $this);
	$this->getServer()->getPluginManager()->registerEvents(new BountyManager(), $this);
	$this->getServer()->getPluginManager()->registerEvents(new ClassManager(), $this);
    }

    function initTasks(): void
    {
        $this->getScheduler()->scheduleRepeatingTask(new SpawnRandomBoss(), 20);
    }

    static function encodeItem(Item $item): string
    {
        return self::$nbtWriter->writeCompressed($item->nbtSerialize());
    }

    static function decodeItem(string $compression): Item
    {
        $tag = self::$nbtWriter->readCompressed($compression);
        if (!$tag instanceof CompoundTag) throw new PluginException("Expected a CompoundTag, got " . get_class($tag));

        return Item::nbtDeserialize($tag);
    }

    static function encodeInventory(Inventory $inventory, $items = []): string
    {
        foreach ($inventory->getContents() as $item) {
            $items = $item->nbtSerialize();
        }

        return self::$nbtWriter->writeCompressed(new CompoundTag("Content", [new ListTag("Items", $items)]));
    }

    static function decodeInventory(string $compression): array
    {
        if (empty($compression)) {
            return [];
        }
        $tag = self::$nbtWriter->readCompressed($compression);
        if (!$tag instanceof CompoundTag) throw new PluginException("Expected a CompoundTag, got " . get_class($tag));

        $content = [];
        foreach ($tag->getListTag("Items")->getValue() as $item) {
            $content[] = Item::nbtDeserialize($item);
        }
        return $content;
    }

    function initFolders(): void
    {
        foreach (self::$DIRECTORIES as $DIRECTORY) {
            @mkdir($this->getDataFolder() . $DIRECTORY);
        }
        self::debug("Successfully initiated all directories.");
    }

    function saveResources(): void
    {
        @mkdir($this->getDataFolder() . "/npc");
        @mkdir($this->getDataFolder() . "/npc/skins");

        foreach ($this->getResources() as $resource => $_) {
            $this->saveResource($resource);
        }

        self::debug("Successfully saved all resources.");
    }

    function loadWorlds(): void
    {
        foreach (self::$STARTUP_WORLDS as $STARTUP_WORLD) {
            $this->getServer()->loadLevel($STARTUP_WORLD);
        }
		self::debug("Successfully loaded all worlds.");
		self::debug("Successfully loaded all variables / sections");
    }

    function getVotes(): int
    {
        return $this->votes;
    }

    function setVotes(int $amount): void
    {
        $this->votes = $amount;
    }

    static function getInstance(): Urbis
    {
        return self::$instance;
    }

    function getMySQLProvider(): MySQLProvider
    {
        return $this->provider;
    }

    function getAnnouncementManager(): AnnouncementManager
    {
        return $this->announcementManager;
    }

    function getAreaManager(): AreaManager
    {
        return $this->areaManager;
    }

    function getCommandManager(): CommandManager
    {
        return $this->commandManager;
    }

    function getWatchdogManager(): WatchdogManager
    {
        return $this->watchdogManager;
    }

    function getRankManager(): RankManager
    {
        return $this->rankManager;
    }

    function getFactionManager(): FactionManager
    {
        return $this->factionManager;
    }

    function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    function getCombatManager(): CombatManager
    {
        return $this->combatManager;
    }

    function getKitManager(): KitManager
    {
        return $this->kitManager;
    }

    function getLevelManager(): LevelManager
    {
        return $this->levelManager;
    }

    function getUpdateManager(): UpdateManager
    {
        return $this->updateManager;
    }

    function getItemManager(): ItemManager
    {
        return $this->itemManager;
    }

    function getCrateManager(): CrateManager
    {
        return $this->crateManager;
    }

    function getPriceManager(): PriceManager
    {
        return $this->priceManager;
    }

    function getEnvoyManager(): EnvoyManager
    {
        return $this->envoyManager;
    }

    function getQuestManager(): QuestManager
    {
        return $this->questManager;
    }

    function getTradeManager(): TradeManager
    {
        return $this->tradeManager;
    }

    function getGambleManager(): GambleManager
    {
        return $this->gambleManager;
    }

    function getEventManager(): EventManager
    {
        return $this->eventManager;
    }

    function getMaskManager(): MaskManager
    {
        return $this->maskManager;
    }

    function getOutpostManager(): OutpostManager
    {
        return $this->outpostManager;
    }

    function getClearlagManager(): ClearLagManager
    {
        return $this->clearlagManager;
    }

    function getTagManager(): TagManager
    {
        return $this->tagManager;
    }

    function getBountyManager(): BountyManager
    {
	return $this->bountyManager;
    }

	function getClassManager(): ClassManager
	{
		return $this->classManager;
	}

    function getSession()
    {
        return $this->sessions;
    }

    function setManager(string $name, $obj) : void {
        $this->{$name} = $obj;
    }

    private function initProviders() : bool {
        try {
            $this->provider = new MySQLProvider($this);
            if ($this->provider->getDatabase()->connect_errno) {
                $this->getServer()->getPluginManager()->disablePlugin($this);
                return false;
            }
        } catch (\Exception $e) {
            $this->getLogger()->error("MySQL Error: " . $e->getMessage());
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return false;
        }
        return true;
    }

}
