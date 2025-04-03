<?php

namespace App\Security;

use App\Service\SessionAuthService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class SessionAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly SessionAuthService $sessionAuthService
    ) {
    }

    /**
     * Vérifier si cet authentificateur supporte la requête actuelle
     */
    public function supports(Request $request): bool
    {
        // Vérifier si la requête contient un cookie de session
        return $request->cookies->has($this->sessionAuthService->getSessionCookieName());
    }

    /**
     * Créer un passport d'authentification à partir du cookie de session
     */
    public function authenticate(Request $request): Passport
    {
        $sessionToken = $request->cookies->get($this->sessionAuthService->getSessionCookieName());
        
        if (!$sessionToken) {
            throw new CustomUserMessageAuthenticationException('Session token manquant.');
        }
        
        // Valider la session
        $user = $this->sessionAuthService->validateSession($sessionToken);
        
        if (!$user) {
            throw new CustomUserMessageAuthenticationException('Session invalide ou expirée.');
        }
        
        // Créer un passport avec l'utilisateur trouvé
        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier())
        );
    }

    /**
     * Appelé en cas de succès d'authentification
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Authentification réussie, pas besoin de réponse spécifique
        return null;
    }

    /**
     * Appelé en cas d'échec d'authentification
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => $exception->getMessage()
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
    
    /**
     * Appelé lorsqu'une authentification est requise mais qu'aucune n'est fournie
     * Implémentation requise par AuthenticationEntryPointInterface
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new JsonResponse(
            ['message' => 'Authentification requise'],
            Response::HTTP_UNAUTHORIZED
        );
    }
} 