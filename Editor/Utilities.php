<?php

namespace Studit\H5PBundle\Editor;

use Doctrine\ORM\EntityManager;
use Studit\H5PBundle\Core\H5PSymfony;

class Utilities
{
    /**
     * Extract library information from library string
     *
     * @param string $library Library string with versioning, e.g. H5P.MultiChoice 1.9
     * @return array|bool
     */
    public static function getLibraryProperties($library)
    {
        $matches = [];
        preg_match_all('/(.+)\s(\d+)\.(\d+)$/', $library, $matches);
        if (count($matches) == 4) {
            $libraryData = [
                'name' => $matches[1][0],
                'machineName' => $matches[1][0],
                'majorVersion' => $matches[2][0],
                'minorVersion' => $matches[3][0],
            ];
            return $libraryData;
        }
        return false;
    }
}
