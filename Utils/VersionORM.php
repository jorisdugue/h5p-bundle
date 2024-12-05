<?php

namespace Studit\H5PBundle\Utils;

use Composer\InstalledVersions;

/**
 * VersionProvider class provides a simple interface to retrieve the version of the Doctrine ORM package.
 * It wraps the `InstalledVersions::getVersion` method, allowing easier testing and version checking.
 */
class VersionORM
{
    /**
     * Retrieves the installed version of the Doctrine ORM package.
     *
     * This method calls `InstalledVersions::getVersion('doctrine/orm')` and returns the version string
     * for the Doctrine ORM package if available. If the package is not found, it returns null.
     *
     * @return string|null The version of Doctrine ORM if installed, null otherwise.
     */
    public function getDoctrineVersion(): ?string
    {
        return InstalledVersions::getVersion('doctrine/orm');
    }
}
