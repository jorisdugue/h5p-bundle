<?php

namespace Studit\H5PBundle\Twig;

use Studit\H5PBundle\Core\H5PIntegration;
use Twig\TwigFilter;

class H5PExtension extends \Twig\Extension\AbstractExtension
{
    /**
     * @var H5PIntegration
     */
    private $h5pIntegration;

    /**
     * H5PExtension constructor.
     * @param H5PIntegration $h5pIntegration
     */
    public function __construct(H5PIntegration $h5pIntegration)
    {
        $this->h5pIntegration = $h5pIntegration;
    }

    public function getFilters(): array
    {
        return [new TwigFilter('h5pCacheBuster', [$this, 'getH5PCacheBuster'])];
    }

    public function getH5PCacheBuster($script): string
    {
        return $script . $this->h5pIntegration->getCacheBuster();
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName(): string
    {
        return 'h5p_extension';
    }
}
