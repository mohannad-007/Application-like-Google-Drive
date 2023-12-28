<?php

namespace App\Http\Controllers;

use App\Aspects\Logger;
use App\Models\File;
use App\Models\FileUserReserved;
use App\Repository\FileRepositoryInterface;
use http\Env\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Psy\Util\Json;

#[Logger]
class FileController extends Controller
{
    //
    protected $fileRepository;
    public function __construct(FileRepositoryInterface $fileRepository)
    {
        $this->fileRepository=$fileRepository;
    }
    public function uploadFileToGroup(Request $request):JsonResponse
    {
        $data=$request->all();
       // dd($data);
        $rules=[
            'file'=>'required',
            'group_id'=>'required|integer'
        ];

        $validation = Validator::make($data, $rules);
        if ($validation->fails())
        {
            return response()->json(['status'=>false,'message'=>$validation->errors()->first()],500);
        }
        $user_id=auth()->user()->id;
        $data['user_id']=$user_id;
        $file=$this->fileRepository->uploadFileToGroup($data);
        if ($file)
        {
            $fileEvent=$this->fileRepository->addFileEvent($file->id,$user_id,1);
            if($fileEvent)
            {
                return response()->json(['status'=>true,'message'=>'File uploaded successfully','data'=>[$file,$fileEvent]],200);
            }
            else
            {
                return response()->json(['status'=>false,'message'=>'File Events not Complete '],500);
            }
        }
        else
        {
            return response()->json(['status'=>false,'message'=>'File upload failed'],500);
        }
    }
    public function updateFileAfterCheckOut(Request $request)
    {
        $data=$request->all();
        $rules=[
            'file'=>'required',
            'file_id'=>'required|integer'
        ];

        $validation = Validator::make($data, $rules);
        if ($validation->fails())
        {
            return response()->json(['status'=>false,'message'=>$validation->errors()->first()],500);
        }
        $file=$this->fileRepository->updateFileAfterCheckOut($data);
        if ($file)
        {
            $fileEvent=$this->fileRepository->addFileEvent($file->id,auth()->user()->id,6);
            if($fileEvent)
            {
                return response()->json(['status'=>true,'message'=>'File updated successfully'],200);
            }
            else
            {
                return response()->json(['status'=>false,'message'=>'File Events not Complete '],500);
            }
        }
        else
        {
            return response()->json(['status'=>false,'message'=>'File update failed'],500);
        }
    }
    public function downloadFile(Request $request)
    {
        $data=$request->all();
        $rules=[
            'file_id'=>'required|integer'
        ];
        $validation = Validator::make($data, $rules);
        if ($validation->fails())
        {
            return response()->json(['status'=>false,'message'=>$validation->errors()->first()],500);
        }
        $user_id=auth()->user()->id;
        $data['user_id']=$user_id;
        $responseData=$this->fileRepository->downloadFile($data);
        $fileEvent=$this->fileRepository->addFileEvent($data['file_id'],$user_id,2);
            if ($fileEvent)
            {
                return response($responseData['content'], 200, $responseData['headers']);
            }
            else
            {
                return response()->json(['status'=>false,'message'=>'File Events not Complete '],500);
            }

    }

    public function deleteFile(Request $request)
    {
        $data=$request->all();
        $rules=[
            'file_id'=>'required|integer'
        ];
        $validation = Validator::make($data, $rules);
        if ($validation->fails())
        {
            return response()->json(['status'=>false,'message'=>$validation->errors()->first()],500);
        }
        $user_id=auth()->user()->id;
        $data['user_id']=$user_id;
        $responseData=$this->fileRepository->deleteFile($data);
        if ($responseData)
        {
            $fileEvent=$this->fileRepository->addFileEvent($data['file_id'],$user_id,3);
            //dd($fileEvent);
            if ($fileEvent)
            {
                return response()->json(['status'=>true,'message'=>'File Deleted Successfully'],200);
            }
        }
        else
        {
            return response()->json(['status'=>false,'message'=>'File not Deleted'],500);
        }

    }
    public function getFile(Request $request):JsonResponse
    {
        $url='/storage/file.txt';
        $filename='DxDiag.txt';
        $exist=Storage::disk('local')->exists($url);
        $fileName = basename($url);
       // dd($fileName);

        // احصل على محتوى الملف
        $fileContent = Storage::disk('local')->get($url);
       // dd($fileContent);

        // قم بتحديد نوع الملف (يمكن أن يكون معرفًا يدويًا أو استخدم mime_content_type())
        $mimeType = Storage::disk('local')->mimeType($url);

        // إعداد الهيدرات اللازمة للمتصفح
        $headers = [
            'Content-Type' => $mimeType,
            'Content-Disposition' => "attachment; filename={$fileName}",
        ];
        // إعادة المحتوى كاستجابة
        return response($fileContent, 200, $headers);
//        return response()->json($fileContent, 200, $headers);

    }
    public function checkIn(Request $request):JsonResponse
    {
        $data=$request->all();
        $rules=[
            'file_id'=>'required|integer'
        ];
        $validation = Validator::make($data, $rules);
        if ($validation->fails())
        {
            return response()->json(['status'=>false,'message'=>$validation->errors()->first()],500);
        }
        $user_id=auth()->user()->id;
        $checkin=$this->fileRepository->checkIn($data);
        if($checkin)
        {
            $fileEvent=$this->fileRepository->addFileEvent($data['file_id'],$user_id,4);
            if ($fileEvent)
            {
                $file_id=$data['file_id'];
                $file=File::find($file_id);
                $file_user_reserved = new FileUserReserved();
                $file_user_reserved->group_id = $file->group_id;
                $file_user_reserved->user_id = $file->user_id;
                $file_user_reserved->save();
                return response()->json(['status'=>true,'message'=>'File Has Been Reserved'],200);
            }
            else
            {
                return  response()->json(['status'=>false,'message'=>'Event File not Complete!'],500);

            }
        }
        else
        {
            return response()->json(['status'=>false,'message'=>'File Not Reserved'],500);

        }
    }
    public function checkOut(Request $request):JsonResponse
    {
        $data=$request->all();
        $rules=[
            'file_id'=>'required|integer'
        ];
        $validation = Validator::make($data, $rules);
        if ($validation->fails())
        {
            return response()->json(['status'=>false,'message'=>$validation->errors()->first()],500);
        }
        $user_id=auth()->user()->id;
        $checkout=$this->fileRepository->checkOut($data);
        if($checkout)
        {
            $fileEvent=$this->fileRepository->addFileEvent($data['file_id'],$user_id,5);
            if ($fileEvent)
            {
                $file_id=$data['file_id'];
                $file=File::find($file_id);
                FileUserReserved::where('group_id', $file->group_id)->where('user_id', $file->user_id)->delete();
                return response()->json(['status'=>true,'message'=>'File Has Been Un-Reserved'],200);
            }
            else
            {
                return  response()->json(['status'=>false,'message'=>'Event File not Complete!'],500);

            }
        }
        else
        {
            return response()->json(['status'=>false,'message'=>'File Not Un-Reserved'],500);

        }
    }
    public function test(Request $request)
    {
        $data=$request->all();
        $size=count($data);
        dd($size);
        $id=1;
        dd($data['id'.$id]);
    }
    public function bulkCheckIn(Request $request):JsonResponse
    {
        $data=$request->all();
        $result=$this->fileRepository->bulkCheckIn($data);
        if ($result)
        {
            $file_id=$data['file_id'];
            $file=File::find($file_id);
            $file_user_reserved = new FileUserReserved();
            $file_user_reserved->group_id = $file->group_id;
            $file_user_reserved->user_id = $file->user_id;
            $file_user_reserved->save();
            return response()->json(['status'=>true,'message'=>'Files Has Been Checked In'],200);
        }
        else
        {
            return response()->json(['status'=>false,'message'=>'Files Not Checked In'],500);
        }
    }
    public function showReportForFile(Request $request):JsonResponse
    {
        $data=$request->all();
        $rules=
            [
                'file_id'=>'required|exists:files,id',
            ];
        $validator=Validator::make($data,$rules);
        if ($validator->fails())
            {
                return response()->json(['status'=>false,'message'=>$validator->errors()],500);
            }
        $reports=$this->fileRepository->showReportForFile($data);
        if ($reports)
        {
            return response()->json(['status'=>true,'message'=>'Reports','data'=>$reports],200);
        }
        else
        {
            return response()->json(['status'=>false,'message'=>'Reports Not Found'],500);
        }
    }


}
