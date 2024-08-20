<?php

declare(strict_types=1);

namespace MrxKun\GiftCode;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;

class Main extends PluginBase {
    private Config $codeConfig;
    private Config $playerConfig;
    private Config $messengerConfig;

    public function onEnable(): void {
        $this->codeConfig = new Config($this->getDataFolder() . "code.json", Config::JSON);
        $this->playerConfig = new Config($this->getDataFolder() . "player.json", Config::JSON);
        
        $this->createOrUpdateMessengerConfig();
        
        $this->messengerConfig = new Config($this->getDataFolder() . "messenger.json", Config::JSON);
    }

    private function createOrUpdateMessengerConfig(): void {
        $messengerFile = $this->getDataFolder() . "messenger.json";
        $defaultMessages = [
            'messages' => [
                'code_used' => 'This code has already been used!',
                'code_redeemed' => 'You have successfully redeemed the code and received {amount} coins!',
                'invalid_code' => 'Invalid gift code!',
                'code_expired' => 'This gift code has expired!',
                'code_deleted' => 'Gift code has been successfully deleted!',
                'code_generated' => 'Gift code has been successfully generated!',
                'no_permission' => 'You do not have permission to use this command!',
                'usage_giftcode' => 'Usage: /giftcode <code>',
                'usage_giftcodegen' => 'Usage: /giftcodegen <code> <amount> <expireTime> <quantity>',
                'usage_giftcodedelete' => 'Usage: /giftcodedelete <code>'
            ]
        ];

        if (!file_exists($messengerFile) || filesize($messengerFile) == 0) {
            file_put_contents($messengerFile, json_encode($defaultMessages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $currentMessages = json_decode(file_get_contents($messengerFile), true);
            $updated = false;
            foreach ($defaultMessages['messages'] as $key => $value) {
                if (!isset($currentMessages['messages'][$key])) {
                    $currentMessages['messages'][$key] = $value;
                    $updated = true;
                }
            }
            if ($updated) {
                file_put_contents($messengerFile, json_encode($currentMessages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
    }

    private function getMessage(string $key, array $params = []): string {
        $message = $this->messengerConfig->getNested("messages.$key", "Message not found: $key");
        foreach ($params as $param => $value) {
            $message = str_replace("{{$param}}", (string)$value, $message);
        }
        return $message;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game.");
            return true;
        }

        switch ($command->getName()) {
            case "giftcode":
                $this->sendGiftCodeForm($sender);
                break;
            case "giftcodegen":
                if (!$sender->hasPermission("giftcode.gen")) {
                    $sender->sendMessage($this->getMessage("no_permission"));
                    return true;
                }
                $this->sendGiftCodeGenForm($sender);
                break;
            case "giftcodedelete":
                if (!$sender->hasPermission("giftcode.delete")) {
                    $sender->sendMessage($this->getMessage("no_permission"));
                    return true;
                }
                $this->sendGiftCodeDeleteForm($sender);
                break;
        }
        return true;
    }

    private function sendGiftCodeForm(Player $player): void {
        $form = new CustomForm(function (Player $player, ?array $data) {
            if ($data === null) {
                return;
            }
            $code = $data[0];
            if (empty($code)) {
                $player->sendMessage($this->getMessage("usage_giftcode"));
                return;
            }
            $this->handleGiftCode($player, $code);
        });

        $form->setTitle("Gift Code");
        $form->addInput("Enter Gift Code", "Enter your gift code here");
        $form->sendToPlayer($player);
    }

    private function handleGiftCode(Player $player, string $code): void {
        if ($this->codeConfig->exists($code)) {
            $codeData = $this->codeConfig->get($code);
            if (time() <= $codeData["expireTime"]) {
                $playerName = $player->getName();
                $playerData = $this->playerConfig->get($playerName, []);
                
                if (in_array($code, $playerData)) {
                    $player->sendMessage($this->getMessage("code_used"));
                    return;
                }

                if ($codeData["quantity"] === "x" || $codeData["quantity"] > 0) {
                    $this->addBalance($playerName, $codeData["amount"]);
                    $this->recordPlayerUsage($playerName, $code);
                    $player->sendMessage($this->getMessage("code_redeemed", ["amount" => $codeData["amount"]]));
                    
                    if ($codeData["quantity"] !== "x") {
                        $codeData["quantity"]--;
                        if ($codeData["quantity"] <= 0) {
                            $this->codeConfig->remove($code);
                        } else {
                            $this->codeConfig->set($code, $codeData);
                        }
                    }
                    $this->codeConfig->save();
                } else {
                    $player->sendMessage($this->getMessage("code_used"));
                }
            } else {
                $player->sendMessage($this->getMessage("code_expired"));
            }
        } else {
            $player->sendMessage($this->getMessage("invalid_code"));
        }
    }

    private function sendGiftCodeGenForm(Player $player): void {
        $form = new CustomForm(function (Player $player, ?array $data) {
            if ($data === null) {
                return;
            }
            $code = $data[0];
            $amount = (int)$data[1];
            $expireTime = $data[2] === 'x' ? 'x' : (int)$data[2];
            $quantity = $data[3] === 'x' ? 'x' : (int)$data[3];

            if (empty($code) || empty($amount) || empty($expireTime) || empty($quantity)) {
                $player->sendMessage($this->getMessage("usage_giftcodegen"));
                return;
            }

            $this->handleGiftCodeGen($player, $code, $amount, $expireTime, $quantity);
        });

        $form->setTitle("Generate Gift Code");
        $form->addInput("Code", "Enter the gift code");
        $form->addInput("Amount", "Enter the amount");
        $form->addInput("Expire Time", "Enter expire time in minutes or 'x' for no expiry");
        $form->addInput("Quantity", "Enter quantity or 'x' for unlimited");
        $form->sendToPlayer($player);
    }

    private function handleGiftCodeGen(Player $player, string $code, int $amount, $expireTime, $quantity): void {
        $expireTime = $expireTime === 'x' ? PHP_INT_MAX : time() + ((int)$expireTime * 60);

        $this->codeConfig->set($code, [
            "amount" => $amount,
            "expireTime" => $expireTime,
            "quantity" => $quantity
        ]);
        $this->codeConfig->save();
        $player->sendMessage($this->getMessage("code_generated"));
    }

    private function sendGiftCodeDeleteForm(Player $player): void {
        $form = new CustomForm(function (Player $player, ?array $data) {
            if ($data === null) {
                return;
            }
            $code = $data[0];
            if (empty($code)) {
                $player->sendMessage($this->getMessage("usage_giftcodedelete"));
                return;
            }
            $this->handleGiftCodeDelete($player, $code);
        });

        $form->setTitle("Delete Gift Code");
        $form->addInput("Enter Gift Code to Delete", "Enter the gift code");
        $form->sendToPlayer($player);
    }

    private function handleGiftCodeDelete(Player $player, string $code): void {
        if ($this->codeConfig->exists($code)) {
            $this->codeConfig->remove($code);
            $this->codeConfig->save();
            $player->sendMessage($this->getMessage("code_deleted"));
        } else {
            $player->sendMessage($this->getMessage("invalid_code"));
        }
    }

    private function addBalance(string $username, int $amount): void {
        try {
            BedrockEconomyAPI::legacy()->addToPlayerBalance($username, $amount);
        } catch (\Exception $e) {
            $this->getLogger()->error("Failed to add balance to player $username: " . $e->getMessage());
        }
    }

    private function recordPlayerUsage(string $username, string $code): void {
        $playerData = $this->playerConfig->get($username, []);
        $playerData[] = $code;
        $this->playerConfig->set($username, $playerData);
        $this->playerConfig->save();
    }
}