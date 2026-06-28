<?php

declare(strict_types=1);

namespace EHAERER\EasyVerein\Middleware;

use EHAERER\EasyVerein\Service\UserService;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * This file is part of the "easy_verein" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2023-2026 Ephraim Härer <mail@ephra.im>, EPHRA.IM
 */

/**
 * EasyVereinMiddleware
 */
class EasyVereinMiddleware implements MiddlewareInterface
{
    /** @var ResponseFactoryInterface */
    private ResponseFactoryInterface $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws GuzzleException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $params = $request->getQueryParams();
        /* https://my-domain.uri/?ev-user-data=1 */
        if (isset($params['ev-user-data']) && (int)$params['ev-user-data'] === 1) {
            $frontendUser = $request->getAttribute('frontend.user');
            if ($frontendUser->user && $frontendUser->user['uid'] > 0 && !empty($frontendUser->user['easyverein_pk'])) {
                $easyVereinPk = $frontendUser->user['easyverein_pk'];
                $userService = GeneralUtility::makeInstance(UserService::class);
                $userData = $userService->getUserData($easyVereinPk);
                $response = $this->responseFactory->createResponse()
                    ->withHeader('Content-Type', 'application/json; charset=utf-8');
                $response->getBody()->write(json_encode($userData));
                return $response;
            }
        }
        return $handler->handle($request);
    }

}
