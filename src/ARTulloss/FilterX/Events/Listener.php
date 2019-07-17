<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 7/10/2019
 * Time: 10:56 PM
 */
declare(strict_types=1);

namespace ARTulloss\FilterX\Events;

use ARTulloss\FilterX\Main;
use ARTulloss\FilterX\Queries\Queries;
use ARTulloss\FilterX\Session\Session;
use ARTulloss\FilterX\Timer;
use ARTulloss\FilterX\Utils;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\Listener as PMListener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;
use Exception;
use pocketmine\utils\TextFormat;
use function time;
use const PHP_INT_MAX;

class Listener implements PMListener {
    /** @var Main $plugin */
    private $plugin;
    /** @var Config $config */
    private $config;
    /**
     * Listener constructor.
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin) {
        $this->plugin = $plugin;
        $this->config = $this->plugin->getConfig();
    }
    /**
     * @param PlayerChatEvent $event
     */
    public function onChat(PlayerChatEvent $event): void{
        $timer = $this->plugin->getTimer();
        if($timer->getState() === Timer::STATE_STOPPED)
            $timer->start();
        $player = $event->getPlayer();
        $msg = $event->getMessage();
        $handler = $this->plugin->getSessionHandler();
        $handler->passSession(function (Session $session) use ($event, $handler, $player, $timer, $msg): void{
            $until = $session->getSoftMutedUntil();
            $filteredWords = $this->config->get('Filtered Words', []);
            $hasFilteredWord = Utils::striExists($filteredWords, $msg);
            $silentConfig = $this->config->get('Silent');
            $infractionConfig = $this->config->get('Infraction');
            if($hasFilteredWord) {
            //    echo "\nSOFT FILTERED MESSAGE\n";
            //    var_dump($infractionConfig);
                $session->incrementInfractions(($infractionConfig['Mode'] === 2) ? Utils::array_substr_count($filteredWords, $msg) : 1);
                if(!$silentConfig['filter']) {
                    $player->sendMessage(TextFormat::RED . "Please rephrase your sentence!");
                    $event->setCancelled();
                } else
                    $event->setRecipients([$player]);
            } elseif($session->isSoftMuted()) {
            //    echo "\nSOFT MUTED MESSAGE\n";
                if(!$silentConfig['filter']) {
                    // It is safe to do getSoftMutedUntilHere because $session->isSoftMuted() would return false if it was null
                //    var_dump($until);
                    $untilStr = Utils::time2str($until, 'ago', '');
                    $event->setCancelled();
                    $player->sendMessage(TextFormat::RED . "You are soft muted! For: $untilStr");
                } else
                    $event->setRecipients([$player]);
            }
            $infractions = $session->getInfractions();
            $infractionPunishments = $this->plugin->getInfractionLengths();
            // *These are sorted from highest to lowest severity which is why this code works*
            foreach ($infractionPunishments as $threshold => $punishment) {
                if(!$session->hasBeenPunishedAtThreshold($threshold) && $infractions >= $threshold) {
                    $session->addToSoftMutedTime($punishment);
                    $session->addToAlreadyPunishedInfraction($threshold);
                    $session->resetInfractions();
                    break;
                }
            }
            $until = $session->getSoftMutedUntil();
        //    echo "\nUNTIL\n";
        //    var_dump($until);
        //    echo "\nNOW\n";
        //    var_dump(time());
            if(($until ?? PHP_INT_MAX) < time()) {
                $name = $player->getName();
                $session->setSoftMutedFor(null);
                $this->plugin->getDatabase()->executeChange(Queries::FILTER_DELETE_SOFT_MUTE, ['name' => $player->getName()], function (int $rows) use ($name, $session): void{
                    $this->plugin->getLogger()->info("Soft mute expired for $name. $rows row(s) changed.");
                    $session->resetPunishedAtThreshold();
                });
            }

            // Reset infractions with timer

            if($timer->isDone()) {
                foreach ((array)$handler->getAllSessions() as $session) {
                    $session->resetInfractions();
                    $session->resetPunishedAtThreshold();
                }
                $timer->start();
            }
        }, $player, $msg);
    }
    /**
     * @param PlayerQuitEvent $event
     * @throws Exception
     */
    public function onQuit(PlayerQuitEvent $event): void{
        $player = $event->getPlayer();
        $handler = $this->plugin->getSessionHandler();
        $session = $handler->getSession($player);
        if($session !== null && $session->isSoftMuted()) {
            $this->plugin->getDatabase()->executeInsert(Queries::FILTER_UPSERT_SOFT_MUTES, [
                'name' => $player->getName(),
                'until' => $session->getSoftMutedUntil()
            ]);
        }
        $handler->deleteSession($player);
    }
}