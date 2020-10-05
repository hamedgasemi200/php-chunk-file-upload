<?php

namespace Hamed\ChunkFile;

use Illuminate\Support\Facades\File;
use Exception;

class Chunk
{
    /**
     * Upload Chunk Files
     *
     * @param array $data
     *
     * @return string
     * @throws Exception
     */
    public static function Upload($data)
    {
        # Set Default Values
        $data = array_merge([
            'chunk_number' => null,
            'chunk_path' => null,
            'chunk_folder' => storage_path("app/chunks/"),
            'max_upload' => 500 * pow(10, 6),
            'errors' => [
                'max_upload' => "You've reached the max file upload"
            ]
        ], $data);

        # Temp Directory
        $temp_dir = $data['chunk_folder'] . md5("{$data['file_name']}-{$data['chunks_count']}");
        if (!is_dir($temp_dir)) mkdir($temp_dir, 0777, true);

        # Move the chunk file to a temporary directory
        rename($data['chunk_path'], "$temp_dir/{$data['file_name']}.part{$data['chunk_number']}");

        # Get all chunks
        $uploaded_chunks = glob("{$temp_dir}/*");

        # Get sum of the size of the all chunks
        $size_of_uploaded_chunks = array_map('filesize', $uploaded_chunks);
        $chunk_files_size = array_sum($size_of_uploaded_chunks) + $data['file_size'];

        # If the client has exceeded the max upload size
        if ($data['max_upload'] < $chunk_files_size) throw new Exception($data['errors']['max_upload'] || "You've reached to the max file upload");

        # If it's the last chunk
        if ($data['chunks_count'] <= count($uploaded_chunks)) {

            # Write chunks in a file
            $file_path = $data['chunk_folder'] . $data['file_name'];
            $file = fopen($file_path, 'w');
            for ($i = 1; $i <= $data['chunk_number']; $i++) {
                # Get part path
                $part_path = "{$temp_dir}/{$data['file_name']}.part{$i}";

                # If file not exists or it's a directory
                if (!is_file($part_path)) {
                    File::deleteDirectory($temp_dir);
                    fclose($file);
                    throw new Exception('The files are not in order');
                }

                # Write the content of the part to the file
                fwrite($file, file_get_contents($part_path));
            }

            # Close the file
            fclose($file);

            # File has been completed. Now delete the chunk folder.
            File::deleteDirectory($temp_dir);

            # Return File Path
            return $file_path;
        }

        # Return the percentage
        return 100 * ($data['chunk_number'] / $data['chunks_count']);
    }
}