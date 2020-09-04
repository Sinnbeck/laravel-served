<?php

namespace Sinnbeck\LaravelServed\Commands;

use Sinnbeck\LaravelServed\Docker\Docker;
use Illuminate\Console\Command;
use Sinnbeck\LaravelServed\Services\Services;
use Sinnbeck\LaravelServed\Commands\Traits\DockerCheck;

class ServedStartCommand extends Command
{
    use DockerCheck;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'served:start {service?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start container(s)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Docker $docker, Services $services)
    {
        $this->checkPrequisites($docker);
        $servedName = config('served.name');
        $docker->ensureNetworkExists($servedName);

        $onlyService = $this->argument('service');

        $serviceList = $services->prepareServiceList();

        foreach ($serviceList as $service) {
            if ($onlyService && $service->simpleName() !== $onlyService) {
                continue;
            }
            $this->info(sprintf('Starting %s (%s) ...', $service->image()->name(), $service->image()->imageTag()));
            $service->container()->start();

        }

        $this->info('Laravel has been served');
        $this->line('Visit the development server at: http://localhost:' . $serviceList[1]->container()->port());

        return 0;


    }
}