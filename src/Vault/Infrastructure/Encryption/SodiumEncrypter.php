<?php

declare(strict_types=1);

namespace App\Vault\Infrastructure\Encryption;

use App\Vault\Domain\Port\Encrypter;

final readonly class SodiumEncrypter implements Encrypter
{
    private string $key;

    public function __construct(string $encryptionKey)
    {
        $decoded = base64_decode($encryptionKey, true);

        if ($decoded === false || strlen($decoded) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new \InvalidArgumentException(sprintf(
                'Encryption key must be %d bytes (base64-encoded). Got %d bytes.',
                SODIUM_CRYPTO_SECRETBOX_KEYBYTES,
                $decoded !== false ? strlen($decoded) : 0,
            ));
        }

        $this->key = $decoded;
    }

    public function encrypt(string $plaintext): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $this->key);

        return base64_encode($nonce . $ciphertext);
    }

    public function decrypt(string $ciphertext): string
    {
        $decoded = base64_decode($ciphertext, true);

        if ($decoded === false) {
            throw new \RuntimeException('Failed to decode ciphertext.');
        }

        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $encrypted = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $plaintext = sodium_crypto_secretbox_open($encrypted, $nonce, $this->key);

        if ($plaintext === false) {
            throw new \RuntimeException('Decryption failed. Invalid key or corrupted data.');
        }

        return $plaintext;
    }
}
