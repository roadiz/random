<?php

declare(strict_types=1);

namespace RZ\Roadiz\Random;

use Psr\Log\LoggerInterface;

class RandomGenerator
{
    protected ?LoggerInterface $logger;
    protected bool $useOpenSsl;

    /**
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        // determine whether to use OpenSSL
        if (defined('PHP_WINDOWS_VERSION_BUILD') && version_compare(PHP_VERSION, '5.3.4', '<')) {
            $this->useOpenSsl = false;
        } elseif (!function_exists('openssl_random_pseudo_bytes')) {
            if (null !== $this->logger) {
                $this->logger->notice('It is recommended that you enable the "openssl" extension for random number generation.');
            }
            $this->useOpenSsl = false;
        } else {
            $this->useOpenSsl = true;
        }
    }

    /**
     * @param int $nbBytes
     * @return string
     */
    protected function getRandomNumber(int $nbBytes = 32): string
    {
        // try OpenSSL
        if ($this->useOpenSsl) {
            $bytes = openssl_random_pseudo_bytes($nbBytes, $strong);

            if (false !== $bytes && true === $strong) {
                return $bytes;
            }

            if (null !== $this->logger) {
                $this->logger->info('OpenSSL did not produce a secure random number.');
            }
        }

        return hash('sha256', uniqid((string) mt_rand(), true), true);
    }
}
