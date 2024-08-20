# GiftCode Plugin

## Description

The GiftCode plugin is a powerful and flexible tool for Minecraft PocketMine-MP servers that allows administrators to create, manage, and distribute gift codes to players. These codes can be redeemed for in-game currency, providing an engaging way to reward players or run promotional events.

## Features

- **Create Gift Codes**: Administrators can generate unique gift codes with customizable parameters.
- **Redeem Codes**: Players can easily redeem gift codes to receive in-game currency.
- **Expiration System**: Set time limits on gift codes to create urgency or run time-limited promotions.
- **Usage Limits**: Define how many times a code can be used, from single-use to unlimited.
- **User-Friendly Interface**: Utilizes FormAPI to provide an intuitive GUI for all functions.
- **Customizable Messages**: All plugin messages can be customized through a configuration file.

## Commands

### For Players:
- `/giftcode`: Opens a form to redeem a gift code.

### For Administrators:
- `/giftcodegen`: Opens a form to generate a new gift code.
- `/giftcodedelete`: Opens a form to delete an existing gift code.

## Usage

### Redeeming a Gift Code
1. Type `/giftcode` in the chat.
2. Enter the gift code in the form that appears.
3. If valid, the code will be redeemed, and you'll receive the specified amount of in-game currency.

### Generating a Gift Code (Admin Only)
1. Type `/giftcodegen` in the chat.
2. Fill in the following information in the form:
   - Code: The unique code for the gift.
   - Amount: The amount of currency to be awarded.
   - Expire Time: Time in minutes until the code expires (use 'x' for no expiry).
   - Quantity: Number of times the code can be used (use 'x' for unlimited).
3. Submit the form to create the gift code.

### Deleting a Gift Code (Admin Only)
1. Type `/giftcodedelete` in the chat.
2. Enter the code you wish to delete in the form.
3. Confirm to remove the gift code from the system.

## Permissions

- `giftcode.use`: Allows players to use the `/giftcode` command (default: true).
- `giftcode.gen`: Allows players to generate gift codes (default: op).
- `giftcode.delete`: Allows players to delete gift codes (default: op).

## Configuration

The plugin uses three configuration files:

1. `code.json`: Stores information about active gift codes.
2. `player.json`: Keeps track of which players have used which codes.
3. `messenger.json`: Contains all customizable messages used by the plugin.

## Dependencies

- FormAPI: Required for the user interface.
- BedrockEconomy: Used for managing in-game currency transactions.

## Installation

1. Place the GiftCode plugin file in your server's `plugins` folder.
2. Ensure that FormAPI and BedrockEconomy are installed and enabled.
3. Restart your server or use a plugin manager to load the GiftCode plugin.
4. Configure the plugin as needed through the generated configuration files.

---

This plugin offers a robust gift code system that can enhance player engagement and provide flexible options for server administrators to reward their community.