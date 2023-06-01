<?php

namespace Salehhashemi\LaravelIntelliGraphql\Console;

use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Schema;
use Salehhashemi\LaravelIntelliGraphql\OpenAi;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class AiGraphqlCommand.
 *
 * A Laravel console command to create a new GraphQL schema, queries, and mutations using AI.
 */
class AiGraphqlCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $name = 'ai:graphql';

    /**
     * The console command description.
     */
    protected $description = 'Create a new GraphQL schema, queries, and mutations using AI';

    public function __construct(private readonly OpenAi $openAi)
    {
        parent::__construct();
    }

    /**
     * Configure the command options.
     */
    protected function configure(): void
    {
        $this->addArgument('table', InputOption::VALUE_REQUIRED, 'The name of the table');
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $table = $this->getTableArgument();

        if (! Schema::hasTable($table)) {
            $this->error("The table '{$table}' does not exist.");

            return 1;
        }

        $schema = $this->getSchemaForTable($table);
        $prompt = $this->createAiPrompt($table, $schema);

        $this->info('Generating AI GraphQL schema, queries, and mutations, this might take a few moments...');

        try {
            $graphqlContent = $this->fetchAiGeneratedContent($prompt);
            $this->createGraphqlFile($table, $graphqlContent);
        } catch (RequestException $e) {
            $this->error('Error fetching AI-generated content: '.$e->getMessage());
        }

        return 0;
    }

    /**
     * Get the 'table' argument or prompt the user if it's not provided.
     */
    private function getTableArgument(): string
    {
        $table = $this->argument('table');

        if (! $table) {
            $table = $this->ask('What should the table be named?');
        }

        return $table;
    }

    /**
     * Get the schema for the provided table.
     *
     * @return array The schema for the table
     */
    private function getSchemaForTable(string $table): array
    {
        return Schema::getColumnListing($table);
    }

    /**
     * Create an AI prompt using the provided information.
     */
    private function createAiPrompt(string $table, array $schema): string
    {
        $prompt = "Generate a GraphQL schema, queries, and mutations for a table named '{$table}'.";
        $prompt .= "\nProvide only the final code without any explanations or additional context.";
        $prompt .= "\nThe current schema of the table is as follows:\n".implode(', ', $schema);

        return $prompt;
    }

    /**
     * Fetch AI-generated content using the provided prompt.
     *
     * @param  string  $prompt  The AI prompt
     * @return string The AI-generated content
     *
     * @throws RequestException
     */
    private function fetchAiGeneratedContent(string $prompt): string
    {
        return $this->openAi->execute($prompt, 3500);
    }

    /**
     * Create a GraphQL file using the provided table name and content.
     *
     * @param  string  $table  The table name
     * @param  string  $content  The GraphQL content
     */
    private function createGraphqlFile(string $table, string $content): void
    {
        $path = base_path('graphql');
        $name = "{$table}.graphql";
        $filepath = "{$path}/{$name}";

        if (! file_exists($path)) {
            mkdir($path, 0755, true);
        }

        file_put_contents($filepath, $content);

        $this->info(sprintf('GraphQL schema for table [%s] created successfully.', $table));
    }
}
