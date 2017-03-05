<?php

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvents;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    const PACKAGE_NAME = 'kardagan/testlibconf';
    const FILE_NAME = 'grumphp.yml';

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * @var bool
     */
    protected $configureScheduled = false;

    /**
     * @var bool
     */
    protected $initScheduled = false;

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * Attach package installation events:
     *
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => 'postPackageInstall',
            PackageEvents::POST_PACKAGE_UPDATE => 'postPackageUpdate',
            PackageEvents::PRE_PACKAGE_UNINSTALL => 'prePackageUninstall',
            ScriptEvents::POST_INSTALL_CMD => 'runScheduledTasks',
            ScriptEvents::POST_UPDATE_CMD => 'runScheduledTasks',
        ];
    }

    /**
     * When this package is updated
     *
     * @param PackageEvent $event
     */
    public function postPackageInstall(PackageEvent $event)
    {
        $this->copyConfigFile($event);
    }

    /**
     * When this package is updated
     *
     * @param PackageEvent $event
     */
    public function postPackageUpdate(PackageEvent $event)
    {
        $this->copyConfigFile($event);
    }

    /**
     * When this package is uninstalled
     *
     * @param PackageEvent $event
     */
    public function prePackageUninstall(PackageEvent $event)
    {
        $this->removeConfigFile($event);
    }

    /**
     * @param Event $event
     */
    public function runScheduledTasks(PackageEvent $event)
    {
        $this->copyConfigFile($event);
    }

    private function copyConfigFile(PackageEvent $event)
    {
        $event->getIO()->write('<fg=white>Copie du fichier ' . self::FILE_NAME . ' à la racine.</fg=white>');
        if (copy($this->getSourcePath($event), $this->getDestPath($event))) {
            $event->getIO()->write('<fg=green>Copie du fichier ' . self::FILE_NAME . ' effectuée</fg=green>');
        } else {
            $event->getIO()->write('<fg=red>Erreur lors de la copie du fichier ' . self::FILE_NAME . '</fg=red>');
        }
    }

    private function removeConfigFile(PackageEvent $event)
    {
        $event->getIO()->write('<fg=white>Suppression du fichier grumphp.yml à la racine.</fg=white>');
        if (unlink($this->getSourcePath($event))) {
            $event->getIO()->write('<fg=green>Suppression du fichier grumphp.yml effectuée</fg=green>');
        } else {
            $event->getIO()->write('<fg=red>Erreur lors de la suppression du fichier grumphp.yml</fg=red>');
        }
    }

    private function getSourcePath(PackageEvent $event)
    {
        return $event->getComposer()->getConfig()->get('vendor-dir') . '/' . self::PACKAGE_NAME . '/config/' . self::FILE_NAME;
    }

    private function getDestPath(PackageEvent $event)
    {
        return $event->getComposer()->getConfig()->get('vendor-dir') . '/../' . self::FILE_NAME;
    }
}
