<?php

namespace App\Http\Controllers;

use App\Exports\QuestionImportTemplateExport;
use App\Http\Requests\ParseQuestionExcelRequest;
use App\Imports\QuestionImportReader;
use App\Logging\LogContext;
use App\Services\QuestionImportService;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QuestionBankTemplateImportController extends Controller
{
    public function __construct(private readonly QuestionImportService $importService) {}

    public function template(): BinaryFileResponse
    {
        auth()->user()->requirePermission(Permissions::QUESTION_BANK_CREATE);

        return Excel::download(new QuestionImportTemplateExport, 'template-import-soal.xlsx');
    }

    public function parse(ParseQuestionExcelRequest $request): RedirectResponse
    {
        $request->user()->requirePermission(Permissions::QUESTION_BANK_CREATE);

        $reader = new QuestionImportReader;
        Excel::import($reader, $request->file('file'));

        $result = $this->importService->parse($reader->rows);

        if ($result['errors'] !== []) {
            return redirect()->route('template-bank-soal.create')
                ->with('import_errors', $result['errors'])
                ->withInput();
        }

        $request->session()->flash('question_import', [
            'questions' => $result['questions'],
            'nama' => $request->input('nama'),
        ]);

        Log::info('Question bank Excel parsed', array_merge(LogContext::make(), [
            'question_count' => count($result['questions']),
        ]));

        return redirect()->route('template-bank-soal.create');
    }
}
