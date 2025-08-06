<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

trait FileUploadTrait
{
    public function handleFileUpload(
        Request $request,
        string $fieldName,
        ?string $oldPath = null,
        string $module = 'general',
        string $prefix = ''
    ): ?string {
        if (!$request->hasFile($fieldName)) {
            return null;
        }

        // Delete old file if exists
        $this->deleteFile($oldPath);

        $file = $request->file($fieldName);
        $extension = $file->getClientOriginalExtension();

        // Generate filename with optional prefix
        $fileName = $prefix
            ? $prefix . '_' . Str::random(20) . '.' . $extension
            : Str::random(25) . '.' . $extension;

        $directory = "uploads/{$module}";
        $filePath = "{$directory}/{$fileName}";

        // Create directory if not exists
        if (!File::exists(public_path($directory))) {
            File::makeDirectory(public_path($directory), 0755, true);
        }

        $file->move(public_path($directory), $fileName);

        return $filePath;
    }

    public function deleteFile(?string $path): void
    {
        if ($path && File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }
}
