<?php

namespace Tests\Feature;

use App\Models\Coffret;
use App\Models\Equipement;
use App\Models\Liaison;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'administrator', 'is_active' => true]);
        $this->user = User::factory()->create(['role' => 'user', 'is_active' => true]);
    }

    private function createCsvFile(array $headers, array $rows): UploadedFile
    {
        $content = "\xEF\xBB\xBF"; // BOM
        $content .= implode(';', $headers) . "\n";
        foreach ($rows as $row) {
            $content .= implode(';', $row) . "\n";
        }

        return UploadedFile::fake()->createWithContent('import.csv', $content);
    }

    public function test_import_coffrets_csv_creates_records(): void
    {
        $file = $this->createCsvFile(
            ['Code', 'Nom', 'Pièce', 'Type', 'Status'],
            [
                ['COF-001', 'Coffret Alpha', 'Salle A', 'armoire', 'active'],
                ['COF-002', 'Coffret Beta', 'Salle B', 'baie', 'inactive'],
            ]
        );

        $response = $this->actingAs($this->admin)->post('/api/v1/imports/coffrets/csv', [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.imported', 2);
        $response->assertJsonPath('data.updated', 0);
        $this->assertDatabaseHas('coffrets', ['code' => 'COF-001', 'name' => 'Coffret Alpha']);
        $this->assertDatabaseHas('coffrets', ['code' => 'COF-002', 'name' => 'Coffret Beta']);
    }

    public function test_import_coffrets_csv_upserts_existing(): void
    {
        Coffret::factory()->create(['code' => 'COF-001', 'name' => 'Old Name']);

        $file = $this->createCsvFile(
            ['Code', 'Nom', 'Pièce', 'Type', 'Status'],
            [['COF-001', 'New Name', 'Salle X', 'baie', 'active']],
        );

        $response = $this->actingAs($this->admin)->post('/api/v1/imports/coffrets/csv', [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.imported', 0);
        $response->assertJsonPath('data.updated', 1);
        $this->assertDatabaseHas('coffrets', ['code' => 'COF-001', 'name' => 'New Name']);
    }

    public function test_import_with_validation_errors_returns_errors(): void
    {
        $file = $this->createCsvFile(
            ['Code', 'Nom', 'Pièce', 'Status'],
            [
                ['COF-001', 'Coffret A', 'Salle A', 'active'],
                ['', 'Missing Code', 'Salle B', 'active'],   // missing required code
                ['COF-003', 'Coffret C', 'Salle C', 'invalid_status'], // bad status
            ],
        );

        $response = $this->actingAs($this->admin)->post('/api/v1/imports/coffrets/csv', [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.imported', 1);
        $this->assertCount(2, $response->json('data.errors'));
    }

    public function test_import_rejects_invalid_file_type(): void
    {
        $file = UploadedFile::fake()->create('data.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->admin)->postJson('/api/v1/imports/coffrets/csv', [
            'file' => $file,
        ]);

        $response->assertStatus(422);
    }

    public function test_import_rejects_missing_file(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/v1/imports/coffrets/csv', []);

        $response->assertStatus(422);
    }

    public function test_user_cannot_import(): void
    {
        $file = $this->createCsvFile(['Code', 'Nom'], [['COF-001', 'Test']]);

        $response = $this->actingAs($this->user)->post('/api/v1/imports/coffrets/csv', [
            'file' => $file,
        ]);

        $response->assertStatus(403);
    }

    public function test_template_download_coffrets(): void
    {
        $response = $this->actingAs($this->admin)->get('/api/v1/imports/coffrets/template');

        $response->assertStatus(200)
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Code', $content);
        $this->assertStringContainsString('Nom', $content);
    }

    public function test_template_download_equipements(): void
    {
        $response = $this->actingAs($this->admin)->get('/api/v1/imports/equipements/template');

        $response->assertStatus(200)
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_import_equipements_csv(): void
    {
        $coffret = Coffret::factory()->create();

        $file = $this->createCsvFile(
            ['Code', 'Nom', 'Type', 'Classification', 'Status', 'Coffret ID'],
            [['EQ-001', 'Switch A', 'switch', 'IT', 'active', (string) $coffret->id]],
        );

        $response = $this->actingAs($this->admin)->post('/api/v1/imports/equipements/csv', [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.imported', 1);
        $this->assertDatabaseHas('equipements', ['equipement_code' => 'EQ-001']);
    }

    public function test_import_ports_csv(): void
    {
        $file = $this->createCsvFile(
            ['Label', 'Device', 'Type', 'VLAN', 'Status'],
            [['GE0/0/1', 'Switch-A', 'ethernet', '100', 'active']],
        );

        $response = $this->actingAs($this->admin)->post('/api/v1/imports/ports/csv', [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.imported', 1);
    }

    public function test_import_liaisons_csv(): void
    {
        $e1 = Equipement::factory()->create();
        $e2 = Equipement::factory()->create();

        $file = $this->createCsvFile(
            ['Label', 'De (ID)', 'Vers (ID)', 'Média', 'Longueur', 'Status'],
            [['L-001', (string) $e1->id, (string) $e2->id, 'fibre', '50', '1']],
        );

        $response = $this->actingAs($this->admin)->post('/api/v1/imports/liaisons/csv', [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.imported', 1);
    }
}
