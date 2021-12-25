# Php Chunk File Upload

A lightweight PHP package for handling the chunk file upload. Compatible with almost all the versions of Laravel.

## Installation

You can simply install the package using the composer.

```
composer require hamed/php-chunk-file-upload
```

https://packagist.org/packages/hamed/php-chunk-file-upload

## Usage

To use this package, simply, run the "Upload" method of `Uploader` after making the object. It needs a/an hashtable/array with the following keys:

+ `chunk_number` => The number of the chunk. It has to be increased for each of the chnuk files in order.
+ `chunks_count` => Total number of the chunk files.
+ `chunk_path` => The path of the incoming chunk file
+ `file_size` => The size of the mail file.
+ `file_name` => The name of the final file

And the following fields are arbitrary for the constructor:

+ `max_upload` => By default it's bytes. If user tries to upload more than max_upload (max_upload < file_size), it will return an error.
+ `chunk_folder` => The folder which all the chunks will be placed in.

## Method returns

The function has 2 states.

+ If the chunk file be uploaded fine, it will return a `float` representing the percentage of the upload.
+ If all the chunks be uploaded fine, it will return an `string` representing the path of the final file.

## Example

Here is an example of resumable js library requests.

```
use Hamed\ChunkFile\Uploader;

...

try {
   $file_path = (new Uploader())->Upload([
      'chunk_path' => $request->file('file')->getRealPath(),
      'file_name' => $request->resumableFilename,
      'chunk_number' => $request->resumableChunkNumber,
      'chunks_count' => $request->resumableTotalChunks,
      'file_size' => $request->resumableTotalSize,
      'max_upload' => 5 * pow(10, 6),
      'errors' => ['max_upload' => "Low Space"],
   ]);
} catch (Exception $exception) {
   return response($exception->getMessage(), 403);
}

if (is_string($file_path)) echo("File path: $file_path"); // Upload has finished. You can move the file.
else echo("Progress: {$file_path}%") // Chunk has been uploaded. Print the percentage of the upload ;)
```
