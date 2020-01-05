<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 8/10/2019
 * Time: 8:37 AM
 */
declare(strict_types=1);

namespace ARTulloss\FilterX\Commands;

use ARTulloss\FilterX\Utils;
use pocketmine\command\CommandSender;
use pocketmine\command\defaults\TellCommand as PMTellCommand;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\lang\TranslationContainer;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Class TellCommand
 * Modified to support /disguise command and most importantly, to allow for filtering of direct messages
 * @package ARTulloss\FilterX\Commands
 */
class TellCommand extends PMTellCommand{
    /**
     * @param \pocketmine\command\CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool
     * @throws \ReflectionException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
        if(!$this->testPermission($sender)){
            return true;
        }

        if(count($args) < 2){
            throw new InvalidCommandSyntaxException();
        }

        $player = Utils::getPlayer(array_shift($args)); // Tweaked to support disguises where the name !== their display name, otherwise this command exposes disguises

        if($player === $sender){
            $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.message.sameTarget"));
            return true;
        }

        if($player instanceof Player){
            $name = $sender instanceof Player ? $sender->getDisplayName() : $sender->getName();
            $msg = "[$name -> {$player->getDisplayName()}] " . implode(" ", $args); // Tweaked? Makes more sense to be the same as above
            if($sender instanceof Player) { // Console can swear
                $event = new PlayerChatEvent($sender, $msg);
                $event->call();
                if(!$event->isCancelled())
                    $sender->sendMessage("[{$sender->getName()} -> {$player->getDisplayName()}] " . implode(" ", $args));
                if($event->getRecipients() !== [$sender]) {
                    $player->sendMessage($msg);
                }
            } else
                $player->sendMessage($msg);
        }else{
            $sender->sendMessage(new TranslationContainer("commands.generic.player.notFound"));
        }

        return true;
    }
}