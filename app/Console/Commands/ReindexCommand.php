<?php

namespace App\Console\Commands;

use App\Models\Paper;
use Elasticsearch\Client;
use Illuminate\Console\Command;

class ReindexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:reindex';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Indexes all papers to Elasticsearch';

    /** @var \Elasticsearch\Client */
    private $elasticsearch;

    public function __construct(Client $elasticsearch)
    {
        parent::__construct();

        $this->elasticsearch = $elasticsearch;

    }

    public function handle()
    {
        $this->info('Indexing all papers...');

        foreach (Paper::cursor() as $paper)
        {
            $this->elasticsearch->index([
                'index' => $paper->getSearchIndex(),
                'type' => $paper->getSearchType(),
                'id' => $paper->getKey(),
                'body' => $paper->toSearchArray(),
            ]);

            // PHPUnit-style feedback
            $this->output->write('.');
        }

        $this->info("\nDone!");
    }
}