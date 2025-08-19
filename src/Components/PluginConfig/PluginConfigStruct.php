<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Components\PluginConfig;

use Shopware\Core\Framework\Struct\Struct;

/**
 * Class PluginConfigStruct
 */
class PluginConfigStruct extends Struct
{
    public bool $testMode;
    public bool $enableCardBinding;
    public bool $freezePayments;
    public string $clientId;
    public string $username;
    public string $password;
    public string $apiUri;
    public string $description;

    /**
     * @param PluginConfigService $pluginConfigService
     * @param string|null $salesChannelId
     */
    public function __construct(PluginConfigService $pluginConfigService, ?string $salesChannelId = null)
    {
        $pluginConfig = $pluginConfigService->getAll($salesChannelId);
        $this->testMode = !empty($pluginConfig['testMode']);
        $this->enableCardBinding = !empty($pluginConfig['enableCardBinding']);
        $this->freezePayments = !empty($pluginConfig['freezePayments']);

        if ($this->testMode) {
            $this->clientId = $pluginConfig['testClientId'] ?? '';
            $this->username = $pluginConfig['testUsername'] ?? '';
            $this->password = $pluginConfig['testPassword'] ?? '';
            $this->apiUri = $pluginConfig['testApiUri'] ?? '';
            $this->description = $pluginConfig['testDescription'] ?? '';
        } else {
            $this->clientId = $pluginConfig['clientId'] ?? '';
            $this->username = $pluginConfig['username'] ?? '';
            $this->password = $pluginConfig['password'] ?? '';
            $this->apiUri = $pluginConfig['apiUri'] ?? '';
            $this->description = $pluginConfig['description'] ?? '';
        }
    }

}