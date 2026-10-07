<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEmailTemplateRequest;
use App\Models\EmailTemplate;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    public function index(): View
    {
        auth()->user()->requirePermission(Permissions::EMAIL_TEMPLATE_VIEW);

        $templates = EmailTemplate::orderBy('key')->get();

        return view('email-templates.index', compact('templates'));
    }

    public function edit(EmailTemplate $templateEmail): View
    {
        auth()->user()->requirePermission(Permissions::EMAIL_TEMPLATE_UPDATE);

        return view('email-templates.edit', compact('templateEmail'));
    }

    public function update(UpdateEmailTemplateRequest $request, EmailTemplate $templateEmail): RedirectResponse
    {
        $templateEmail->update($request->validated());

        return redirect()
            ->route('template-email.index')
            ->with('status', 'Template email berhasil diperbarui.');
    }
}
