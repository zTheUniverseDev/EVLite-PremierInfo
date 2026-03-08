<?php

namespace EVLitePremierInfo;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\level\sound\ExpPickupSound;
use pocketmine\level\sound\FizzSound;
use pocketmine\level\sound\GhastShootSound;

class DynamicCommand extends Command {

    private $commandConfig;
    private $unknownMessage;

    public function __construct($name, $config, $unknownMessage) {
        parent::__construct($name, "Comando personalizado", "/$name", []);
        $this->commandConfig = $config;
        $this->unknownMessage = $unknownMessage;
    }

    public function execute(CommandSender $sender, $label, array $args) {
        $node = $this->commandConfig;

        if (empty($args)) {
            $this->sendHelp($sender, $node);
            return true;
        }

        foreach ($args as $index => $arg) {
            if (isset($node[$arg]) && is_array($node[$arg])) {
                $node = $node[$arg];
            } else {
                $this->sendUnknown($sender, $arg);
                return true;
            }
        }

        if (isset($node['_texto_'])) {
            $message = $node['_texto_'];
            $message = str_replace('{player}', $sender->getName(), $message);
            $message = str_replace('{online}', count($sender->getServer()->getOnlinePlayers()), $message);
            $message = $this->translateColors($message);
            $sender->sendMessage($message);

            if ($sender instanceof Player && isset($node['_sonido_'])) {
                $this->playSound($sender, $node['_sonido_']);
            }
        } else {
            $this->sendUnknown($sender, end($args));
        }

        return true;
    }

    private function sendHelp(CommandSender $sender, $node) {
        if (isset($node['_ayuda_'])) {
            $helpMsg = $node['_ayuda_'];
        } else {
            $subcommands = array_filter(array_keys($node), function($key) {
                return $key[0] !== '_';
            });
            if (empty($subcommands)) {
                $helpMsg = "&cNo hay subcomandos disponibles.";
            } else {
                $helpMsg = "&aUsa: &e/" . $this->getName() . " " . implode(" &7| &e/" . $this->getName() . " ", $subcommands);
            }
        }
        $helpMsg = str_replace('{player}', $sender->getName(), $helpMsg);
        $helpMsg = str_replace('{online}', count($sender->getServer()->getOnlinePlayers()), $helpMsg);
        $helpMsg = str_replace('{command}', $this->getName(), $helpMsg);
        $helpMsg = $this->translateColors($helpMsg);
        $sender->sendMessage($helpMsg);
    }

    private function sendUnknown(CommandSender $sender, $badSubcommand) {
        $msg = str_replace('{subcommand}', $badSubcommand, $this->unknownMessage);
        $msg = str_replace('{player}', $sender->getName(), $msg);
        $msg = str_replace('{online}', count($sender->getServer()->getOnlinePlayers()), $msg);
        $msg = str_replace('{command}', $this->getName(), $msg);
        $msg = $this->translateColors($msg);
        $sender->sendMessage($msg);
    }

    private function playSound(Player $player, $soundType) {
        $position = $player->getPosition();
        $sound = null;

        switch ($soundType) {
            case 'positivo':
                $sound = new ExpPickupSound($position);
                break;
            case 'info':
                $sound = new FizzSound($position);
                break;
            case 'negativo':
                $sound = new GhastShootSound($position);
                break;
        }

        if ($sound !== null) {
            $player->getLevel()->addSound($sound, [$player]);
        }
    }

    private function translateColors($message) {
        return str_replace('&', '§', $message);
    }
}
