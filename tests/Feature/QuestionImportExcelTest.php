<?php

namespace Tests\Feature;

use App\Exports\QuestionImportTemplateExport;
use App\Models\Question;
use App\Models\QuestionBankTemplate;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class QuestionImportExcelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('fake');
    }

    private function hrAdmin(): User
    {
        return User::factory()->hrAdmin()->create();
    }

    /**
     * @param  array<int, array<int, string|int>>  $rows
     */
    private function makeExcel(array $rows, string $filename = 'soal.xlsx'): UploadedFile
    {
        $export = new class($rows) implements FromArray, WithHeadings
        {
            /**
             * @param  array<int, array<int, string|int>>  $rows
             */
            public function __construct(private readonly array $rows) {}

            public function array(): array
            {
                return $this->rows;
            }

            /**
             * @return array<int, string>
             */
            public function headings(): array
            {
                return QuestionImportTemplateExport::HEADINGS;
            }
        };

        Excel::store($export, $filename, 'fake');
        $path = Storage::disk('fake')->path($filename);

        return new UploadedFile(
            $path,
            $filename,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );
    }

    /**
     * @return array<int, array<int, string|int>>
     */
    private function validRows(): array
    {
        return [
            ['Pilihan Ganda', 'Apa fungsi hemoglobin?', 10, 'Mengangkut oksigen', 'Mencerna makanan', 'Melawan infeksi', 'Mengatur gula', 'A'],
            ['Esai', 'Jelaskan sterilisasi alat.', 20, '', '', '', '', '-'],
            ['Unggah Gambar', 'Unggah foto hasil pemeriksaan.', 15, '', '', '', '', '-'],
        ];
    }

    public function test_template_can_be_downloaded(): void
    {
        $response = $this->actingAs($this->hrAdmin())->get(route('template-bank-soal.import.template'));

        $response->assertOk();
        $this->assertStringContainsString('spreadsheet', $response->headers->get('Content-Type'));

        Excel::store(new QuestionImportTemplateExport, 'template.xlsx', 'fake');

        $reader = new class implements ToCollection
        {
            /** @var array<int, Collection<int, Collection<int, mixed>>> */
            public array $sheets = [];

            public function collection(Collection $rows): void
            {
                $this->sheets[] = $rows;
            }
        };
        Excel::import($reader, Storage::disk('fake')->path('template.xlsx'));

        $this->assertCount(3, $reader->sheets);
        $this->assertEquals(QuestionImportTemplateExport::HEADINGS, $reader->sheets[0]->first()->values()->all());
    }

    public function test_parsing_valid_file_fills_form_without_saving(): void
    {
        $response = $this->actingAs($this->hrAdmin())->post(route('template-bank-soal.import.parse'), [
            'nama' => 'Tes Hasil Import',
            'file' => $this->makeExcel($this->validRows()),
        ]);

        $response->assertRedirect(route('template-bank-soal.create'));
        $this->assertDatabaseCount('question_bank_templates', 0);
        $this->assertDatabaseCount('questions', 0);

        $this->actingAs($this->hrAdmin())->get(route('template-bank-soal.create'))
            ->assertOk()
            ->assertSee('Apa fungsi hemoglobin?')
            ->assertSee('Jelaskan sterilisasi alat.');
    }

    public function test_user_can_edit_filled_form_and_save_template(): void
    {
        $admin = $this->hrAdmin();
        $this->actingAs($admin)->post(route('template-bank-soal.import.parse'), [
            'nama' => 'Tes Hasil Import',
            'file' => $this->makeExcel($this->validRows()),
        ]);
        $this->actingAs($admin)->get(route('template-bank-soal.create'));

        $response = $this->actingAs($admin)->post(route('template-bank-soal.store'), [
            'nama' => 'Tes Hasil Import',
            'questions' => [
                [
                    'tipe' => 'mc',
                    'pertanyaan' => 'Apa fungsi hemoglobin?',
                    'nilai_poin' => 10,
                    'correct_option' => 0,
                    'options' => [
                        ['teks_opsi' => 'Mengangkut oksigen'],
                        ['teks_opsi' => 'Mencerna makanan'],
                        ['teks_opsi' => 'Melawan infeksi'],
                        ['teks_opsi' => 'Mengatur gula'],
                    ],
                ],
                [
                    'tipe' => 'essay',
                    'pertanyaan' => 'Jelaskan sterilisasi alat.',
                    'nilai_poin' => 20,
                ],
                [
                    'tipe' => 'image',
                    'pertanyaan' => 'Unggah foto hasil pemeriksaan.',
                    'nilai_poin' => 15,
                ],
            ],
        ]);

        $response->assertRedirect(route('template-bank-soal.index'));
        $template = QuestionBankTemplate::firstOrFail();
        $this->assertEquals('Tes Hasil Import', $template->nama);
        $this->assertCount(3, $template->questions);
        $this->assertEquals([1, 2, 3], $template->questions->pluck('urutan')->all());
        $this->assertTrue($template->questions->first()->options()->where('is_correct', true)->exists());
        $template->questions->each(fn (Question $q) => $this->assertNull($q->gambar_path));
    }

    public function test_invalid_rows_are_reported_and_form_is_not_filled(): void
    {
        $rows = $this->validRows();
        $rows[0][7] = 'Z';

        $this->actingAs($this->hrAdmin())->post(route('template-bank-soal.import.parse'), [
            'nama' => 'Tes Error',
            'file' => $this->makeExcel($rows),
        ])->assertRedirect(route('template-bank-soal.create'));

        $this->actingAs($this->hrAdmin())->get(route('template-bank-soal.create'))
            ->assertOk()
            ->assertSee('Baris 2')
            ->assertDontSee('Apa fungsi hemoglobin?');
    }

    public function test_two_option_mc_is_accepted(): void
    {
        $rows = [['Pilihan Ganda', 'Soal dua opsi?', 5, 'A benar', 'B salah', '', '', 'A']];

        $this->actingAs($this->hrAdmin())->post(route('template-bank-soal.import.parse'), [
            'file' => $this->makeExcel($rows),
        ])->assertRedirect(route('template-bank-soal.create'));

        $this->actingAs($this->hrAdmin())->get(route('template-bank-soal.create'))
            ->assertOk()
            ->assertSee('Soal dua opsi?');
    }

    public function test_mc_with_single_option_is_rejected(): void
    {
        $rows = [['Pilihan Ganda', 'Soal satu opsi?', 5, 'A saja', '', '', '', 'A']];

        $this->actingAs($this->hrAdmin())->post(route('template-bank-soal.import.parse'), [
            'file' => $this->makeExcel($rows),
        ])->assertRedirect(route('template-bank-soal.create'));

        $this->actingAs($this->hrAdmin())->get(route('template-bank-soal.create'))
            ->assertOk()
            ->assertSee('minimal mengisi Opsi A dan Opsi B');
    }

    public function test_mc_with_option_gap_is_rejected(): void
    {
        $rows = [['Pilihan Ganda', 'Soal bercelah?', 5, 'A', '', 'C', '', 'C']];

        $this->actingAs($this->hrAdmin())->post(route('template-bank-soal.import.parse'), [
            'file' => $this->makeExcel($rows),
        ])->assertRedirect(route('template-bank-soal.create'));

        $this->actingAs($this->hrAdmin())->get(route('template-bank-soal.create'))
            ->assertOk()
            ->assertSee('Opsi tidak boleh ada celah kosong');
    }

    public function test_key_pointing_to_empty_option_is_rejected(): void
    {
        $rows = [['Pilihan Ganda', 'Kunci kosong?', 5, 'A', 'B', '', '', 'C']];

        $this->actingAs($this->hrAdmin())->post(route('template-bank-soal.import.parse'), [
            'file' => $this->makeExcel($rows),
        ])->assertRedirect(route('template-bank-soal.create'));

        $this->actingAs($this->hrAdmin())->get(route('template-bank-soal.create'))
            ->assertOk()
            ->assertSee('Kunci Jawaban menunjuk opsi yang kosong');
    }

    public function test_unknown_type_is_rejected(): void
    {
        $rows = [['Benar Salah', 'Tipe salah?', 5, 'A', 'B', '', '', 'A']];

        $this->actingAs($this->hrAdmin())->post(route('template-bank-soal.import.parse'), [
            'file' => $this->makeExcel($rows),
        ])->assertRedirect(route('template-bank-soal.create'));

        $this->actingAs($this->hrAdmin())->get(route('template-bank-soal.create'))
            ->assertOk()
            ->assertSee('Baris 2');
    }

    public function test_invalid_points_are_rejected(): void
    {
        foreach ([0, 101, 'abc'] as $poin) {
            $rows = [['Esai', 'Poin salah?', $poin, '', '', '', '', '-']];

            $this->actingAs($this->hrAdmin())->post(route('template-bank-soal.import.parse'), [
                'file' => $this->makeExcel($rows, 'soal-'.$poin.'.xlsx'),
            ])->assertRedirect(route('template-bank-soal.create'));

            $this->actingAs($this->hrAdmin())->get(route('template-bank-soal.create'))
                ->assertOk()
                ->assertSee('Poin harus angka bulat');
        }
    }

    public function test_empty_question_is_rejected(): void
    {
        $rows = [['Esai', '', 10, '', '', '', '', '-']];

        $this->actingAs($this->hrAdmin())->post(route('template-bank-soal.import.parse'), [
            'file' => $this->makeExcel($rows),
        ])->assertRedirect(route('template-bank-soal.create'));

        $this->actingAs($this->hrAdmin())->get(route('template-bank-soal.create'))
            ->assertOk()
            ->assertSee('Pertanyaan wajib diisi');
    }

    public function test_file_without_questions_is_rejected(): void
    {
        $this->actingAs($this->hrAdmin())->post(route('template-bank-soal.import.parse'), [
            'file' => $this->makeExcel([]),
        ])->assertRedirect(route('template-bank-soal.create'));

        $this->actingAs($this->hrAdmin())->get(route('template-bank-soal.create'))
            ->assertOk()
            ->assertSee('File tidak berisi soal');
    }

    public function test_wrong_extension_is_rejected(): void
    {
        $file = UploadedFile::fake()->create('soal.csv', 10, 'text/csv');

        $this->actingAs($this->hrAdmin())->post(route('template-bank-soal.import.parse'), ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_oversized_file_is_rejected(): void
    {
        $file = UploadedFile::fake()->create('soal.xlsx', 2100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($this->hrAdmin())->post(route('template-bank-soal.import.parse'), ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_more_than_five_hundred_rows_are_rejected(): void
    {
        $rows = [];
        for ($i = 0; $i <= 500; $i++) {
            $rows[] = ['Esai', 'Soal ke-'.$i, 1, '', '', '', '', '-'];
        }

        $this->actingAs($this->hrAdmin())->post(route('template-bank-soal.import.parse'), [
            'file' => $this->makeExcel($rows),
        ])->assertRedirect(route('template-bank-soal.create'));

        $this->actingAs($this->hrAdmin())->get(route('template-bank-soal.create'))
            ->assertOk()
            ->assertSee('melebihi batas maksimal 500 soal');
    }

    public function test_non_hr_admin_cannot_access_import_routes(): void
    {
        $user = User::factory()->withRole(Role::Employee)->create();

        $this->actingAs($user)->get(route('template-bank-soal.import.template'))->assertForbidden();
        $this->actingAs($user)->post(route('template-bank-soal.import.parse'), [
            'file' => $this->makeExcel($this->validRows()),
        ])->assertForbidden();
    }
}
