<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\LeaveExchangeSeeder;

class SeedLeaveExchange extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'seed:leave-exchange {--fresh : Truncate existing leave exchange data first}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Seed leave exchange (tukar libur) sample data';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $this->info('Starting to seed leave exchange data...');

    $seeder = new LeaveExchangeSeeder();

    if ($this->option('fresh')) {
      $this->info('Clearing existing leave exchange data...');
      $seeder->run();
      $this->info('Leave exchange data has been refreshed!');
    } else {
      $seeder->run();
      $this->info('Leave exchange data has been seeded!');
    }

    $this->info('Leave exchange seeding completed successfully!');
  }
}
