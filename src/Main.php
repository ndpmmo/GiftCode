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
    private Config $messengerConfig;

    public function onEnable(): void {
        $this->codeConfig = new Config($this->getDataFolder() . "code.json", Config::JSON);
        $this->saveResource("messenger.yml");
        $this->messengerConfig = new Config($this->getDataFolder() . "messenger.yml", Config::YAML);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "giftcode") {
            if ($sender instanceof Player) {
                $this->showGiftcodeForm($sender);
            } else {
                $sender->sendMessage($this->messengerConfig->get("messages")["only_game"]);
            }
        } elseif ($command->getName() === "giftcodegen") {
            if (!$sender->hasPermission("giftcode.gen")) {
                $sender->sendMessage($this->messengerConfig->get("messages")["no_permission"]);
                return true;
            }

            if ($sender instanceof Player) {
                $this->showGiftcodeGenForm($sender);
            } else {
                $sender->sendMessage($this->messengerConfig->get("messages")["only_game"]);
            }
        } elseif ($command->getName() === "giftcodedelete") {
            if (!$sender->hasPermission("giftcode.delete")) {
                $sender->sendMessage($this->messengerConfig->get("messages")["no_permission"]);
                return true;
            }

            if ($sender instanceof Player) {
                $this->showGiftcodeDeleteForm($sender);
            } else {
                $sender->sendMessage($this->messengerConfig->get("messages")["only_game"]);
            }
        }
        return true;
    }

    private function showGiftcodeForm(Player $player): void {
        $form = new CustomForm(function (Player $player, $data = null) {
            if ($data === null) {
                return;
            }

            $code = $data[0];
            if ($this->codeConfig->exists($code)) {
                $codeData = $this->codeConfig->get($code);
                if (time() <= $codeData["expireTime"]) {
                    $this->addBalance($player->getName(), $codeData["amount"]);
                    $player->sendMessage(str_replace("{amount}", strval($codeData["amount"]), $this->messengerConfig->get("messages")["giftcode_redeemed"]));
                    $this->codeConfig->remove($code);
                    $this->codeConfig->save();
                } else {
                    $player->sendMessage($this->messengerConfig->get("messages")["giftcode_expired"]);
                }
            } else {
                $player->sendMessage($this->messengerConfig->get("messages")["giftcode_invalid"]);
            }
        });

        $form->setTitle("Giftcode Redemption");
        $form->addInput("Enter the giftcode you want to redeem:", "Enter giftcode here");
        $form->sendToPlayer($player);
    }

    private function showGiftcodeGenForm(Player $player): void {
        $form = new CustomForm(function (Player $player, $data = null) {
            if ($data === null) {
                return;
            }

            $code = $data[0];
            $amount = (int)$data[1];
            $expireTime = $data[2];

            if ($expireTime === "x" || $expireTime === "X") {
                $expireTime = PHP_INT_MAX; // Thời gian tồn tại vĩnh viễn
            } else {
                $expireTime = time() + ((int)$expireTime * 60);
            }

            $this->codeConfig->set($code, ["amount" => $amount, "expireTime" => $expireTime]);
            $this->codeConfig->save();
            $player->sendMessage($this->messengerConfig->get("messages")["giftcode_generated"]);
        });

        $form->setTitle("Giftcode Generation");
        $form->addInput("Enter the giftcode:", "Enter giftcode here");
        $form->addInput("Enter the amount:", "Enter amount here", "100");
        $form->addInput("Enter the expire time (in minutes, or 'x' for permanent):", "Enter expire time here", "1440");
        $form->sendToPlayer($player);
    }

    private function showGiftcodeDeleteForm(Player $player): void {
        $form = new CustomForm(function (Player $player, $data = null) {
            if ($data === null) {
                return;
            }

            $code = $data[0];
            if ($this->codeConfig->exists($code)) {
                $this->codeConfig->remove($code);
                $this->codeConfig->save();
                $player->sendMessage($this->messengerConfig->get("messages")["giftcode_deleted"]);
            } else {
                $player->sendMessage($this->messengerConfig->get("messages")["giftcode_invalid"]);
            }
        });

        $form->setTitle("Giftcode Deletion");
        $form->addInput("Enter the giftcode you want to delete:", "Enter giftcode here");
        $form->sendToPlayer($player);
    }

    private function addBalance(string $username, int $amount): void {
        try {
            BedrockEconomyAPI::legacy()->addToPlayerBalance($username, $amount);
        } catch (\Exception $e) {
            $this->getLogger()->error("Failed to add balance to player $username: " . $e->getMessage());
        }
    }
}