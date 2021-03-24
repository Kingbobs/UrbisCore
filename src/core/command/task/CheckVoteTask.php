<?php

declare(strict_types = 1);

namespace core\command\task;

use core\crate\Crate;
use core\item\types\Artifact;
use core\CorePlayer;
use core\translation\Translation;
use core\translation\TranslationException;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use pocketmine\utils\TextFormat;

class CheckVoteTask extends AsyncTask {

    const API_KEY = "6spu1JjFcRJYhrZTDEHZBfPuWpEGuQ4f4Ji";

    const STATS_URL = "https://minecraftpocket-servers.com/api/?object=servers&element=detail&key=" . self::API_KEY;

    const CHECK_URL = "http://minecraftpocket-servers.com/api-vrc/?object=votes&element=claim&key=" . self:: API_KEY . "&username={USERNAME}";

    const POST_URL = "http://minecraftpocket-servers.com/api-vrc/?action=post&object=votes&element=claim&key=" . self:: API_KEY . "&username={USERNAME}";

    const VOTED = "voted";

    const CLAIMED = "claimed";

    /** @var string */
    private $player;

    /**
     * CheckVoteTask constructor.
     *
     * @param string $player
     */
    public function __construct(string $player) {
        $this->player = $player;
    }

    public function onRun() {
        $result = [];
        $get = Internet::getURL(str_replace("{USERNAME}", $this->player, self::CHECK_URL));
        if($get === false) {
            return;
        }
        $get = json_decode($get, true);
        if((!isset($get["voted"])) or (!isset($get["claimed"]))) {
            return;
        }
        $result[self::VOTED] = $get["voted"];
        $result[self::CLAIMED] = $get["claimed"];
        if($get["voted"] === true and $get["claimed"] === false) {
            $post = Internet::postURL(str_replace("{USERNAME}", $this->player, self::POST_URL), []);
            if($post === false) {
                $result = null;
            }
        }
        $this->setResult($result);
    }

    /**
     * @param Server $server
     *
     * @throws TranslationException
     */
    public function onCompletion(Server $server) {
        $player = $server->getPlayer($this->player);
        if((!$player instanceof CorePlayer) or $player->isClosed()) {
            return;
        }
        $result = $this->getResult();
        if(empty($result)) {
            $player->sendMessage(Translation::getMessage("errorOccurred"));
            return;
        }
        $player->setCheckingForVote(false);
        if($result[self::VOTED] === true) {
            if($result[self::CLAIMED] === true) {
                $player->setVoted();
                $player->sendMessage(Translation::getMessage("alreadyVoted"));
                return;
            }
            $player->setVoted();
            $votes = $player->getCore()->getVotes();
            ++$votes;
            $player->getCore()->setVotes($votes);
            $keys = (int)ceil($votes / 100);
            $factor = (100 * $keys) - $votes;
            $keys += 3;
            $server->broadcastMessage(Translation::getMessage("voteBroadcast", [
                "name" => $player->getDisplayName(),
                "votes" => TextFormat::GREEN . $factor,
                "amount" => TextFormat::RED . "x$keys"
            ]));
			$player->addKeys($player->getCore()->getCrateManager()->getCrate(Crate::VOTE), 3);
            $player->addToBalance(150000);
			$randomArtifact = mt_rand(1, 3);
			$randomArtifact2 = mt_rand(1, 3);
			if($randomArtifact == $randomArtifact2){
				$item2 = (new Artifact())->getItemForm()->setCount(1);
				if($player->getInventory()->canAddItem($item2)){
					$player->getInventory()->addItem($item2);
				}
			}
            if($factor <= 0) {
                $item = (new Artifact())->getItemForm()->setCount($keys);
                $player->getCore()->getServer()->broadcastMessage(Translation::getMessage("artifactAll", [
                    "name" => TextFormat::AQUA . "Voting System",
                    "amount" => TextFormat::YELLOW . $keys,
                ]));
                /** @var CorePlayer $player */
                foreach($player->getCore()->getServer()->getOnlinePlayers() as $player) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                }
            }
            return;
        }
        $player->sendMessage(Translation::getMessage("haveNotVoted"));
        $player->setVoted(false);
    }
}