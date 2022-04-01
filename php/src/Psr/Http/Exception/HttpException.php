<?php

declare(strict_types=1);

namespace BankId\OIDC\Psr\Http\Exception;

use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;

class HttpException extends RuntimeException implements ClientExceptionInterface
{
}
