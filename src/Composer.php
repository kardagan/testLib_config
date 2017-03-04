<?php

use Composer\Script\Event;

class Composer {
	public static function integrate( Event $event ) {
		$event->getIO()->write("Show me after INSTALL/UPDATE command");
		if ( copy ( 'grumphp.yml' , $event->getComposer()->getConfig()->get('vendor-dir') . '/../grumphp.yml' ) ) {
			$event->getIO()->write('<fg=green>Copie du fichier grumphp.yml effectuée</fg=green>');
		} else {
			$event->getIO()->write('<fg=red>Erreur lors de la copie du fichier grumphp.yml</fg=red>');
		}
	}
}
