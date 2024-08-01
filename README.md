# Microsoft Entra ID authentication for Flow applications

Neos Flow package for Microsoft Entra ID authentication

## Installation

Just run

```
composer require steinbauerit/microsoftentraidauthentication
```

## Configuration

```yaml
SteinbauerIT:
  MicrosoftEntraIdAuthentication:
    options:
      clientId: ed491d0d-98de-40cd-b0d4-76c4cc96e680
      clientSecret: 1fk50~k~kJgjUrLnM.Ax3sNfQ6cOzNVkklPWecqP
      tenantId: 0304d5a7-ab72-4656-bc7b-5f98b4c78ccc
    account:
      accountIdentifierProperty: userPrincipalName # properties form additionalAttributes are automatically detected
      authenticationProviderName: Acme.Package:Login
      allowedRoles:
        - Acme.Package:User
        - Acme.Package:Admin
    onAuthenticationSuccess:
      redirectToUri: /dashboard
    # Class execution coming soon
```

## Author

* E-Mail: patric.eckhart@steinbauer-it.com
* URL: http://www.steinbauer-it.com
