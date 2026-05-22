<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicMediaController extends Controller
{
    public function show(Request $request, string $path): StreamedResponse|Response
    {
        $path = ltrim($path, '/');

        if ($path === '' || str_contains($path, '..') || !Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $fullPath = Storage::disk('public')->path($path);
        if (!is_file($fullPath)) {
            abort(404);
        }

        $fileSize = filesize($fullPath);
        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
        $rangeHeader = $request->header('Range');

        $start = 0;
        $end = $fileSize - 1;
        $status = 200;

        if ($rangeHeader && preg_match('/bytes=(\d*)-(\d*)/', $rangeHeader, $matches)) {
            $status = 206;

            if ($matches[1] !== '') {
                $start = (int) $matches[1];
            }

            if ($matches[2] !== '') {
                $end = min((int) $matches[2], $fileSize - 1);
            }

            if ($matches[1] === '' && $matches[2] !== '') {
                $suffixLength = min((int) $matches[2], $fileSize);
                $start = $fileSize - $suffixLength;
                $end = $fileSize - 1;
            }

            if ($start > $end || $start >= $fileSize) {
                return response('', 416, [
                    'Content-Range' => "bytes */{$fileSize}",
                    'Accept-Ranges' => 'bytes',
                ]);
            }
        }

        $length = $end - $start + 1;
        $headers = [
            'Accept-Ranges' => 'bytes',
            'Content-Type' => $mimeType,
            'Content-Length' => $length,
            'Cache-Control' => 'public, max-age=31536000',
        ];

        if ($status === 206) {
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$fileSize}";
        }

        return response()->stream(function () use ($fullPath, $start, $length) {
            $handle = fopen($fullPath, 'rb');
            fseek($handle, $start);

            $remaining = $length;
            while ($remaining > 0 && !feof($handle)) {
                $chunkSize = min(8192, $remaining);
                echo fread($handle, $chunkSize);
                $remaining -= $chunkSize;
                flush();
            }

            fclose($handle);
        }, $status, $headers);
    }
}
