<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class CsvImportService
{
    /**
     * Import CSV data into a model.
     *
     * @param  UploadedFile  $file        The uploaded CSV file
     * @param  string        $modelClass  The Eloquent model class
     * @param  array         $columnMap   CSV header => model field mapping
     * @param  array         $rules       Validation rules per field
     * @param  string        $uniqueKey   The unique field for upsert
     * @return array{imported: int, updated: int, errors: array}
     */
    public function import(UploadedFile $file, string $modelClass, array $columnMap, array $rules, string $uniqueKey): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Read header row
        $headers = fgetcsv($handle, 0, ';');
        if (!$headers) {
            fclose($handle);
            return ['imported' => 0, 'updated' => 0, 'errors' => [['line' => 1, 'message' => 'En-têtes CSV manquants']]];
        }

        $headers = array_map('trim', $headers);

        $imported = 0;
        $updated = 0;
        $errors = [];
        $line = 1; // header is line 1

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $line++;

            // Skip empty rows
            if (count($row) === 1 && empty(trim($row[0]))) {
                continue;
            }

            // Map CSV columns to model fields
            $data = [];
            foreach ($columnMap as $csvHeader => $modelField) {
                $index = array_search($csvHeader, $headers);
                if ($index !== false && isset($row[$index])) {
                    $value = trim($row[$index]);
                    $data[$modelField] = $value !== '' ? $value : null;
                }
            }

            // Validate the row
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                $messages = collect($validator->errors()->all())->implode('; ');
                $errors[] = ['line' => $line, 'message' => $messages];
                continue;
            }

            $validated = $validator->validated();

            // Upsert: check if record exists by unique key
            if (empty($validated[$uniqueKey])) {
                $errors[] = ['line' => $line, 'message' => "Clé unique '{$uniqueKey}' manquante"];
                continue;
            }

            $existing = $modelClass::where($uniqueKey, $validated[$uniqueKey])->first();

            if ($existing) {
                $existing->update($validated);
                $updated++;
            } else {
                $modelClass::create($validated);
                $imported++;
            }
        }

        fclose($handle);

        return compact('imported', 'updated', 'errors');
    }
}
