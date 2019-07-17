<?php

/*
 * Auto-generated by libasynql-def
 * Created from mysql.sql
 */

declare(strict_types=1);

namespace ARTulloss\FilterX\Queries;

interface Queries{
    /**
     * <h4>Declared in:</h4>
     * - C:/Users/Adam/pocketmine/plugins/FilterX/resources/mysql.sql:9
     */
    public const FILTER_CREATE_PLAYERS = "filter.create.players";

    /**
     * <h4>Declared in:</h4>
     * - C:/Users/Adam/pocketmine/plugins/FilterX/resources/mysql.sql:15
     */
    public const FILTER_CREATE_SOFT_MUTES = "filter.create.soft_mutes";

    /**
     * <h4>Declared in:</h4>
     * - C:/Users/Adam/pocketmine/plugins/FilterX/resources/mysql.sql:44
     *
     * <h3>Variables</h3>
     * - <code>:name</code> string, required in mysql.sql
     */
    public const FILTER_DELETE_SOFT_MUTE = "filter.delete.soft_mute";

    /**
     * <h4>Declared in:</h4>
     * - C:/Users/Adam/pocketmine/plugins/FilterX/resources/mysql.sql:34
     *
     * <h3>Variables</h3>
     * - <code>:name</code> string, required in mysql.sql
     */
    public const FILTER_GET_PLAYER = "filter.get.player";

    /**
     * <h4>Declared in:</h4>
     * - C:/Users/Adam/pocketmine/plugins/FilterX/resources/mysql.sql:38
     *
     * <h3>Variables</h3>
     * - <code>:name</code> string, required in mysql.sql
     */
    public const FILTER_GET_SOFT_MUTES = "filter.get.soft_mutes";

    /**
     * <h4>Declared in:</h4>
     * - C:/Users/Adam/pocketmine/plugins/FilterX/resources/mysql.sql:21
     *
     * <h3>Variables</h3>
     * - <code>:name</code> string, required in mysql.sql
     */
    public const FILTER_INSERT_PLAYERS = "filter.insert.players";

    /**
     * <h4>Declared in:</h4>
     * - C:/Users/Adam/pocketmine/plugins/FilterX/resources/mysql.sql:28
     *
     * <h3>Variables</h3>
     * - <code>:until</code> int, required in mysql.sql
     * - <code>:name</code> string, required in mysql.sql
     */
    public const FILTER_UPSERT_SOFT_MUTES = "filter.upsert.soft_mutes";

}