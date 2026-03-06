<?php

namespace Modules\PriceRuleManagement\Console;

use Illuminate\Console\Command;
use Modules\PriceRuleManagement\Jobs\ApplyCatalogPriceRuleJob;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ApplyCatalogPriceRuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'catalog:apply-price-rule';

    /**
     * The console command description.
     */
    protected $description = 'Apply catalog price rules to products and update prices.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ApplyCatalogPriceRuleJob::dispatch();
        $this->info('Catalog price rules applied successfully.');
    }

}
