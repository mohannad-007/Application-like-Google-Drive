<?php

namespace App\Repository;
use App\Models\EventType;
use App\Models\File;
use App\Models\FileEvent;


interface FileRepositoryInterface
{
    public function uploadFileToGroup($data): ?File;

    public function checkFileIfExist($group_id, $file_name, $file_extension): bool;

    public function addFileEvent($file_id, $user_id, $event_type_id): ?FileEvent;

    public function downloadFile($data): ?string;

    public function deleteFile($data): bool;

    public function updateFileAfterCheckOut($data): ?File;

    public function checkIn($data): bool;
    public function checkOut($data): bool;
    public function bulkCheckIn($data): bool;
    public function showReportForFile($data);
}
