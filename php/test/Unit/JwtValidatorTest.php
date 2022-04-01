<?php

declare(strict_types=1);

namespace BankId\OIDC\Test\Unit;

use BankId\OIDC\Discovery\ConfigurationProvider;
use BankId\OIDC\Tools\JwtValidator;
use BankId\OIDC\Discovery\KeysProvider;
use Exception;
use Jose\Component\Checker\InvalidHeaderException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JwtValidatorTest extends TestCase
{
    /**
     * @testWith    ["eyJraWQiOiJycC1zaWduIiwiYWxnIjoiUFM1MTIifQ.eyJzdWIiOiIxZWM3YzA2My1kNjAwLTQ5NjEtOGVhNS03YTQwN2RjYzg1MjUiLCJhenAiOiI3MmZkYTAxMS0wNDc5LTRhNGMtOWZmZi0wYTZjN2Y1ODRlMWUiLCJpc3MiOiJodHRwczpcL1wvb2lkYy5zYW5kYm94LmJhbmtpZC5jelwvIiwiZXhwIjoxNjQ1MzA2MDg4LCJpYXQiOjE2NDUzMDI1MjEsImp0aSI6ImIxZjJmM2Q2LTU3ZGYtNGFkNy04ZDQ1LWVlZWM4MDFkNzJkNyJ9.gBQbPyphbetv269bP6amTkPixSycm8wdetqFkaEOR3MmZTT1Y94lO9qeoKfsHJxhlri8yE7lySQcpzGTO2JVJ_HXZo5eGoSQAC7U3_kmU73jH7qRWcFF1KrSr6yI6G0xXJLBl2b7qfusp5f76jmt1phTqtS255_QBJecub-vex-inQ1z6-tXlOBW8-ka9BM8o1xs5O7c-pNRaHD-oBwkvIg0M2J32aZ4Ho4hIuRdU2Z4gzOE7qfNHTh0sGhSYLfF82PGMHuOAEQlCNh5-zKPGgzgeJLBK5lHUK2VggAh9TAGjEdsQ0fgdPBX4wFr9Wrs6gI5xEfUiMjZBXyHrcWuOA"]
     *              ["eyJraWQiOiJycC1zaWduIiwiYWxnIjoiUFM1MTIifQ.eyJpc3MiOiJodHRwczpcL1wvb2lkYy5zYW5kYm94LmJhbmtpZC5jelwvIiwiZXhwIjoxNjc2ODM4NTIxLCJpYXQiOjE2NDUzMDI1MjEsImp0aSI6IjMxNjQ3YmY1LWJjYTctNDE1MS1hNzZhLWRhZGNmM2YzOGUyMyJ9.PF7363xIuPli5h4Kd4uaJhKOkXT86FbhKxkOETTZSE1mp0RnmrxttlG0D9P8yNnkpM3-4cyTifB1ONwn-BpRunlewpwoUG39KDzMq31lY5V6hKGSUuRm9e8Ghp0yfjncNqrOBVsqjdHeBFAzcB_wzonpDTgsGFyui_R5Jh9rSy9ihfNvJt0DkI9WI1Kr2kshSqx8ZCs4yOhM_vYj6augKBwwIpk_6Sj-tnhEy-hu1v7ZHXaP_WmpPqsuhvM3tFKnArwg02OQjGwlebh3Q6I3sURnh6MELjj2XCTa1lY1beQ4XfXmvO09-FqkF0Yk4SIX5HnT_p74V-rAwiqArPxrAw"]
     *              ["eyJraWQiOiJycC1zaWduIiwiYWxnIjoiUFM1MTIifQ.eyJhY3IiOiIxIiwic3ViIjoiMWVjN2MwNjMtZDYwMC00OTYxLThlYTUtN2E0MDdkY2M4NTI1IiwiYXVkIjoiNzJmZGEwMTEtMDQ3OS00YTRjLTlmZmYtMGE2YzdmNTg0ZTFlIiwiYXV0aF90aW1lIjoxNjQ1MzAyNDkwNjcxLCJpc3MiOiJodHRwczpcL1wvb2lkYy5zYW5kYm94LmJhbmtpZC5jelwvIiwiZXhwIjoxNjQ1MzAzMTIxLCJpYXQiOjE2NDUzMDI1MjEsImp0aSI6IjkzZDllMDJjLTlhZDQtNGNmMy1hYWFhLTI3NmQ1ZTE2NDczMCJ9.XnG4jmkxcxaW9qEAvXKsYdvwa7PyiOGewNn0CqAkAmv6YDtbJsCTQdleMxFEURVTyOsSshR51SGCcfouxfMPmTTZ3QaF23ssWAHRgpK95yTAfUqcdT3z2jD9A4EaHCgpbbbwZJefet-FwUtCrHkdYBuxBLjerXKXFgvD3vqw7WOEt_lZh5B-KF9o6zMSrSZlUzHDOkc5GgUlKLftFpGtpOV1X9gPyGAeIRM_ga5tF2uoBbTSbtq6nsp3Mcl02AR9fQ23Or9LA0KaKHonM0AU1b_BiW76BTK6AlbxJtjZ-2kR2vY3wBoxpUL6gd7rEbS17EUR7fTKg9gUWh70SzCgcA"]
     */
    public function testValidSignature(string $jwt): void
    {
        /** @var MockObject&ConfigurationProvider $configurationProvider */
        $configurationProvider = $this->createMock(ConfigurationProvider::class);
        $configurationProvider->expects(static::once())->method('getIssuer')->willReturn('https://oidc.sandbox.bankid.cz/');

        /** @var MockObject&KeysProvider $keysProvider */
        $keysProvider = $this->createMock(KeysProvider::class);
        $keysProvider->expects(static::once())->method('getKeys')->willReturn($this->getValidKeys());

        $jwkValidator = new JwtValidator(
            configurationProvider: $configurationProvider,
            keysProvider: $keysProvider,
        );

        $jwkValidator->validate(
            jwt: $jwt,
            expectedAlgos: ['ES512', 'PS512'],
        );
    }

    /**
     * Regular tokens with BROKEN word in the signature.
     *
     * @testWith    ["eyJraWQiOiJycC1zaWduIiwiYWxnIjoiUFM1MTIifQ.eyJzdWIiOiIxZWM3YzA2My1kNjAwLTQ5NjEtOGVhNS03YTQwN2RjYzg1MjUiLCJhenAiOiI3MmZkYTAxMS0wNDc5LTRhNGMtOWZmZi0wYTZjN2Y1ODRlMWUiLCJpc3MiOiJodHRwczpcL1wvb2lkYy5zYW5kYm94LmJhbmtpZC5jelwvIiwiZXhwIjoxNjQ1MzA2MDg4LCJpYXQiOjE2NDUzMDI1MjEsImp0aSI6ImIxZjJmM2Q2LTU3ZGYtNGFkNy04ZDQ1LWVlZWM4MDFkNzJkNyJ9.BROKENphbetv269bP6amTkPixSycm8wdetqFkaEOR3MmZTT1Y94lO9qeoKfsHJxhlri8yE7lySQcpzGTO2JVJ_HXZo5eGoSQAC7U3_kmU73jH7qRWcFF1KrSr6yI6G0xXJLBl2b7qfusp5f76jmt1phTqtS255_QBJecub-vex-inQ1z6-tXlOBW8-ka9BM8o1xs5O7c-pNRaHD-oBwkvIg0M2J32aZ4Ho4hIuRdU2Z4gzOE7qfNHTh0sGhSYLfF82PGMHuOAEQlCNh5-zKPGgzgeJLBK5lHUK2VggAh9TAGjEdsQ0fgdPBX4wFr9Wrs6gI5xEfUiMjZBXyHrcWuOA"]
     *              ["eyJraWQiOiJycC1zaWduIiwiYWxnIjoiUFM1MTIifQ.eyJpc3MiOiJodHRwczpcL1wvb2lkYy5zYW5kYm94LmJhbmtpZC5jelwvIiwiZXhwIjoxNjc2ODM4NTIxLCJpYXQiOjE2NDUzMDI1MjEsImp0aSI6IjMxNjQ3YmY1LWJjYTctNDE1MS1hNzZhLWRhZGNmM2YzOGUyMyJ9.BROKENxIuPli5h4Kd4uaJhKOkXT86FbhKxkOETTZSE1mp0RnmrxttlG0D9P8yNnkpM3-4cyTifB1ONwn-BpRunlewpwoUG39KDzMq31lY5V6hKGSUuRm9e8Ghp0yfjncNqrOBVsqjdHeBFAzcB_wzonpDTgsGFyui_R5Jh9rSy9ihfNvJt0DkI9WI1Kr2kshSqx8ZCs4yOhM_vYj6augKBwwIpk_6Sj-tnhEy-hu1v7ZHXaP_WmpPqsuhvM3tFKnArwg02OQjGwlebh3Q6I3sURnh6MELjj2XCTa1lY1beQ4XfXmvO09-FqkF0Yk4SIX5HnT_p74V-rAwiqArPxrAw"]
     *              ["eyJraWQiOiJycC1zaWduIiwiYWxnIjoiUFM1MTIifQ.eyJhY3IiOiIxIiwic3ViIjoiMWVjN2MwNjMtZDYwMC00OTYxLThlYTUtN2E0MDdkY2M4NTI1IiwiYXVkIjoiNzJmZGEwMTEtMDQ3OS00YTRjLTlmZmYtMGE2YzdmNTg0ZTFlIiwiYXV0aF90aW1lIjoxNjQ1MzAyNDkwNjcxLCJpc3MiOiJodHRwczpcL1wvb2lkYy5zYW5kYm94LmJhbmtpZC5jelwvIiwiZXhwIjoxNjQ1MzAzMTIxLCJpYXQiOjE2NDUzMDI1MjEsImp0aSI6IjkzZDllMDJjLTlhZDQtNGNmMy1hYWFhLTI3NmQ1ZTE2NDczMCJ9.BROKENkxcxaW9qEAvXKsYdvwa7PyiOGewNn0CqAkAmv6YDtbJsCTQdleMxFEURVTyOsSshR51SGCcfouxfMPmTTZ3QaF23ssWAHRgpK95yTAfUqcdT3z2jD9A4EaHCgpbbbwZJefet-FwUtCrHkdYBuxBLjerXKXFgvD3vqw7WOEt_lZh5B-KF9o6zMSrSZlUzHDOkc5GgUlKLftFpGtpOV1X9gPyGAeIRM_ga5tF2uoBbTSbtq6nsp3Mcl02AR9fQ23Or9LA0KaKHonM0AU1b_BiW76BTK6AlbxJtjZ-2kR2vY3wBoxpUL6gd7rEbS17EUR7fTKg9gUWh70SzCgcA"]
     */
    public function testBrokenSignatures(string $jwt): void
    {
        /** @var MockObject&ConfigurationProvider $configurationProvider */
        $configurationProvider = $this->createMock(ConfigurationProvider::class);
        $configurationProvider->expects(static::once())->method('getIssuer')->willReturn('https://oidc.sandbox.bankid.cz/');

        /** @var MockObject&KeysProvider $keysProvider */
        $keysProvider = $this->createMock(KeysProvider::class);
        $keysProvider->expects(static::once())->method('getKeys')->willReturn($this->getValidKeys());

        $jwkValidator = new JwtValidator(
            configurationProvider: $configurationProvider,
            keysProvider: $keysProvider,
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid signature');

        $jwkValidator->validate(
            jwt: $jwt,
            expectedAlgos: ['ES512', 'PS512'],
        );
    }

    /**
     * Regular tokens, but headers and payload do not match the signature.
     *
     * @testWith    ["eyJraWQiOiJycC1zaWduIiwiYWxnIjoiUFM1MTIifQ.eyJhY3IiOiIxIiwic3ViIjoiMWVjN2MwNjMtZDYwMC00OTYxLThlYTUtN2E0MDdkY2M4NTI1IiwiYXVkIjoiNzJmZGEwMTEtMDQ3OS00YTRjLTlmZmYtMGE2YzdmNTg0ZTFlIiwiYXV0aF90aW1lIjoxNjQ1MzAyNDkwNjcxLCJpc3MiOiJodHRwczpcL1wvb2lkYy5zYW5kYm94LmJhbmtpZC5jelwvIiwiZXhwIjoxNjQ1MzAzMTIxLCJpYXQiOjE2NDUzMDI1MjEsImp0aSI6IjkzZDllMDJjLTlhZDQtNGNmMy1hYWFhLTI3NmQ1ZTE2NDczMCJ9.gBQbPyphbetv269bP6amTkPixSycm8wdetqFkaEOR3MmZTT1Y94lO9qeoKfsHJxhlri8yE7lySQcpzGTO2JVJ_HXZo5eGoSQAC7U3_kmU73jH7qRWcFF1KrSr6yI6G0xXJLBl2b7qfusp5f76jmt1phTqtS255_QBJecub-vex-inQ1z6-tXlOBW8-ka9BM8o1xs5O7c-pNRaHD-oBwkvIg0M2J32aZ4Ho4hIuRdU2Z4gzOE7qfNHTh0sGhSYLfF82PGMHuOAEQlCNh5-zKPGgzgeJLBK5lHUK2VggAh9TAGjEdsQ0fgdPBX4wFr9Wrs6gI5xEfUiMjZBXyHrcWuOA"]
     *              ["eyJraWQiOiJycC1zaWduIiwiYWxnIjoiUFM1MTIifQ.eyJzdWIiOiIxZWM3YzA2My1kNjAwLTQ5NjEtOGVhNS03YTQwN2RjYzg1MjUiLCJhenAiOiI3MmZkYTAxMS0wNDc5LTRhNGMtOWZmZi0wYTZjN2Y1ODRlMWUiLCJpc3MiOiJodHRwczpcL1wvb2lkYy5zYW5kYm94LmJhbmtpZC5jelwvIiwiZXhwIjoxNjQ1MzA2MDg4LCJpYXQiOjE2NDUzMDI1MjEsImp0aSI6ImIxZjJmM2Q2LTU3ZGYtNGFkNy04ZDQ1LWVlZWM4MDFkNzJkNyJ9.PF7363xIuPli5h4Kd4uaJhKOkXT86FbhKxkOETTZSE1mp0RnmrxttlG0D9P8yNnkpM3-4cyTifB1ONwn-BpRunlewpwoUG39KDzMq31lY5V6hKGSUuRm9e8Ghp0yfjncNqrOBVsqjdHeBFAzcB_wzonpDTgsGFyui_R5Jh9rSy9ihfNvJt0DkI9WI1Kr2kshSqx8ZCs4yOhM_vYj6augKBwwIpk_6Sj-tnhEy-hu1v7ZHXaP_WmpPqsuhvM3tFKnArwg02OQjGwlebh3Q6I3sURnh6MELjj2XCTa1lY1beQ4XfXmvO09-FqkF0Yk4SIX5HnT_p74V-rAwiqArPxrAw"]
     *              ["eyJraWQiOiJycC1zaWduIiwiYWxnIjoiUFM1MTIifQ.eyJpc3MiOiJodHRwczpcL1wvb2lkYy5zYW5kYm94LmJhbmtpZC5jelwvIiwiZXhwIjoxNjc2ODM4NTIxLCJpYXQiOjE2NDUzMDI1MjEsImp0aSI6IjMxNjQ3YmY1LWJjYTctNDE1MS1hNzZhLWRhZGNmM2YzOGUyMyJ9.XnG4jmkxcxaW9qEAvXKsYdvwa7PyiOGewNn0CqAkAmv6YDtbJsCTQdleMxFEURVTyOsSshR51SGCcfouxfMPmTTZ3QaF23ssWAHRgpK95yTAfUqcdT3z2jD9A4EaHCgpbbbwZJefet-FwUtCrHkdYBuxBLjerXKXFgvD3vqw7WOEt_lZh5B-KF9o6zMSrSZlUzHDOkc5GgUlKLftFpGtpOV1X9gPyGAeIRM_ga5tF2uoBbTSbtq6nsp3Mcl02AR9fQ23Or9LA0KaKHonM0AU1b_BiW76BTK6AlbxJtjZ-2kR2vY3wBoxpUL6gd7rEbS17EUR7fTKg9gUWh70SzCgcA"]
     */
    public function testPayloadMismatch(string $jwt): void
    {
        /** @var MockObject&ConfigurationProvider $configurationProvider */
        $configurationProvider = $this->createMock(ConfigurationProvider::class);
        $configurationProvider->expects(static::once())->method('getIssuer')->willReturn('https://oidc.sandbox.bankid.cz/');

        /** @var MockObject&KeysProvider $keysProvider */
        $keysProvider = $this->createMock(KeysProvider::class);
        $keysProvider->expects(static::once())->method('getKeys')->willReturn($this->getValidKeys());

        $jwkValidator = new JwtValidator(
            configurationProvider: $configurationProvider,
            keysProvider: $keysProvider,
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid signature');

        $jwkValidator->validate(
            jwt: $jwt,
            expectedAlgos: ['ES512', 'PS512'],
        );
    }

    /**
     * @testWith    ["eyJraWQiOiJycC1zaWduIiwiYWxnIjoiUFM1MTIifQ.eyJzdWIiOiIxZWM3YzA2My1kNjAwLTQ5NjEtOGVhNS03YTQwN2RjYzg1MjUiLCJhenAiOiI3MmZkYTAxMS0wNDc5LTRhNGMtOWZmZi0wYTZjN2Y1ODRlMWUiLCJpc3MiOiJodHRwczpcL1wvb2lkYy5zYW5kYm94LmJhbmtpZC5jelwvIiwiZXhwIjoxNjQ1MzA2MDg4LCJpYXQiOjE2NDUzMDI1MjEsImp0aSI6ImIxZjJmM2Q2LTU3ZGYtNGFkNy04ZDQ1LWVlZWM4MDFkNzJkNyJ9.gBQbPyphbetv269bP6amTkPixSycm8wdetqFkaEOR3MmZTT1Y94lO9qeoKfsHJxhlri8yE7lySQcpzGTO2JVJ_HXZo5eGoSQAC7U3_kmU73jH7qRWcFF1KrSr6yI6G0xXJLBl2b7qfusp5f76jmt1phTqtS255_QBJecub-vex-inQ1z6-tXlOBW8-ka9BM8o1xs5O7c-pNRaHD-oBwkvIg0M2J32aZ4Ho4hIuRdU2Z4gzOE7qfNHTh0sGhSYLfF82PGMHuOAEQlCNh5-zKPGgzgeJLBK5lHUK2VggAh9TAGjEdsQ0fgdPBX4wFr9Wrs6gI5xEfUiMjZBXyHrcWuOA"]
     */
    public function testWrongAlgo(string $jwt): void
    {
        /** @var MockObject&ConfigurationProvider $configurationProvider */
        $configurationProvider = $this->createMock(ConfigurationProvider::class);
        $configurationProvider->expects(static::once())->method('getIssuer')->willReturn('https://oidc.sandbox.bankid.cz/');

        /** @var MockObject&KeysProvider $keysProvider */
        $keysProvider = $this->createMock(KeysProvider::class);
        $keysProvider->expects(static::once())->method('getKeys')->willReturn($this->getValidKeys());

        $jwkValidator = new JwtValidator(
            configurationProvider: $configurationProvider,
            keysProvider: $keysProvider,
        );

        $this->expectException(InvalidHeaderException::class);
        $this->expectExceptionMessage('Unsupported algorithm.');

        $jwkValidator->validate(
            jwt: $jwt,
            expectedAlgos: ['HS256', 'HS384', 'HS512', 'PS256', 'PS384', 'RS256', 'RS384', 'RS512', 'ES256', 'ES256K', 'ES384', 'ES512'],
        );
    }

    /**
     * @return array<array<string,mixed>>
     */
    private function getValidKeys(): array
    {
        return [
            [
                'kty' => 'RSA',
                'x5t#S256' => 'fYowjlnVtUVM3EvJahDnIBjZITeS2SK-9zeE4j3iZ-w',
                'e' => 'AQAB',
                'use' => 'enc',
                'kid' => 'rp-encrypt',
                'x5c' => [
                    'MIIElTCCBBugAwIBAgICECwwCgYIKoZIzj0EAwMwfjELMAkGA1UEBhMCQ1oxDjAMBgNVBAgMBVByYWhhMSAwHgYDVQQKDBdCYW5rb3ZuaSBpZGVudGl0YSwgYS5zLjEdMBsGA1UEAwwUQmFua0lEIFByb2R1Y3Rpb24gQ0ExHjAcBgkqhkiG9w0BCQEWD2FkbWluQGJhbmtpZC5jejAeFw0yMTAzMTgwNzUwMDJaFw0yNDAzMTcwNzUwMDJaMFsxCzAJBgNVBAYTAkNaMQ4wDAYDVQQIDAVQcmFoYTEgMB4GA1UECgwXQmFua292bmkgaWRlbnRpdGEsIGEucy4xGjAYBgNVBAMMEUJhbmtJRCBwcm9kdWN0aW9uMIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAxFLhcDDXnkdcO7CV1gjm4pXu60VFVuVKdYazZ+Bv1EXZ8I6NNQ/yrS0fysyLdaeNEwTrQ2rhb2BjuaR9aOvrPdhFlS2yKZ+k4+wkWeioc6t3jZvb9fJvKpCxozMU8XwC/OVO81G3Az5Gyv/nAGCzNmHRsXUiJBA9gh5OVduBJyAZN6w7s8F4A+QQlSdbMkVduHpUqGlGbvDDZ0zpssJQv2pA3i6y3mfAEPccr75Vgx/le9+6PC/e7BaZFUY/BdP6KmesitPZgD6EACP/QUh21jHn0feGDV+nGkZswPxZp3FCEz6YnkZg24/C6JHOjUee/gATjjjUC+uxpVPLuUGjR+Rf0WMmczMec3LJTfXwhx33ai6nQ02vp8UUGzjfSzF0UiztrWJQ9pRgc4o95h4npcLO+n7uh3NVR2/nHtBPEYGvxxZyX50Ux8HibaHEKZvoQARQ6/MTKgo0FpjGd0G97BxB5FKxw7WwiSLI9USQuDubnE3xqnQMsgJcAlg2HcQkCMu5P+6H2mer9l3wm127KFDHaZeUvV8feEBX6juz4kguQwwtZg/Op1/Hbjh/+pRvUCnbj+erjLzX4Y1rwYZlTlg3QRTaTbxV+Qhfv5gO7ZTlXSvyCIWhKnUYc8EGT1VpKDhoOdzVM23VT5m9plZKZQsyrMJMD1DP15sh2Tj1/8ECAwEAAaOB4DCB3TAJBgNVHRMEAjAAMBEGCWCGSAGG+EIBAQQEAwIGQDAzBglghkgBhvhCAQ0EJhYkQmFua0lEIFByb2R1Y3Rpb24gQ2xpZW50IENlcnRpZmljYXRlMB0GA1UdDgQWBBRzRJstwoejw003aJ6fquk9rsU1QDAfBgNVHSMEGDAWgBQqoi9yTXXY0beUgU8zj/QtExL35jALBgNVHQ8EBAMCBBAwOwYDVR0fBDQwMjAwoC6gLIYqaHR0cHM6Ly9jYS5iYW5raWQuY3ovY3JsL3Byb2QvcHJvZC5jcmwuY3JsMAoGCCqGSM49BAMDA2gAMGUCMQCDv5oUXSpGdQFgSD9QPzl6pqTRX2zMeFT4OPj3IKSJPrdEi7A4iPTjWs9r2dm9ngsCMEwCMeFbc3iIA6H+iZGDEgls4pOJQAn5qNq1td9VQijqw+XSeGMkwYmtV/SvRlOyyw==',
                ],
                'n' => 'xFLhcDDXnkdcO7CV1gjm4pXu60VFVuVKdYazZ-Bv1EXZ8I6NNQ_yrS0fysyLdaeNEwTrQ2rhb2BjuaR9aOvrPdhFlS2yKZ-k4-wkWeioc6t3jZvb9fJvKpCxozMU8XwC_OVO81G3Az5Gyv_nAGCzNmHRsXUiJBA9gh5OVduBJyAZN6w7s8F4A-QQlSdbMkVduHpUqGlGbvDDZ0zpssJQv2pA3i6y3mfAEPccr75Vgx_le9-6PC_e7BaZFUY_BdP6KmesitPZgD6EACP_QUh21jHn0feGDV-nGkZswPxZp3FCEz6YnkZg24_C6JHOjUee_gATjjjUC-uxpVPLuUGjR-Rf0WMmczMec3LJTfXwhx33ai6nQ02vp8UUGzjfSzF0UiztrWJQ9pRgc4o95h4npcLO-n7uh3NVR2_nHtBPEYGvxxZyX50Ux8HibaHEKZvoQARQ6_MTKgo0FpjGd0G97BxB5FKxw7WwiSLI9USQuDubnE3xqnQMsgJcAlg2HcQkCMu5P-6H2mer9l3wm127KFDHaZeUvV8feEBX6juz4kguQwwtZg_Op1_Hbjh_-pRvUCnbj-erjLzX4Y1rwYZlTlg3QRTaTbxV-Qhfv5gO7ZTlXSvyCIWhKnUYc8EGT1VpKDhoOdzVM23VT5m9plZKZQsyrMJMD1DP15sh2Tj1_8E',
            ],
            [
                'kty' => 'EC',
                'x5t#S256' => 'TnaGIMHLKjxvfx4EQGrXOueG9c8Fk2nlyGsTBVBK2Tw',
                'use' => 'sig',
                'crv' => 'P-384',
                'kid' => 'mtls',
                'x5c' => [
                    'MIIDETCCApagAwIBAgICECowCgYIKoZIzj0EAwMwfjELMAkGA1UEBhMCQ1oxDjAMBgNVBAgMBVByYWhhMSAwHgYDVQQKDBdCYW5rb3ZuaSBpZGVudGl0YSwgYS5zLjEdMBsGA1UEAwwUQmFua0lEIFByb2R1Y3Rpb24gQ0ExHjAcBgkqhkiG9w0BCQEWD2FkbWluQGJhbmtpZC5jejAeFw0yMTAzMDQwNTA1NDdaFw0yMjAzMDQwNTA1NDdaMGExCzAJBgNVBAYTAkNaMQ4wDAYDVQQIDAVQcmFoYTEgMB4GA1UECgwXQmFua292bmkgaWRlbnRpdGEsIGEucy4xIDAeBgNVBAMMF0Jhbmtvdm5pIGlkZW50aXRhLCBhLnMuMHYwEAYHKoZIzj0CAQYFK4EEACIDYgAEG6vPecxuqTN92+g6mFqrLoov7IWt599QUQ23j7oxY4ZmBAMAz2KM7zULau/+X0SPDk9A4mx6nwOfjL4SP1ysEziu5ScLvd4O4v8ql2UA2cFxIqFcLAEpPnWYiSUN2v26o4IBAjCB/zAJBgNVHRMEAjAAMBEGCWCGSAGG+EIBAQQEAwIFoDAzBglghkgBhvhCAQ0EJhYkQmFua0lEIFByb2R1Y3Rpb24gQ2xpZW50IENlcnRpZmljYXRlMB0GA1UdDgQWBBRAQ7pl/4tw0JgPsfAYOdAB1vcAsjAfBgNVHSMEGDAWgBQqoi9yTXXY0beUgU8zj/QtExL35jAOBgNVHQ8BAf8EBAMCBeAwHQYDVR0lBBYwFAYIKwYBBQUHAwIGCCsGAQUFBwMEMDsGA1UdHwQ0MDIwMKAuoCyGKmh0dHBzOi8vY2EuYmFua2lkLmN6L2NybC9wcm9kL3Byb2QuY3JsLmNybDAKBggqhkjOPQQDAwNpADBmAjEA+zx8xKUGv3jS7JjXgxVrxX4ZxCSHd2A7GmLCp0YdS42+X0a0/xP+30nWEd0YG7o1AjEAqJHcip1H0HV3uqw+B5AtssGEH//lt+N5MZZkNWSa8WYtbOunXentBaqOfUmGRqqT',
                ],
                'x' => 'G6vPecxuqTN92-g6mFqrLoov7IWt599QUQ23j7oxY4ZmBAMAz2KM7zULau_-X0SP',
                'y' => 'Dk9A4mx6nwOfjL4SP1ysEziu5ScLvd4O4v8ql2UA2cFxIqFcLAEpPnWYiSUN2v26',
            ],
            [
                'kty' => 'RSA',
                'x5t#S256' => 'VOAJMMCpfJDYdRW1uE_9_Fw8pBA1HJcqmQq_4xFRuWc',
                'e' => 'AQAB',
                'use' => 'sig',
                'kid' => 'rp-sign',
                'x5c' => [
                    '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',
                ],
                'n' => 'xBeRT-xgvxR40MEmJWN-9EPyRl_MxaiWoKAfCG16iM0W0lyBqdej2QKSX70rh6di3YupbNyZeD5Yij037_VPOrrdyyk2Oirw2xx1-aWS6B3T9GXD_Vmfb3k6kw6A9nl4mlDbV7MuWS_fqHldv2QTFCPxX0AQ2ohZJrJiLD3Rbm93W14tzYQUEixUh1YamrtR-iTGHSHOxdTNHqk1r_waTBh-GJiYPxNMd3BruLZrv0cHlH3NdxG-Ge37Rgg6_sI3OEp8qmVul8cazVM4nth1vWLBTeEDY2vmANfZ119vPAaAvDw1vxgI5XBDGR6QZCKF8ifx5bfA5fxEQhLfEtC_lQ',
            ],
        ];
    }
}
