<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SkillImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Models\ExamSkill;
use App\Services\SkillExcelImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class SkillImportController extends Controller
{
    public function template(ExamSkill $skill)
    {
        $fileName = Str::slug($skill->name ?: 'quiz') . '-import-template.xlsx';

        return Excel::download(new SkillImportTemplateExport($skill), $fileName);
    }

    public function preview(Request $request, ExamSkill $skill, SkillExcelImportService $importService)
    {
        $validated = $request->validate([
            'import_file' => 'required|file|mimes:xlsx|max:20480',
        ]);

        try {
            $preview = $importService->preview($skill, $validated['import_file']);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('admin.skills.edit', $skill)
                ->withErrors(['import_file' => 'Không đọc được file Excel. Hãy kiểm tra file .xlsx và thử lại.']);
        }

        $token = null;
        $expiresAt = now()->addMinutes(30);

        if (empty($preview['errors'])) {
            $token = (string) Str::uuid();
            Cache::put($this->cacheKey($skill, $token), $preview['payload'], $expiresAt);
        }

        return redirect()
            ->route('admin.skills.edit', $skill)
            ->with('skill_import_preview', [
                'token' => $token,
                'summary' => $preview['summary'],
                'errors' => $preview['errors'],
                'details' => empty($preview['errors']) ? $importService->previewDetails($preview['payload']) : null,
                'expires_at' => $expiresAt->toDateTimeString(),
            ]);
    }

    public function confirm(Request $request, ExamSkill $skill, SkillExcelImportService $importService)
    {
        $validated = $request->validate([
            'preview_token' => 'required|string',
        ]);

        $cacheKey = $this->cacheKey($skill, $validated['preview_token']);
        $payload = Cache::get($cacheKey);

        if (!$payload) {
            return redirect()
                ->route('admin.skills.edit', $skill)
                ->withErrors(['import_file' => 'Bản preview đã hết hạn. Hãy upload file và preview lại.']);
        }

        if (($payload['skill_id'] ?? null) !== $skill->id || ($payload['skill_type'] ?? null) !== $skill->skill_type) {
            Cache::forget($cacheKey);

            return redirect()
                ->route('admin.skills.edit', $skill)
                ->withErrors(['import_file' => 'Bản preview không khớp với quiz hiện tại.']);
        }

        try {
            $importService->replaceContent($skill, $payload);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('admin.skills.edit', $skill)
                ->withErrors(['import_file' => 'Không thể import file Excel. Dữ liệu quiz chưa bị thay đổi.']);
        }

        Cache::forget($cacheKey);

        return redirect()
            ->route('admin.skills.edit', $skill)
            ->with('success', 'Import Excel thành công. Nội dung quiz đã được thay thế.');
    }

    private function cacheKey(ExamSkill $skill, string $token): string
    {
        return "skill-import-preview:{$skill->id}:{$token}";
    }
}
