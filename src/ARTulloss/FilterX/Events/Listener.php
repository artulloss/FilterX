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
use function str_replace;
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
     * @priority HIGH
     */
    public function onChat(PlayerChatEvent $event): void{
        $player = $event->getPlayer();
        $msg = $event->getMessage();
        $handler = $this->plugin->getSessionHandler();
        $handler->passSession(function (Session $session) use ($event, $handler, $player, $msg): void{
            $until = $session->getSoftMutedUntil();
            $silentConfig = $this->config->get('Silent');
            $isFiltered = false;
            $infractions = $this->plugin->checkAllFilters($msg, $isFiltered);
            if($isFiltered) {
                if(!$silentConfig['filter']) {
                    $player->sendMessage(TextFormat::RED . "Please rephrase your sentence!");
                    $event->setCancelled();
                    $this->broadcastToStaff($event);
                } else
                    $this->handleEvent($event);
            } elseif($session->isSoftMuted()) {
                if(!$silentConfig['filter']) {
                    // It is safe to do getSoftMutedUntilHere because $session->isSoftMuted() would return false if it was null
                    $untilStr = Utils::time2str($until, 'ago', '');
                    $event->setCancelled();
                    $player->sendMessage(TextFormat::RED . "You are soft muted! For: $untilStr");
                } else
                    $this->handleEvent($event);
            }
            $session->removeExpiredInfractions($this->config->getNested('Infraction.Expire After'));
            $session->incrementInfractions($infractions);
            $infractions = $session->getInfractions();
            $infractionPunishments = $this->plugin->getInfractionLengths();
            // *These are sorted from highest to lowest severity which is why this code works*
            foreach ($infractionPunishments as $threshold => $punishment) {
                if($infractions >= $threshold) {
                    $session->addToSoftMutedTime($punishment);
                    $session->removeInfractions($threshold);
                    break;
                }
            }
            $until = $session->getSoftMutedUntil();
            if(($until ?? PHP_INT_MAX) < time()) {
                $name = $player->getName();
                $session->setSoftMutedFor(null);
                $this->plugin->getDatabase()->executeChange(Queries::FILTER_DELETE_SOFT_MUTE, ['name' => $player->getName()], function (int $rows) use ($name, $session): void{
                    $this->plugin->getLogger()->info("Soft mute expired for $name. $rows row(s) changed.");
                });
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
    /**
     * @param PlayerChatEvent $event
     */
    private function handleEvent(PlayerChatEvent $event): void{
        $player = $event->getPlayer();
        $event->setRecipients([$player]);
        $this->broadcastToStaff($event);
    }
    /**
     * @param PlayerChatEvent $event
     */
    private function broadcastToStaff(PlayerChatEvent $event): void{
        $staffChat = $this->plugin->getServer()->getPluginManager()->getPlugin('StaffChat');
        if($staffChat !== null)
            $staffChat->pluginBroadcast($this->plugin->getName(), $event->getMessage(), str_replace('%player%', $event->getPlayer()->getName(), $this->config->get('Staff Chat Format')));
    }
}