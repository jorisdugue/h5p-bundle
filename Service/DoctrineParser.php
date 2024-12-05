<?php

namespace Studit\H5PBundle\Service;

use Composer\InstalledVersions;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * This class exists to prevent breaking changes when working with different versions of Doctrine ORM.
 * For example, in Doctrine ORM v3.x, certain parameters require an ArrayCollection.
 * @author Joris Dugué
 */
class DoctrineParser
{
    /**
     * This method converts parameters to an ArrayCollection for ORM v3.
     * If using ORM v2, it simply returns the received parameters as is.
     *
     * @param array $params The input parameters to process.
     * @return ArrayCollection|array Returns an ArrayCollection for ORM v3 or the original parameters for ORM v2.
     */
    public static function buildParams(array $params): ArrayCollection|array
    {
        $doctrineVersion = InstalledVersions::getVersion('doctrine/orm');
        if ($doctrineVersion !== null && str_starts_with($doctrineVersion, '3')) {
            // For Doctrine ORM v3, ensure the parameters are returned as an ArrayCollection
            $paramsCollection = [];

            foreach ($params as $k => $val){
                $paramsCollection[] = new \Doctrine\ORM\Query\Parameter($k, $val);
            }

            return new ArrayCollection($paramsCollection);
        }
        // For Doctrine ORM v2, return the parameters as is
        return $params;
    }
}