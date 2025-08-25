<?php declare(strict_types=1);

use Ngs\AmeriaPayment\Service\EncryptionService;
use PHPUnit\Framework\TestCase;

class EncryptionServiceTest extends TestCase
{
    public function testEncryptDecryptRoundtrip(): void
    {
        $service = new EncryptionService('app-secret');
        $plain = 'sensitive-token';
        $cipher = $service->encrypt($plain);
        $this->assertNotSame($plain, $cipher);
        $decoded = $service->decrypt($cipher);
        $this->assertSame($plain, $decoded);
    }
}
