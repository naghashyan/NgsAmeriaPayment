<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Components\PluginConfig;

use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Class PluginConfigService
 */
class PluginConfigService
{
    private const CONFIG_PREFIX = 'NgsAmeriaPayment.config';

    private SystemConfigService $systemConfigService;

    /**
     * PluginConfigService constructor.
     *
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * Get all configs
     *
     * @param string|null $salesChannelId
     *
     * @return array|null
     */
    public function getAll(?string $salesChannelId = null): ?array
    {
        return $this->systemConfigService->get(self::CONFIG_PREFIX, $salesChannelId);
    }
}