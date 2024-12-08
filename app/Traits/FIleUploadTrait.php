<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File as FacadesFile;

trait FileUploadTrait
{
    /**
     * Handle file upload and optionally delete the old file.
     *
     * @param Request $request
     * @param string $fieldName
     * @param string|null $oldPath
     * @param string $subDir
     * @return string|null
     */
    public function handleFileUpload(Request $request, string $fieldName, ?string $oldPath = null, string $subDir = 'uploads'): ?string
    {
        // Check if the file exists in the request
        if (!$request->hasFile($fieldName)) {
            return $oldPath; // Return the old path if no new file is uploaded
        }

        // Delete the old file if it exists
        if ($oldPath && FacadesFile::exists(public_path($oldPath))) {
            FacadesFile::delete(public_path($oldPath));
        }

        // Process the new file upload
        $file = $request->file($fieldName);
        $extension = $file->getClientOriginalExtension();
        $updatedFileName = Str::random(30) . '.' . $extension;

        // Set the directory path dynamically based on the sub-directory
        $filePath = $subDir . '/' . $updatedFileName;

        // Move the file to the desired directory
        $file->move(public_path($subDir), $updatedFileName);

        return $filePath;
    }

    /**
     * Delete a file by its path.
     *
     * @param string $path
     * @return void
     */
    public function deleteFile(string $path): void
    {
        if ($path && FacadesFile::exists(public_path($path))) {
            FacadesFile::delete(public_path($path));
        }
    }
}
