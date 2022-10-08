<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;

class HomeController extends Controller
{
    public function index()
    {
        return view('video_uploader');
    }

    public function video_upload(Request $request)
    {
        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

        if(!$receiver->isUploaded()){
            //filee not uploaded
        }

        $fileReceived = $receiver->receive();
        if($fileReceived->isFinished()){
            $file = $fileReceived->getFile();
            $extension = $file->getClientOriginalExtension();
            $fileName = str_replace('.'.$extension, '', $file->getClientOriginalName());
            $fileName = '-' . md5(time()) . '.' . $extension;

            $disk = Storage::disk(config('filesystems.default'));
            $path = $disk->put('videos', $file, $fileName);

            //delete chunked file
            unlink($file->getPathname());
            return [
                'path' => asset('storage/' . $path),
                'filename' => $fileName
            ];
        }

        // return percentage
         $handler = $fileReceived->handler();
         return [
            'done' => $handler->getPercentageDone(),
            'status' => true
         ];
    }
}
