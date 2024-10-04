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

## Usage

#### Please follow the "Sign in with Microsoft: Branding guidelines for applications": https://learn.microsoft.com/en-us/entra/identity-platform/howto-add-branding-in-apps

```neosafx
afx`
    <Neos.Fusion:Link.Action href.package="SteinbauerIT.MicrosoftEntraIdAuthentication" href.controller="Authentication" href.action="login">Login with MS Entry ID</Neos.Fusion:Link.Action>
`
```

```html
  <a href="/mseid/login">Login with MS Entry ID</a>
```

### Optional: With base64 encoded callback uri

This callback uri is used for the redirect.

```html
  <a href="/mseid/login/aHR0cHM6Ly93d3cuZm9vYmFyLmNvbS9ncmFudD90b2tlbj1mMWcyZDRmZzFqa2w0NTY0NWtsNjkweGM5ODBjeHY=">Login with MS Entry ID</a>
```


## Author

* E-Mail: patric.eckhart@steinbauer-it.com
* URL: http://www.steinbauer-it.com
