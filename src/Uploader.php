<?php

namespace Hamed\ChunkFile;

use Exception;

class Uploader
{
    public $chunks_folder = '';
    public $max_upload = 0;

    public function __construct($config = [])
    {
        $this->chunks_folder = $config['chunks_folder'] ?? storage_path("app/chunks/");
        $this->max_upload = $config['max_upload'] ?? 500 * pow(10, 6);
    }

    protected function make_one_file($directory, $chunks)
    {
        $file_name = basename($directory);
        $file_path = "$directory/$file_name";

        # Write chunks to one file
        $file = fopen($file_path, 'w');
        for ($i = 1; $i <= count($chunks); $i++) {
            # Get part path
            $part_path = "$directory/$file_name.part{$i}";

            # Write the content of the part to the file
            fwrite($file, file_get_contents($part_path));

            # Delete chunk file
            unlink($part_path);
        }
        fclose($file);

        # Return File Path
        return $file_path;
    }

    /**
     * Upload Chunk Files
     *
     * @param array $data
     *
     * @return string
     * @throws Exception
     */
    public function Upload($data)
    {
        # Set Default Values
        $data = array_merge([
            'chunks_count' => 0,
            'chunk_number' => null,
            'chunk_path' => null,
            'file_name' => null,
            'errors' => [
                'max_upload' => "You've reached the max file upload"
            ]
        ], $data);

        # Make a relatively unique directory.
        $tmp_dir_name = md5("{$data['file_name']}{$data['chunks_count']}");
        $tmp_dir_path = rtrim($this->chunks_folder, '/') . "/$tmp_dir_name";
        if (!is_dir($tmp_dir_path)) mkdir($tmp_dir_path, 0777, true);

        # Move the chunk file, to a temporary directory.
        rename($data['chunk_path'], "$tmp_dir_path/$tmp_dir_name.part{$data['chunk_number']}");

        # Get all chunks
        $uploaded_chunks = glob("$tmp_dir_path/*.part[0-9]*");
        $chunks_size = array_sum(array_map('filesize', $uploaded_chunks));

        # If the client has exceeded the max upload size
        if ($this->max_upload < $chunks_size || $this->max_upload < $data['file_size']) {
            throw new Exception($data['errors']['max_upload'] || "You've reached to the max file upload");
        }

        # If it's the last chunk
        if ($data['chunks_count'] <= count($uploaded_chunks)) {
            # Make chunk files, one file
            return $this->make_one_file($tmp_dir_path, $uploaded_chunks);
        } else {
            # Return the percentage
            return 100 * ($data['chunk_number'] / $data['chunks_count']);
        }
    }
}