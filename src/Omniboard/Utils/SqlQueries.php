<?php

declare(strict_types=1);

namespace Omniboard\Utils;

interface SqlQueries {
    
    public const CREATE_ISLAND_TABLE = "topislanddata.createTable";
    public const ISLAND_UPDATE_POINTS = "topislanddata.updatePoints";
    public const ISLAND_GET_TOP = "topislanddata.getTopPlayers";
}
