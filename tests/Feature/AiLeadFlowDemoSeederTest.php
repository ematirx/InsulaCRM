<?php

namespace Tests\Feature;

use App\Models\Lead;
use Tests\TestCase;

class AiLeadFlowDemoSeederTest extends TestCase
{
    public function test_ai_leadflow_demo_seed_creates_a_restricted_user_and_curated_leads(): void
    {
        $this->seed(\Database\Seeders\AiLeadFlowDemoSeeder::class);

        $this->assertDatabaseHas('tenants', ['name' => 'AI LeadFlow CRM Demo']);
        $this->assertDatabaseHas('users', ['email' => 'demo.agent@aileadflow.test']);
        $this->assertSame(10, Lead::query()->count());
        $this->assertSame(3, Lead::where('temperature', 'hot')->count());
        $this->assertSame(3, Lead::where('temperature', 'warm')->count());
        $this->assertSame(4, Lead::where('temperature', 'cold')->count());
    }
}
