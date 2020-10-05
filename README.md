 Laravel package for chunk file uploading


A light weight laravel chunk file uploader. Compatible with almost all the versions of Laravel.

# Usage

To use this library simply run the Upload function of Chunks class statically. It takes 4 required parameters:

+ The Incoming Chunk File => An object of \Illuminate\Http\File|\Illuminate\Http\UploadedFile
+ The name of the final file => A string
+ The index/number of the chunk => An integer
+ The count of the total chunks => An integer

What does the function return? It has 2 states. If everything goes fine, it returns the path of the uploaded file. Otherwise, if upload failes it returns null.

Here is an example of resumable js library requests.

```
use Hamedgasemi\ChunkUpload\Chunks;

$file_path = Chunks::Upload($request->file('file'), $request->resumableFilename, $request->resumableChunkNumber, $request->resumableTotalChunks);

if($file_path) echo ("The file is uploaded successfully");
else echo ("Upload Failed");
```
