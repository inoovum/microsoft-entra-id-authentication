<?php
namespace SteinbauerIT\MicrosoftEntraIdAuthentication\Controller;

/*
 * This file is part of the SteinbauerIT.MicrosoftEntraIdAuthentication package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Exception;
use Neos\Flow\Mvc\Controller\ActionController;

use Neos\Flow\Mvc\View\JsonView;
use Neos\Flow\Security\Context;
use SteinbauerIT\AzureAuthPhpClient\GraphApiClient;
use Microsoft\Kiota\Abstractions\ApiException;
use Microsoft\Kiota\Authentication\Oauth\AuthorizationCodeContext;
use Microsoft\Kiota\Authentication\PhpLeagueAuthenticationProvider;
use Microsoft\Kiota\Http\GuzzleRequestAdapter;
use SteinbauerIT\MicrosoftEntraIdAuthentication\Service\AuthenticationService;

#[Flow\Scope("singleton")]
final class AuthenticationController extends ActionController
{

    /**
     * @var string
     */
    protected $defaultViewObjectName = JsonView::class;

    #[Flow\InjectConfiguration(path: 'options.clientId', package: 'SteinbauerIT.MicrosoftEntraIdAuthentication')]
    protected string $clientId;

    #[Flow\InjectConfiguration(path: 'options.clientSecret', package: 'SteinbauerIT.MicrosoftEntraIdAuthentication')]
    protected string $clientSecret;

    #[Flow\InjectConfiguration(path: 'options.tenantId', package: 'SteinbauerIT.MicrosoftEntraIdAuthentication')]
    protected string $tenantId;

    #[Flow\InjectConfiguration(path: 'onAuthenticationSuccess.redirectToUri', package: 'SteinbauerIT.MicrosoftEntraIdAuthentication')]
    protected string $redirectToUri;

    #[Flow\Inject]
    protected AuthenticationService $authenticationService;

    #[Flow\Inject]
    protected Context $securityContext;

    /**
     *
     * @return void
     */
    public function loginAction(): void
    {
        $uri = 'https://login.microsoftonline.com/' . $this->tenantId . '/oauth2/v2.0/authorize?client_id=' . $this->clientId . '&response_type=code&redirect_uri=' . $this->getRedirectUri() . '&response_mode=query&scope=User.Read&state=12345';
        $this->redirectToUri($uri);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function authenticateAction(): void
    {
        if($this->request->hasArgument('code')) {
            try {
                $allowedHosts = ['graph.microsoft.com'];
                $scopes = ['User.Read'];

                $tokenRequestContext = new AuthorizationCodeContext(
                    $this->tenantId,
                    $this->clientId,
                    $this->clientSecret,
                    $this->request->getArgument('code'),
                    $this->getRedirectUri()
                );

                $authProvider = new PhpLeagueAuthenticationProvider($tokenRequestContext, $scopes, $allowedHosts);
                $requestAdapter = new GuzzleRequestAdapter($authProvider);
                $client = new GraphApiClient($requestAdapter);

                $me = $client->me()->get()->wait();

                if($this->authenticationService->authenticate($me)) {
                    $this->redirectToUri($this->redirectToUri);
                }
                $this->view->assign('value', ['response' => 'success', 'account' => $this->securityContext->getAccount()->getAccountIdentifier()]);
            } catch (ApiException $ex) {
                throw new Exception($ex->getMessage(), $ex->getCode());
            }
        }
    }

    /**
     * @return void
     */
    private function getRedirectUri(): string
    {
        return 'https://' . $_SERVER['HTTP_HOST'] . '/mseid/auth';
    }

}
