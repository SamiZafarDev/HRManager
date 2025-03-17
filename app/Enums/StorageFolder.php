<?php

namespace App\Enums;

enum StorageFolder: string
{
    case PROFILE_PICTURES = 'profile_pictures';
    case DOCUMENTS = 'documents';
    case UPLOADS = 'uploads';
    case TEMP_FILES = 'temp';
    case LOGS = 'logs';

    /**
     * Get the full storage path for the folder.
     */
    public function path(): string
    {
        return storage_path('app/' . $this->value);
    }

    /**
     * Get the public URL path for the folder.
     */
    public function publicPath(): string
    {
        return asset('storage/' . $this->value);
    }
}
