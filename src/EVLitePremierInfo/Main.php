<?php

namespace EVLitePremierInfo;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;

class Main extends PluginBase {

    public function onEnable() {
        $this->saveDefaultConfig();
        $config = $this->getConfig()->get('comandos', []);
        $unknownMessage = $this->getConfig()->get('unknown-subcommand', "&cSubcomando '{subcommand}' no existe.");

        if (empty($config)) {
            $this->getLogger()->warning("No hay comandos definidos en config.yml");
            return;
        }

        $commandMap = $this->getServer()->getCommandMap();
        foreach ($config as $commandName => $commandData) {
            if (!is_array($commandData)) {
                $this->getLogger()->warning("El comando '$commandName' no está definido correctamente");
                continue;
            }

            $command = new DynamicCommand($commandName, $commandData, $unknownMessage);
            $commandMap->register($this->getName(), $command);
        }
    }
}
