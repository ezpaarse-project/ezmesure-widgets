# ezMESURE Widgets

This Wordpress site demonstrates how to use the [ezMESURE](https://ezmesure.couperin.org) API in order to show charts and metrics to users authenticated via Shibboleth.

## Prerequisite
A recent version of Docker and Docker-Compose.

## Install

### Clone !

```bash
  git clone https://github.com/ezpaarse-project/ezmesure-widgets.git
```

### Configuration

1) Put the private key (``server.key``) and the certificate (``server.crt``) used to declare the service provider in the [fédération d'identités Education-Recherche](https://federation.renater.fr/registry?action=get_all) in ``rp/shibboleth/ssl/``.
**NB**: the private key is critical and should not be shared.

2) Set the following environment variables :
- APPLI_APACHE_SERVERNAME
- APPLI_APACHE_SERVERADMIN
- APPLI_APACHE_LOGLEVEL
- ENTITY_ID
- MYSQL_USER
- MYSQL_PASSWORD
- MYSQL_DATABASE

3) Configure shibboleth
```bash
  make config
```

5) The authentication process requires the user to be located at `ezmesure-preprod.couperin.org`. If working on localhost, add the following line into `/etc/hosts`:
```
127.0.0.1 ezmesure-preprod.couperin.org
```

## Usage
```bash
  make start   # start ezMESURE Widgets
  make stop    # stop ezMESURE Widgets
  make cleanup # stop and remove all docker containers
```

## Configure Shibboleth on Wordpress

1) Connect to http://ezmesure-preprod.couperin.org and follow the installation instructions.

2) Install the Shibboleth plugin and activate it

3) Go to Shibboleth settings
  - Under "General"
    - Change Login URL to https://ezmesure-preprod.couperin.org/Shibboleth.sso/Login
    - Change Logout URL to https://ezmesure-preprod.couperin.org/Shibboleth.sso/Logout
    - Set Attribute Access to "HTTP Headers"
    - Tick "Default Login Method"
  - Under "Users"
    - Tick "Automatically Create Accounts"

## Known issues
  - Mounting the `wp-content` directory with docker results in the impossibility to choose language upon installation.
