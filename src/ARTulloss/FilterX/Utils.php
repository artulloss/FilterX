<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 7/11/2019
 * Time: 12:15 AM
 */
declare(strict_types=1);

namespace ARTulloss\FilterX;

use ARTulloss\FilterX\libs\PASVL\Traverser\FailReport;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use function strtolower;
use function implode;
use Throwable;
use Closure;

/**
 * Class Utils
 * Static functions that are useful but don't really have a great place to belong
 * @package ARTulloss\FilterX
 */
class Utils {
    /**
     * String case insensitive check if exists in haystack
     * @param string[] $needles
     * @param string $haystack
     * @return bool
     */
    static function striExists(array $needles, string $haystack): bool{
        foreach ($needles as $needle) {
            if(stripos($haystack, $needle) !== false)
                return true;
        }
        return false;
    }
    /**
     * @param array $needles
     * @param string $haystack
     * @return int
     */
    static function array_substr_count(array $needles, string $haystack): int{
        $i = 0;
        $haystack = strtolower($haystack);
        foreach ($needles as $needle)
            $i += substr_count($haystack, strtolower($needle));
        return $i;
    }
    /**
     * @param $time
     * @param string $past
     * @param string $future
     * @return string
     */
    static function time2str($time, $past = 'ago', $future = 'left') {

        $d[0] = [1,"second", "seconds"];
        $d[1] = [60,"minute", "minutes"];
        $d[2] = [3600,"hour", "hours"];
        $d[3] = [86400,"day", "days"];
        $d[4] = [604800,"week", "weeks"];
        $d[5] = [2592000,"month", "months"];
        $d[6] = [31104000,"year", "years"];

        $w = [];

        $return = "";
        $now = time();
        $diff = ($now - $time);
        $secondsLeft = $diff;

        for($i = 6; $i > -1; $i--) {
            $w[$i] = intval($secondsLeft / $d[$i][0]);
            $secondsLeft -= ($w[$i] * $d[$i][0]);
            if($w[$i] !== 0) {
                $abs = abs($w[$i]);
                $return .= $abs . " " . $d[$i][1] . ($abs > 1 ? 's' : '') . " ";
            }
        }

        $return .= ($diff > 0) ? $past : $future;
        return $return;
    }
    /**
     * @param Plugin|null $plugin
     * @return Closure
     */
    static function getOnError(Plugin $plugin = null): Closure{
        return function (Throwable $error) use ($plugin): void{
            $hasLogger = $plugin !== null ? $plugin : Server::getInstance();
            $hasLogger->getLogger()->logException($error);
        };
    }
    /**
     * @param FailReport $report
     * @return string
     */
    static function getFailReason(FailReport $report): string{
        $reason = $report->getReason();
        $reasonArray = [];
        if($reason->isKeyQuantityType())
            $reasonArray[] = 'Invalid key quantity found!';
        if($reason->isKeyType())
            $reasonArray[] = 'Invalid key type found!';
        if($reason->isValueType())
            $reasonArray[] = 'Invalid value type found!';
        return implode(TextFormat::EOL, $reasonArray);
    }
}