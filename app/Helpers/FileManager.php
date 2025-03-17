<?php

namespace App\Helpers;

use App\Enums\StorageFolder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileManager
{
    /**
     * Upload a file to the specified disk.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $folder
     * @param  string  $disk
     * @return string|null
     */
    public static function uploadFile($file, StorageFolder $StorageFolder = StorageFolder::UPLOADS, $disk = 'public')
    {
        // Generate a unique filename using the original extension
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Store the file on the specified disk
        $path = $file->storeAs(str($StorageFolder->value), $filename, $disk);

        // Return the file path or URL if necessary
        return asset('storage/' . $path);
    }

    /**
     * Delete a file from the storage.s
     *
     * @param  string  $path
     * @param  string  $disk
     * @return bool
     */
    public static function deleteFile($filename, StorageFolder $StorageFolder = StorageFolder::UPLOADS, $disk = 'public')
    {
        $path = FileManager::getFilePath($filename, $StorageFolder, $disk);
        // Check if file exists before attempting to delete it
        if (FileManager::fileExists($path, $disk)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Get the file URL from the storage.
     *
     * @param  string  $path
     * @param  string  $disk
     * @return string|null
     */
    public static function getFileUrlFromName($filename, StorageFolder $StorageFolder = StorageFolder::UPLOADS, $disk = 'public')
    {
        $path = FileManager::getFilePath($filename, $StorageFolder, $disk);
        return FileManager::getFileUrl($path, $disk);
    }
    public static function getFileUrl($path, $disk = 'public')
    {
        if (FileManager::fileExists($path, $disk)) {
            return asset('storage/' . $path);
        }
        return null;
    }

    public static function getFilePath($filename, StorageFolder $StorageFolder = StorageFolder::UPLOADS, $disk = 'public')
    {
        $parts = explode('/', $filename);
        $path = $StorageFolder->value .'/'. end($parts);
        return $path;
    }


    /**
     * Check if a file exists.
     *
     * @param  string  $path
     * @param  string  $disk
     * @return bool
     */
    public static function fileExists($path, $disk = 'public')
    {
        return Storage::disk($disk)->exists($path);
    }
}
