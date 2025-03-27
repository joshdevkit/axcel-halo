<?php

namespace App\Core\Console\Commands;

use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand as DoctrineMigrateCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use App\Core\Eloquent\Database\ConnectionManager;

class MigrateCommand
{
    public function execute(array $arguments): void
    {
        try {
            echo "🔄 Checking for new migrations...\n";

            $dbalConnection = ConnectionManager::getDbalConnection();
            $configFile = __DIR__ . '/../../../../config/migrations.php';

            if (!file_exists($configFile)) {
                throw new \Exception("❌ Migration config file not found: {$configFile}");
            }

            $config = new PhpFile($configFile);
            $dependencyFactory = DependencyFactory::fromConnection($config, new ExistingConnection($dbalConnection));
            $output = new ConsoleOutput();

            $statusCommand = new StatusCommand($dependencyFactory);
            $statusInput = new ArrayInput([]);
            $statusCommand->run($statusInput, $output);

            $migrateCommand = new DoctrineMigrateCommand($dependencyFactory);
            $migrateInput = new ArrayInput([]);
            $migrateCommand->run($migrateInput, $output);

            echo "✅ Migration completed successfully.\n";
        } catch (\Throwable $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }
}
