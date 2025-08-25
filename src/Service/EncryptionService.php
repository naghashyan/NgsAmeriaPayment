<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Service;

use RuntimeException;

/**
 * Simple wrapper around OpenSSL AES-256-GCM encryption using APP_SECRET
 */
class EncryptionService
{
    private string $secret;

    public function __construct(string $appSecret)
    {
        $this->secret = hash('sha256', $appSecret, true);
    }

    public function encrypt(string $plaintext): string
    {
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($plaintext, 'aes-256-gcm', $this->secret, OPENSSL_RAW_DATA, $iv, $tag);
        if ($cipher === false) {
            throw new RuntimeException('Encryption failed');
        }
        return base64_encode($iv . $tag . $cipher);
    }

    public function decrypt(string $payload): string
    {
        $data = base64_decode($payload, true);
        if ($data === false || strlen($data) < 28) {
            throw new RuntimeException('Invalid payload');
        }
        $iv = substr($data, 0, 12);
        $tag = substr($data, 12, 16);
        $cipher = substr($data, 28);
        $plain = openssl_decrypt($cipher, 'aes-256-gcm', $this->secret, OPENSSL_RAW_DATA, $iv, $tag);
        if ($plain === false) {
            throw new RuntimeException('Decryption failed');
        }
        return $plain;
    }
}
