<?php

namespace Salehhashemi\LaravelIntelliGraphql\Tests;

use Illuminate\Foundation\Console\Kernel;
use Salehhashemi\LaravelIntelliGraphql\Console\AiGraphqlCommand;
use Salehhashemi\LaravelIntelliGraphql\LaravelIntelliGraphqlServiceProvider;

class AiFactoryCommandConsoleTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected function getPackageProviders($app): array
    {
        return [LaravelIntelliGraphqlServiceProvider::class];
    }

    /** @test */
    public function test_ai_graphql_command_is_registered()
    {
        $kernel = $this->app->make(Kernel::class);

        $commandList = $kernel->all();

        $this->assertArrayHasKey('ai:graphql', $commandList);
    }

    /** @test */
    public function test_ai_graphql_command_options()
    {
        $command = $this->app->make(AiGraphqlCommand::class);
        $definition = $command->getDefinition();
        $options = $definition->getOptions();
        $arguments = $definition->getArguments();

        $this->assertArrayHasKey('table', $arguments);
    }

    /** @test */
    public function test_ai_graphql_command()
    {
        $this->artisan('ai:graphql', [
            'table' => 'users',
        ])
            ->assertExitCode(0);

        $this->assertTrue(file_exists(base_path('graphql/users.graphql')));

        // Cleanup
        unlink(base_path('graphql/users.graphql'));
    }
}
