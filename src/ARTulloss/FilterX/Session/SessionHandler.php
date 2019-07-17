<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 7/10/2019
 * Time: 11:08 PM
 */
declare(strict_types=1);

namespace ARTulloss\FilterX\Session;

use ARTulloss\FilterX\Main;
use ARTulloss\FilterX\Queries\Queries;
use ARTulloss\FilterX\Utils;
use pocketmine\Player;
use pocketmine\utils\Utils as PMUtils;
use Closure;
use function time;
use function var_dump;

class SessionHandler {
    /** @var Main $plugin */
    private $plugin;
    /**
     * SessionHandler constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }
    /** @var Session[] $sessions */
    private $sessions;
    /**
     * All in one create and update the session if it already exists
     * @param Closure $passTo
     * @param Player $player
     * @param string $lastMessage
     */
    public function passSession(Closure $passTo, Player $player, string $lastMessage): void{
        $session = $this->getSession($player);
        if($session === null)
            $this->passToNewSession($passTo, $player, $lastMessage);
        else {
            PMUtils::validateCallableSignature(function (Session $session): void{}, $passTo);
            $session->setLastMessage($lastMessage);
            $passTo($session);
        }
    }
    /**
     * @param Closure $passTo
     * @param Player $player
     * @param string $lastMessage
     */
    public function passToNewSession(Closure $passTo, Player $player, string $lastMessage): void{
        PMUtils::validateCallableSignature(function (Session $session): void{}, $passTo);
        $session = new Session($player, $lastMessage);
        $database = $this->plugin->getDatabase();
        $name = $player->getName();
        $onError = Utils::getOnError($this->plugin);

        $whenDone = function (Closure $passTo, Session $session, int $id) use ($database, $player, $name, $onError): void{
            $session->setId($id);
            $database->executeSelect(Queries::FILTER_GET_SOFT_MUTES, [
                'name' => $name
            ], function (array $result) use ($passTo, $player, $session): void{
            //    echo "\nDATABASE RESULT SOFT MUTES\n";
            //    var_dump($result);
                if($result !== [])
                    $session->setSoftMutedFor($result[0]['until'] > time() ? $result[0]['until'] : null);
                $this->sessions[$player->getLowerCaseName()] = $session;
                $passTo($session);
            }, $onError);
        };

        $database->executeSelect(Queries::FILTER_GET_PLAYER, [
            'name' => $name
        ], function (array $result) use ($whenDone, $passTo, $database, $session, $name, $onError): void{
        //    echo "\nDATABASE RESULT GET PLAYER\n";
        //    var_dump($result);
            if($result !== [] && isset($result[0])) {
                $result = $result[0];
                $id = $result['id'];
                $whenDone($passTo, $session, $id);
            } else {
                $database->executeInsert(Queries::FILTER_INSERT_PLAYERS, [
                    'name' => $name
                ], function (int $rows) use ($whenDone, $passTo, $database, $session, $name, $onError): void{
                //    echo "\nROWS\n";
                //    var_dump($rows);
                    $database->executeSelect(Queries::FILTER_GET_PLAYER, [
                        'name' => $name
                    ], function (array $result) use ($whenDone, $passTo, $database, $session, $name, $onError): void{
                    //    echo "\nDATABASE RESULT GET PLAYER AFTER INSERT\n";
                    //    var_dump($result);
                        if ($result !== [] && isset($result[0])) {
                            $result = $result[0];
                            $id = $result['id'];
                            $whenDone($passTo, $session, $id);
                        }
                    });
                }, $onError);
            }
        }, $onError);
    }
    /**
     * @param Player $player
     */
    public function deleteSession(Player $player): void{
        unset($this->sessions[$player->getName()]);
    }
    /**
     * @param Player $player
     * @return Session|null
     */
    public function getSession(Player $player): ?Session{
        return $this->sessions[$player->getLowerCaseName()] ?? null;
    }
    /**
     * @return Session[]|null
     */
    public function getAllSessions(): ?array{
        return $this->sessions;
    }
}