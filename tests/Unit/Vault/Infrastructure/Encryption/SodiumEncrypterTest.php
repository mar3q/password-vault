<?php

declare(strict_types=1);

namespace App\Tests\Unit\Vault\Infrastructure\Encryption;

use App\Vault\Infrastructure\Encryption\SodiumEncrypter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SodiumEncrypterTest extends TestCase
{
    private SodiumEncrypter $encrypter;

    protected function setUp(): void
    {
        $key = base64_encode(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));
        $this->encrypter = new SodiumEncrypter($key);
    }

    #[Test]
    public function it_encrypts_and_decrypts_successfully(): void
    {
        $plaintext = 'my-secret-password';

        $encrypted = $this->encrypter->encrypt($plaintext);
        $decrypted = $this->encrypter->decrypt($encrypted);

        self::assertSame($plaintext, $decrypted);
        self::assertNotSame($plaintext, $encrypted);
    }

    #[Test]
    public function it_produces_different_ciphertexts_for_same_input(): void
    {
        $plaintext = 'my-secret-password';

        $encrypted1 = $this->encrypter->encrypt($plaintext);
        $encrypted2 = $this->encrypter->encrypt($plaintext);

        self::assertNotSame($encrypted1, $encrypted2);
    }

    #[Test]
    public function it_rejects_invalid_key_length(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new SodiumEncrypter(base64_encode('too-short'));
    }

    #[Test]
    public function it_fails_to_decrypt_with_wrong_key(): void
    {
        $key1 = base64_encode(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));
        $key2 = base64_encode(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));

        $encrypter1 = new SodiumEncrypter($key1);
        $encrypter2 = new SodiumEncrypter($key2);

        $encrypted = $encrypter1->encrypt('secret');

        $this->expectException(\RuntimeException::class);

        $encrypter2->decrypt($encrypted);
    }
}
