<?php

declare(strict_types=1);

namespace ARTulloss\FilterX;

use function arsort;
use ARTulloss\FilterX\{Events\Listener,
    Filters\BaseFilter,
    Filters\IPAddressFilter,
    Filters\WordExistsFilter,
    libs\PASVL\Traverser\FailReport,
    libs\PASVL\Traverser\VO\Traverser,
    libs\PASVL\ValidatorLocator\ValidatorLocator,
    Queries\Queries,
    Session\SessionHandler};
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use RuntimeException;
use function spl_object_id;
use function strtotime;

class Main extends PluginBase {

    /** @var SessionHandler $sessionHandler */
    private $sessionHandler;
    /** @var DataConnector $database */
    private $database;
    /** @var int[] $infractionLengths */
    private $infractionLengths;
    /** @var Config $databaseConfig */
    private $databaseConfig;
    /** @var BaseFilter[] $filters */
    private $filters;

    private const CONFIG_PATTERN = [
        'Filtered Words' => [
            '*' => ':string'
        ],
        'Silent' => [
            'filter' => ':bool',
            'soft_mute' => ':bool'
        ],
        'Infraction' => [
            'Word Mode' => ':string :regexp(/^count|boole?a?n?$/i)',
            'IP Filter' => ':int', # infractions
            'Expire After' => ':int', # seconds
            'Punishments' => [
                '*' => ':string :regexp(/^(?:[0-9]+ \)(?:seconds?|minutes?|hours?|days?|weeks?|months?|years?\)$/i)'
            ]
        ],
        'Staff Chat Format' => ':string'
    ];

    private const DATABASE_PATTERN = [
        'type' => ':string :regexp(/^(sqlite|mysql\)$/)',
        'sqlite' => [
            'file' => ':string :regexp(/^.*\.sqlite$/)'
        ],
        'mysql' => [
            'host' => ':string :regexp(/^((25[0-5]|2[0-4]  [0-9]|[01]?[0-9][0-9]?\)\.\){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?\)$/)',
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
            $resetEverySeconds = (int) $this->getConfig()->getNested('Infraction.Expire After');
            if($resetEverySeconds <= 0) {
                $resetEverySeconds = 60;
                $this->getLogger()->error("'Infractions.Expire After' can't be set as equal to or less than 0. Using default value of 60 seconds.");
                $this->getConfig()->setNested('Infraction.Expire After', $resetEverySeconds);
            }
            $this->registerFilters();
        }

	}
	public function onDisable(): void{
        if(isset($this->database))
            $this->database->close();
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
        $catchFunction = function (string $file, FailReport $report): void{
            $logger = $this->getLogger();
            $reason = Utils::getFailReason($report);
            $logger->error("Invalid $file detected! Reason: $reason");
            $logger->error('Disabling...');
            $this->getServer()->getPluginManager()->disablePlugin($this);
        };
        $traverser = new Traverser(new ValidatorLocator());
        try {
            $traverser->match(self::CONFIG_PATTERN, $this->getConfig()->getAll());
        } catch (FailReport $report) {
            $catchFunction('config.yml', $report);
        }
        try {
            $traverser->match(self::DATABASE_PATTERN, $this->databaseConfig->getAll());
        } catch (FailReport $report) {
            $catchFunction('database.yml', $report);
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
    public function registerFilters(): void{
        $cfg = $this->getConfig()->getAll();
        foreach ([new IPAddressFilter($cfg['Infraction']['IP Filter']), new WordExistsFilter($cfg['Infraction']['Word Mode'], $cfg['Filtered Words'])] as $filter)
            $this->registerFilter($filter);
    }
    /**
     * @param BaseFilter $filter
     */
    public function registerFilter(BaseFilter $filter): void{
        $this->filters[spl_object_id($filter)] = $filter;
    }
    /**
     * @param BaseFilter $filter
     */
    public function deRegisterFilter(BaseFilter $filter): void{
        unset($this->filters[spl_object_id($filter)]);
    }
    /**
     * @return BaseFilter[]
     */
    public function getFilters(): array{
        return $this->filters;
    }
    /**
     * @param string $sentence
     * @param $isFiltered
     * @return int
     */
    public function checkAllFilters(string $sentence, bool &$isFiltered = false): int{
        $sum = 0;
        foreach ($this->getFilters() as $filter) {
            $sum += $filter->countViolations($sentence);
        }
        $isFiltered = $sum > 0;
        return $sum;
    }
    /**
     * @throws \Exception
     */
    public function resolveTimeLengths(): void{
        $infractionStringLengths = $this->getConfig()->getNested('Infraction.Punishments');
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
