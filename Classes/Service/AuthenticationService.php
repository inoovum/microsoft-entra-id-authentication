<?php
namespace SteinbauerIT\MicrosoftEntraIdAuthentication\Service;

/*
 * This file is part of the SteinbauerIT.MicrosoftEntraIdAuthentication package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Account;
use Neos\Flow\Security\AccountRepository;
use Neos\Flow\Security\Authentication\AuthenticationManagerInterface;
use Neos\Flow\Security\Authentication\Token\UsernamePassword;
use Neos\Flow\Security\Authentication\TokenInterface;
use Neos\Flow\Security\Context;
use Neos\Flow\Security\Policy\PolicyService;
use Psr\Log\LoggerInterface;
use Neos\Flow\Log\Utility\LogEnvironment;
use SteinbauerIT\AzureAuthPhpClient\Models\User;

class AuthenticationService
{

    #[Flow\InjectConfiguration(path: 'account.accountIdentifierProperty', package: 'SteinbauerIT.MicrosoftEntraIdAuthentication')]
    protected string $accountIdentifierProperty;

    #[Flow\InjectConfiguration(path: 'account.authenticationProviderName', package: 'SteinbauerIT.MicrosoftEntraIdAuthentication')]
    protected string $authenticationProviderName;

    #[Flow\InjectConfiguration(path: 'account.allowedRoles', package: 'SteinbauerIT.MicrosoftEntraIdAuthentication')]
    protected array $allowedRoles;

    #[Flow\Inject]
    protected AccountRepository $accountRepository;

    #[Flow\Inject]
    protected AuthenticationManagerInterface $authenticationManager;

    #[Flow\Inject]
    protected PolicyService $policyService;

    #[Flow\Inject]
    protected Context $securityContext;

    /**
     * @Flow\Inject(name="Neos.Flow:SecurityLogger")
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param User $user
     * @return bool
     * @throws \Exception
     */
    public function authenticate(User $user): bool
    {
        if(array_key_exists($this->accountIdentifierProperty, $user->getAdditionalData())) {
            $accountIdentifier = $user->getAdditionalData()[$this->accountIdentifierProperty];
        } else {
            $accountIdentifier = $user[$this->accountIdentifierProperty];
        }

        $this->logger->debug(sprintf('Microsoft Entra ID Authentication: Starting authentication for user "%s" ...', $accountIdentifier), LogEnvironment::fromMethodName(__METHOD__));

        $account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($accountIdentifier, $this->authenticationProviderName);

        if($account !== null && $this->rolesAreAllowed($account)) {

            $account->authenticationAttempted(TokenInterface::AUTHENTICATION_SUCCESSFUL);

            $tokens = $this->securityContext->getAuthenticationTokensOfType(UsernamePassword::class);

            /* @var UsernamePassword $token */
            foreach ($tokens as $token) {
                $token->setAccount($account);
                $token->setAuthenticationStatus(TokenInterface::AUTHENTICATION_SUCCESSFUL);
            }
            $this->authenticationManager->authenticate();

            $this->logger->debug(sprintf('Microsoft Entra ID Authentication: Successfully authenticated account "%s" with authentication provider %s.', $account->getAccountIdentifier(), $account->getAuthenticationProviderName()), LogEnvironment::fromMethodName(__METHOD__));

            return true;

        }

        $this->logger->debug(sprintf('Microsoft Entra ID Authentication: The roles of account %s are not in the allowedRoles in the Configuration or the account does not exist.', $account->getAccountIdentifier()), LogEnvironment::fromMethodName(__METHOD__));
        return false;
    }

    /**
     * @param Account $account
     * @return bool
     */
    public function rolesAreAllowed(Account $account): bool
    {
        $roles = $account->getRoles();
        foreach ($roles as $role) {
            if(in_array($role->getIdentifier(), $this->allowedRoles)) {
                return true;
            }
        }
        return false;
    }

}
