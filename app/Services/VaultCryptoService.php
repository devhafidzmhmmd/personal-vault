<?php

namespace App\Services;

use Illuminate\Support\Str;

class VaultCryptoService
{
    private const PBKDF2_ITERATIONS = 100000;

    private const KEY_LENGTH = 32;

    private const IV_LENGTH = 12;

    private const TAG_LENGTH = 16;

    /**
     * Derive encryption key from master password and salt using PBKDF2.
     */
    public function deriveKey(string $masterPassword, string $salt): string
    {
        return hash_pbkdf2(
            'sha256',
            $masterPassword,
            $salt,
            self::PBKDF2_ITERATIONS,
            self::KEY_LENGTH,
            true
        );
    }

    /**
     * Generate a random salt for key derivation.
     */
    public function generateSalt(): string
    {
        return Str::random(32);
    }

    /**
     * Encrypt plaintext with the given key (32 bytes). Returns base64(iv + tag + ciphertext).
     */
    public function encrypt(string $plaintext, string $key): string
    {
        $iv = random_bytes(self::IV_LENGTH);
        $tag = '';

        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );

        if ($ciphertext === false) {
            throw new \RuntimeException('Encryption failed');
        }

        return base64_encode($iv . $tag . $ciphertext);
    }

    /**
     * Decrypt payload (base64 of iv + tag + ciphertext) with the given key.
     */
    public function decrypt(string $payload, string $key): string
    {
        $raw = base64_decode($payload, true);
        if ($raw === false || strlen($raw) < self::IV_LENGTH + self::TAG_LENGTH) {
            throw new \RuntimeException('Invalid payload');
        }

        $iv = substr($raw, 0, self::IV_LENGTH);
        $tag = substr($raw, self::IV_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($raw, self::IV_LENGTH + self::TAG_LENGTH);

        $plaintext = openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plaintext === false) {
            throw new \RuntimeException('Decryption failed');
        }

        return $plaintext;
    }
}
