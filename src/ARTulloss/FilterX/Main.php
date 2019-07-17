<?php

declare(strict_types=1);

namespace ARTulloss\FilterX;

use function arsort;
use ARTulloss\FilterX\{Events\Listener,
    libs\PASVL\Traverser\FailReport,
    libs\PASVL\Traverser\VO\Traverser,
    libs\PASVL\ValidatorLocator\ValidatorLocator,
    Session\SessionHandler};
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use RuntimeException;
use function strtotime;

class Main extends PluginBase {

    /** @var SessionHandler $sessionHandler */
    private $sessionHandler;
    /** @var Timer $timer */
    private $timer;
    /** @var DataConnector $database */
    private $database;
    /** @var int[] $infractionLengths */
    private $infractionLengths;
    /** @var Config $databaseConfig */
    private $databaseConfig;

    private const CONFIG_PATTERN = [
        'Filtered Words' => [
            '*' => ':string'
        ],
        'Silent' => [
            'filter' => ':bool',
            'soft_mute' => ':bool'
        ],
        'Infraction' => [
            'Mode' => ':int :between(1,2)',
            'Reset Every' => ':int', # seconds
            'Punishments' => [
                '*' => ':string :regexp(/^(?:[0-9]+ \)(?:seconds?|minutes?|hours?|days?|weeks?|months?|years?\)$/i)'
            ]
        ]
    ];

    private const DATABASE_PATTERN = [
        'type' => ':string :regexp(/^(sqlite|mysql\)$/)',
        'sqlite' => [
            'file' => ':string :regexp(/^.*\.sqlite$/)'
        ],
        'mysql' => [
            'host' => ':string :regexp(/^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?\)\.\){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?\)$/)',
            'username' => ':string',
            'password' => ':string',
            'schema' => ':string'
        ],
        'worker-limit' => ':int :min(1)'
    ];

    /**
     * @throws \Exception
     */
	public function onEnable(): void{
	    $this->saveDefaultConfigs();
	    $this->startDatabase();
	    $this->checkConfigsValid();
	    if($this->isEnabled()) {
            $this->resolveTimeLengths();
            $this->getServer()->getPluginManager()->registerEvents(new Listener($this), $this);
            $this->sessionHandler = new SessionHandler($this);
            $resetEverySeconds = (int) $this->getConfig()->getNested('Infraction.Reset Every');
            if($resetEverySeconds <= 0) {
                $resetEverySeconds = self::CONFIG_PATTERN['Infraction']['Reset Every'];
                $this->getLogger()->error("'Infractions Reset Every' can't be set as equal to or less than 0. Using default of $resetEverySeconds.");
            }
            $this->timer = new Timer($resetEverySeconds);
        }

	}
    public function saveDefaultConfigs(): void{
        $this->saveDefaultConfig();
        // Create database config
        $fileName = $this->getDataFolder() . 'database.yml';
        $this->saveResource('database.yml');
        $this->databaseConfig = new Config($fileName);
    }
    /**
     * Check if the config is valid
     */
    public function checkConfigsValid(): void{
        $traverser = new Traverser(new ValidatorLocator());
        try {
            # TODO Maybe do this by file so it's easier to know which file is broken- but the configs are rather small...
            $traverser->match(self::CONFIG_PATTERN, $this->getConfig()->getAll());
            $traverser->match(self::DATABASE_PATTERN, $this->databaseConfig->getAll());
        } catch (FailReport $report) {
            $logger = $this->getLogger();
            $logger->error('Invalid config detected! Reason:');
            Utils::outputFailReasons($this, $report);
            $logger->error('Disabling...');
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
    }
	public function startDatabase(): void{
        $this->database = libasynql::create($this, $this->databaseConfig->getAll(), [
            "sqlite" => "sqlite.sql",
            "mysql" => "mysql.sql"
        ]);
        $this->database->executeGeneric(Queries::FILTER_CREATE_PLAYERS, [], null, Utils::getOnError($this));
        $this->database->executeGeneric(Queries::FILTER_CREATE_SOFT_MUTES, [], null, Utils::getOnError($this));
        $this->database->waitAll();
    }
    /**
     * @throws \Exception
     */
    public function resolveTimeLengths(): void{
        $infractionStringLengths = $this->getConfig()->getNested('Infraction.Punishments');
    //    var_dump($infractionStringLengths);
        foreach ($infractionStringLengths as $infractions => $length)
            $this->infractionLengths[$infractions] = strtotime($length, 0);
        arsort($this->infractionLengths);
    }
    /**
     * @return SessionHandler
     */
    public function getSessionHandler(): SessionHandler{
        if(!isset($this->sessionHandler))
            throw new RuntimeException('The get session handler function should only be called after the plugin has been enabled!');
        return $this->sessionHandler;
    }
    /**
     * @return Timer
     */
    public function getTimer(): Timer{
        if(!isset($this->timer))
            throw new RuntimeException('The get timer function should only be called after the plugin has been enabled!');
        return $this->timer;
    }
    /**
     * @return DataConnector
     */
    public function getDatabase(): DataConnector{
        return $this->database;
    }
    /**
     * @return int[]
     */
    public function getInfractionLengths(): array{
        return $this->infractionLengths;
    }
}
