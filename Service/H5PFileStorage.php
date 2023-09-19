<?php

namespace Studit\H5PBundle\Service;

use Studit\H5PBundle\Core\H5POptions;

class H5PFileStorage extends \H5PDefaultStorage
{
    public function __construct(H5POptions $h5POptions)
    {
        parent::__construct($h5POptions->getAbsoluteH5PPath());
    }
}
