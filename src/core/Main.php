<?php

namespace core;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{

    public function onEnable(): void
    {
        $this->getLogger()->notice("Plugin Core has been succefully initialized");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        $commandName = $command->getName();
        if ($commandName == "link") {
            if (isset($args[0])) {
                //debuging purposes to be changed
                $this->getLogger()->notice("sending argument");
                $this->sendToBot($args[0]);
                $this->getLogger()->notice("argument sent");

            } else {
                $sender->sendMessage("Missing argument.");
                return false;
            }
        }

        return true;
    }
    public function sendToBot($argument)
    {
        // URL
        $url = 'http://localhost:4007/execute-command';
        $data = json_encode(['command' => $argument]);

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
        $this->getLogger()->notice("Request sent to JavaScript bot.");

        $responseData = json_decode($result, true);
        if ($responseData && isset($responseData['foundUser'])) {
            $foundUser = $responseData['foundUser'];
            //TODO: make a switch case
            if ($foundUser === 1) {
                $this->getLogger()->notice("User found by JavaScript bot.");
                return true;
            } elseif ($foundUser === 2) {
                $this->getLogger()->notice("User not found by JavaScript bot.");
                return false;
            } else {
                $this->getLogger()->error("Invalid response from JavaScript bot.");
                return false;
            }
        } else {
            $this->getLogger()->error("Invalid response from JavaScript bot.");
            return false;
        }
    }






}