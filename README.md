<div style="display: flex; justify-content: center; align-items: center;">
	<img src="./img/logo.png" style="margin: 0 auto;">
</div>

## About:

This is a plugin that blocks players who use **VPN/Proxy** on their PocketMine **API 5** (PMMP 5) server

## How to use? 

To start using, you need to create an account **(Free/Premium)** at: [**https://vpnapi.io/**](https://vpnapi.io/)

After creating the account, copy the generated key in the dashboard

After copying this key, install the plugin on your server and use the command below:

- `/antivpn setkey <key>` - the `<key>` value, must be your account key generated in the **Dashboard** of **https://vpnapi.io/**

After that your plugin will be working to expel players who try to use VPN 

## Features:

### config.yml:

- `enable-cache` - If true, the system will save all IP addresses with and without proxy that enter the server, for faster checking (without connecting to the API) **RECOMENDED**
- `alert-admins` - If true, it will **alert** all players with the permission: `antivpn.alert.receive`
- `alert-admin-message` - The message that will be sent
- `kick-screen-message` - The message that will be sent on the screen of the player expelled due to suspected VPN/Proxy

### Command `/antivpn`


