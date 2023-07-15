<?php

namespace core;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;


class Main extends PluginBase implements Listener {

    public function onEnable(): void {
        $this->getLogger()->notice("Plugin Core has been successfully initialized");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        $commandName = $command->getName();
        if ($commandName == "link") {
            if (isset($args[0])) {
                // Debugging purposes to be changed
                $this->getLogger()->notice("sending argument");
                $this->sendToBot($sender->getName(), $args[0], $sender);
                $this->getLogger()->notice("argument sent");
            } else {;
                $sender->sendMessage(TextFormat::RED . "error missing args");
                return false;
            }
        }

        return true;
    }

    public function sendToBot($username, $argument, $sender) {
        // URL
        $url = 'http://localhost:4015/execute-command';
        $data = json_encode(['command' => $argument, 'username' => $username]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $result = curl_exec($ch);

        if ($result === false) {
            $this->getLogger()->error("Failed to send request to JavaScript bot: " . curl_error($ch));
            curl_close($ch);
            return false;
        }

        curl_close($ch);
        $responseData = json_decode($result, true);
        if ($responseData && isset($responseData['foundUser'])) {
            $foundUser = $responseData['foundUser'];
            switch ($foundUser) {
                case 1:
                    $sender->sendMessage(TextFormat::GREEN . "Successefully linked with Discord.");
                    return true;
                case 2:
                    $sender->sendMessage(TextFormat::RED . "error userID dosn't exists");
                    return false;
                case 3:
                    $sender->sendMessage(TextFormat::RED . "Join the discord first -> discord.gg/.");
                    return false;
                case 4:
                    $this->getLogger()->error("Error: Channel you setup dosn't exist");
                    return false;
                case 5:
                    $sender->sendMessage(TextFormat::RED . "Account already linked.");
                    return false;
                case 6:
                    $this->getLogger()->notice("Error: id dosn't exist");
                    return false;
                case 7:
                    $sender->sendMessage(TextFormat::RED . "Linking invitation has been declined.");
                    return false;
                case 8:
                    $sender->sendMessage(TextFormat::RED . "Invitation has expired.");
                    return false;
                default:
                    $this->getLogger()->error("Invalid response from JavaScript bot.");
                    return false;
            }
        } else {
            $this->getLogger()->error("Invalid response from JavaScript bot.");
            return false;
        }
    }
}
