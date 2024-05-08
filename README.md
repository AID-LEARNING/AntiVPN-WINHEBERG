<p align="center">
	<img src="./img/logo.png" style="margin: 0 auto;">
</p>

## About:

This is a plugin that blocks players who use **VPN/Proxy** on their PocketMine **API 5** (PMMP 5) server
This plugin was developed for [**WINHEBERG**](https://winheberg.fr/)  customers.


The fork's goal is to improve the basic plugin, which only embeds vpnapi.io, but with this version you'll get better detection with iphub.info.
In particular, it detects Cloudflare WARP VPNs and Hosting, proxy or bad IPs.

## How to use? 

VPNAPI.IO - To start using, you need to create an account **(Free/Premium)** at: [**https://vpnapi.io/**](https://vpnapi.io/) 


IPHUB.INFO - To start using, you need to create an account **(Free/Premium)** at: [**https://vpnapi.io/**](https://iphub.info/login)

Once you've created your account on either platform, copy the generated API key from your dashboard.

Next, navigate to the configuration file: `config.yml`

Finally, add the API keys under the appropriate section.

## Features:

### config.yml:

- `enable-cache` - If true, the system will save all IP addresses with and without proxy that enter the server, for faster checking (without connecting to the API) **RECOMENDED**
- `alert-admins` - If true, it will **alert** all players with the permission: `antivpn.alert.receive`
- `alert-admin-message` - The message that will be sent
- `kick-screen-message` - The message that will be sent on the screen of the player expelled due to suspected VPN/Proxy

### Command `/antivpn`

- `/antivpn` 
  - `whitelist` 
    - `add`: Add players who will be ignored by the system
    - `remove`: Remove players who are being ignored
    - `list`: View the list of players who are being ignored 

### UI 

The whitelist system is configurable via the **UI** (when you execute the command from the game and not from the console)

To access the **UI** just use: `/antivpn whitelist`

### Bypass 

- ⚠️ Players who have permission: `antivpn.bypass` will be ignored just like the **whitelist**

## Fork author 

- **SenseiTarzan**


## Author:

- **Rajador**:
  - ✉**Discord**: [**My Group**](https://discord.gg/DV5DgDSq7W)
  - 📷**Instagram**: [**My Instagram**](https://www.instagram.com/rajadortv/)
  - 📽**YouTube**: [**Channel**](https://www.youtube.com/channel/UC1UJFxth-YRkNuLBqBYyqbA)
 

