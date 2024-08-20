# GiftcodePlugin

GiftcodePlugin is a PocketMine-MP plugin that allows players to redeem giftcodes and receive rewards in the form of in-game currency. It also provides functionality for server administrators to generate and manage giftcodes.

## Features

- Players can redeem giftcodes using the `/giftcode` command and receive the specified amount of in-game currency.
- Server administrators can generate new giftcodes with a specified code, amount, and expiration time using the `/giftcodegen` command.
- Server administrators can delete existing giftcodes using the `/giftcodedelete` command.
- Giftcodes can have an expiration time in minutes, or they can be set to never expire by specifying 'x' as the expiration time.
- The plugin integrates with the BedrockEconomy plugin to handle the distribution of in-game currency.
- Customizable messages for various events and notifications, which can be configured in the `messenger.yml` file.

## Requirements

- PocketMine-MP server software
- BedrockEconomy plugin (installed and configured)
- FormAPI plugin (installed)

## Installation

1. Download the GiftcodePlugin `.phar` file.
2. Place the `.phar` file in the `plugins` directory of your PocketMine-MP server.
3. Restart your server to load the plugin.

## Configuration

The plugin creates a `messenger.yml` file in the plugin's data directory upon first run. This file contains the configurable messages for various events and notifications. You can modify the messages to suit your preferences.

## Usage

### Player Commands

- `/giftcode <code>`: Redeem a giftcode and receive the associated reward.

### Admin Commands

- `/giftcodegen <code> <amount> <expireTime>`: Generate a new giftcode with the specified code, amount, and expiration time (in minutes). Use 'x' for the expiration time to create a permanent giftcode.
- `/giftcodedelete <code>`: Delete an existing giftcode.

## Permissions

- `giftcode.use`: Allows players to use the `/giftcode` command to redeem giftcodes. (Default: true)
- `giftcode.gen`: Allows players to use the `/giftcodegen` command to generate new giftcodes. (Default: op)
- `giftcode.delete`: Allows players to use the `/giftcodedelete` command to delete giftcodes. (Default: op)

## Support and Contributions

If you encounter any issues or have suggestions for improvements, please feel free to open an issue on the plugin's GitHub repository. Contributions in the form of pull requests are also welcome.

## License

This plugin is released under the [MIT License](https://opensource.org/licenses/MIT). You are free to use, modify, and distribute the plugin as per the terms of the license.

## Credits

- Plugin developed by MrxKun
- BedrockEconomy plugin by [cooldogedev](https://github.com/cooldogedev/BedrockEconomy)
- FormAPI plugin by [jojoe77777](https://github.com/jojoe77777/FormAPI)

Enjoy using the GiftcodePlugin! :smile:
